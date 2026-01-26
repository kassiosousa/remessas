# Domain (Regras de negócio e Casos de Uso) — RemessasAPI

Este documento descreve o domínio do sistema, seus conceitos, regras e casos de uso.
O objetivo é permitir que qualquer pessoa entenda como o produto funciona sem precisar ler o código.

---

## 1. Visão do produto

O RemessasAPI é um backend para:
- cadastrar **Projetos**;
- cadastrar **Parceiros**;
- vincular **Parceiros a Projetos** com percentuais de participação (**share_percent**);
- registrar **Invoices** e suas associações a projetos (ex.: relatórios Steam/lojas);
- gerar **Pagamentos** baseadas em relatórios e regras de divisão;
- garantir consistência de **divisão de receita** e rastreabilidade.

---

## 2. Entidades e conceitos

### 2.1 Usuário (User)
Representa a identidade autenticada que opera o sistema.
- Pode criar e administrar projetos (dependendo de autorização/perfil).
- Pode registrar parceiros e vinculações.

Campos:
- id, name, email, password (hash), status
- timestamps

---

### 2.2 Projeto (Project)
Representa um "produto/jogo" ou unidade de negócio que terá receitas divididas.

Campos:
- id, name, description, status (draft/active/archived), currency (ex.: USD/BRL)
- owner_user_id (opcional, se houver dono)
- timestamps

Regras:
- Um projeto pode ter N parceiros (via pivot ProjectPartner).
- Um projeto pode ter N relatórios associados.
- Um projeto pode ter N pagamentos.

---

### 2.3 Parceiro (Partner)
Pessoa/empresa que participa do projeto e recebe parte da receita.

Campos:
- id, name, type (person/company), document (CPF/CNPJ opcional), email
- payment_details (pix/bank) (se existir)
- timestamps

Regras:
- Um parceiro pode estar em N projetos.
- Um parceiro pode receber N pagamentos.

---

### 2.4 Vínculo Projeto-Parceiro (ProjectPartner) — Pivot
Relação N:N entre Project e Partner, contendo a regra de split.

Campos:
- id (ou chave composta), project_id, partner_id
- share_percent (0..100, decimal permitido)
- role (ex.: dev, publisher, artist) (se existir)
- timestamps

**Invariantes (críticas):**
1) `share_percent` deve ser >= 0 e <= 100.  
2) Para um projeto em operação (active), a soma de `share_percent` dos parceiros vinculados deve ser no máximo **100%** (com tolerância de arredondamento).  
3) Um parceiro não deve aparecer duplicado no mesmo projeto.  
4) Alterações na lista de parceiros devem ser atômicas (transaction), evitando estados intermediários inválidos.

---

### 2.5 Relatório (Report)
Registro de um relatório financeiro ou de vendas (ex.: Steam, Epic, etc.) que pode ser associado a projetos.

Campos:
- id, source (steam/epic/other), period_start, period_end
- gross_amount, net_amount, fees, taxes (conforme seu domínio)
- currency, raw_payload / file_reference (se existir)
- timestamps

Regras:
- Um relatório pode se relacionar com N projetos (via ReportProject).
- O relatório pode ser usado para compor N pagamentos.

---

### 2.6 Vínculo Relatório-Projeto (ReportProject) — Pivot
Associação de um relatório a um projeto, possivelmente com valores alocados a esse projeto.

Campos:
- report_id, project_id
- allocated_amount (opcional)
- timestamps

Regras:
- A associação deve respeitar a moeda/período do relatório e consistência do projeto.
- Um relatório pode ser associado a múltiplos projetos quando o relatório traz múltiplos apps/itens.

---

### 2.7 Pagamento (Payout)
Documento/registro de cobrança ou repasse gerado com base em relatório e divisão do projeto.

Campos:
- id, project_id, report_id (pivot)
- status (draft/issued/paid/canceled)
- total_amount, currency
- due_date, issued_at
- timestamps

Regras:
- Ao gerar pagamento para um projeto com parceiros, o sistema pode gerar linhas ou sub-itens por parceiro (dependendo do design).
- Um pagamento deve ser reprodutível e auditável: precisa indicar de qual relatório veio e qual regra de split foi aplicada.

---

### 2.8 Vínculo Invoice-Report (InvoiceReport) — Pivot (se existir)
Associação entre invoice e relatórios quando uma invoice engloba múltiplos relatórios/períodos.

---

## 3. Regras de negócio (detalhadas)

### 3.1 Validação da soma de share_percent (Regra principal)
Ao criar/atualizar vínculo de parceiros em um projeto, a soma dos `share_percent` deve ser validada.

**Regra:**
- Para projetos `active` (ou quando você estiver “fechando” split), a soma precisa ser **100%**.
- Para projetos `draft`, pode-se permitir soma diferente de 100%, mas deve ficar explícito que o projeto está incompleto para geração de invoices (recomendado).

**Tolerância/Precisão:**
- Se `share_percent` for decimal, aplicar tolerância de arredondamento (ex.: `abs(sum - 100) <= 0.01`).
- Internamente, prefira armazenar como decimal (ex.: `DECIMAL(5,2)`), e fazer soma com precisão.

**Erros:**
- `422 Unprocessable Entity` quando:
  - soma > 100
  - soma < 100 (em contexto onde 100 é obrigatório)
  - algum share_percent inválido (<0, >100)
  - parceiro duplicado no mesmo projeto

---

### 3.2 Regra condicional: detach_missing
Em operações de “sync” de parceiros do projeto (envio de lista completa):
- `detach_missing = true`: remove do projeto todos os parceiros que não estiverem na lista enviada.
- `detach_missing = false`: mantém vínculos existentes e apenas:
  - cria novos vínculos para parceiros presentes;
  - atualiza share_percent dos parceiros presentes.

**Uso recomendado:**
- `detach_missing = true` quando a UI/API envia o “estado completo” (fonte de verdade).
- `detach_missing = false` quando a operação é incremental.

**Regras adicionais:**
- Se `detach_missing = true`, a validação de soma para 100% deve ocorrer após o “detach + upsert”.
- Se a remoção causar soma != 100% e o projeto exigir 100%, a operação deve falhar e dar rollback.

---

### 3.3 Operação mode=sync
Quando existir um payload com `"mode": "sync"`:
- o servidor deve interpretar que a intenção é alinhar o estado do vínculo (ProjectPartner) com uma lista enviada.
- normalmente associada a `detach_missing` conforme regra acima.

---

### 3.4 Geração de Invoice (com base em relatório)
Pré-condições:
- Projeto deve estar apto (ex.: status active).
- Projeto deve ter split válido (soma = 100%).
- Relatório deve estar associado ao projeto (diretamente ou via pivot).

Processo típico:
1) Selecionar projeto + relatório (ou relatórios).
2) Calcular valor base (net_amount ou allocated_amount).
3) Aplicar regras de divisão por parceiro:
   - partner_amount = base_amount * (share_percent/100)
4) Persistir invoice e itens/linhas (se houver).
5) Registrar status (draft/issued).
6) Garantir idempotência se houver “gerar novamente” (opcional, mas recomendado).

Erros:
- `409 Conflict` se tentar gerar invoice duplicada para mesmo projeto+relatório e política bloquear.
- `422` se split inválido.

---

### 3.5 Atualização de status de Invoice
Regras:
- `draft -> issued -> paid` é fluxo comum.
- `issued -> canceled` permitido (dependendo do negócio).
- `paid -> canceled` normalmente proibido.

Erros:
- `409` para transições inválidas.

---

## 4. Casos de uso (Use Cases)

### UC-01 — Autenticar usuário (Login)
Ator: usuário.
Entrada: email, password.
Saída: token de acesso e dados básicos do usuário.
Erros: credenciais inválidas (401).

### UC-02 — Encerrar sessão (Logout)
Ator: usuário autenticado.
Entrada: token.
Saída: confirmação de logout (token invalidado/revogado).

### UC-03 — Consultar usuário atual (Me)
Ator: usuário autenticado.
Saída: dados do usuário.

---

### UC-10 — Criar projeto
Ator: usuário autenticado.
Entrada: name, description, currency, status.
Saída: projeto criado.

### UC-11 — Listar projetos
Ator: usuário autenticado.
Entrada: paginação/filtros.
Saída: lista paginada.

### UC-12 — Atualizar projeto
Ator: usuário autenticado.
Entrada: campos editáveis.
Saída: projeto atualizado.

### UC-13 — Arquivar projeto
Ator: usuário autenticado.
Saída: status alterado.

---

### UC-20 — Criar parceiro
Ator: usuário autenticado.
Entrada: name, type, email/document/payment_details (se existir).
Saída: parceiro criado.

### UC-21 — Listar parceiros
Ator: usuário autenticado.
Saída: lista paginada.

---

### UC-30 — Vincular parceiro a projeto (incremental)
Ator: usuário autenticado.
Entrada: partner_id + share_percent (+ role).
Regra: não duplicar parceiro.
Saída: vínculo criado.
Validação: share_percent válido; soma = 100% se regra exigir.

### UC-31 — Atualizar share_percent de parceiro no projeto
Ator: usuário autenticado.
Entrada: partner_id + novo share_percent.
Saída: vínculo atualizado.
Validação: soma (quando aplicável).

### UC-32 — Remover parceiro do projeto
Ator: usuário autenticado.
Entrada: partner_id.
Saída: vínculo removido.
Validação: se projeto exige soma 100%, pode bloquear remoção se tornar inválido (ou permitir e marcar projeto como incompleto, conforme política do produto).

### UC-33 — Sincronizar parceiros do projeto (mode=sync)
Ator: usuário autenticado.
Entrada: lista completa de parceiros com share_percent e parâmetro detach_missing.
Saída: estado do split atualizado.
Validação: soma e regras de detach_missing.

---

### UC-40 — Cadastrar relatório
Ator: usuário autenticado.
Entrada: source, período, valores, moeda, payload/file.
Saída: relatório criado.

### UC-41 — Associar relatório a projeto
Ator: usuário autenticado.
Entrada: report_id, project_id, allocated_amount (opcional).
Saída: associação criada.

---

### UC-50 — Gerar invoice
Ator: usuário autenticado.
Entrada: project_id, report_id (ou lista), parâmetros.
Saída: invoice gerada (draft/issued).
Validação: split válido (100%), relatório associado, consistência de moeda/período.

### UC-51 — Listar invoices
Ator: usuário autenticado.
Entrada: filtros por projeto/status/período.
Saída: lista paginada.

### UC-52 — Atualizar status da invoice
Ator: usuário autenticado.
Entrada: nova transição.
Saída: invoice atualizada.
Validação: transição válida.

---

## 5. Respostas de erro padronizadas (recomendado)

Formato:
- message: string
- errors: object (campo -> lista de mensagens)
- code: string (opcional)

Códigos típicos:
- 401 Unauthorized (token ausente/inválido)
- 403 Forbidden (sem permissão)
- 404 Not Found
- 409 Conflict (duplicidade/transição inválida)
- 422 Unprocessable Entity (validações: soma != 100, share inválido)
- 500 Internal Server Error

---

## 6. Requisitos não funcionais (recomendado)

- Operações de sync devem ser transacionais.
- Operações críticas devem registrar auditoria (opcional, mas recomendado).
- Deve haver consistência de precisão em percentuais e cálculos monetários.
- Endpoints devem ser versionáveis (ex.: /api/v1) se o projeto crescer.
