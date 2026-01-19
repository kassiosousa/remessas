
0) Health check da API

Objetivo: confirmar que a API está respondendo.

curl -s http://localhost:8080/api | jq

Esperado: {"message":"api ok"}

--------------------------------------------------

1) Criar um Partner (sócio/parceiro)

Objetivo: cadastrar um parceiro que recebe percentual do lucro líquido.

curl -s -X POST http://localhost:8080/api/partners \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Parceiro A",
    "email": "parceiroA@exemplo.com",
    "portfolio": "https://portfolio.com/parceiroA",
    "birthday": "1990-01-20"
  }' | jq


Você deve guardar o id retornado.
Exemplo:

PARTNER_ID=1

Se seu endpoint exigir created_by, então você ainda não está usando auth e provavelmente está preenchendo por default no backend. Se não estiver, me avise que ajusto os curls para enviar created_by.

2) Criar um Project (jogo)

Objetivo: cadastrar o jogo/projeto que aparece nos reports.

curl -s -X POST http://localhost:8080/api/projects \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Lighthouse",
    "description": "Jogo do farol",
    "date_release": "2025-09-01",
    "steam_id": 123456,
    "url": "https://store.steampowered.com/app/123456",
    "finished": true
  }' | jq


Guarde o id do projeto:

PROJECT_ID=1

3) Vincular Partner ao Project (ProjectPartner)

Objetivo: dizer que o Parceiro A participa do Lighthouse e qual % ele recebe.

Dependendo do que você modelou, esse vínculo pode ter campos como:

revenue_share_percent (porcentagem)

net_share_percent (porcentagem no líquido)

starts_at, ends_at (vigência)

active

Exemplo de request:

curl -s -X POST http://localhost:8080/api/project-partners \
  -H "Content-Type: application/json" \
  -d "{
    \"project_id\": $PROJECT_ID,
    \"partner_id\": $PARTNER_ID,
    \"net_share_percent\": 30
  }" | jq


Explicação: Partner A recebe 30% do valor líquido atribuído a este projeto dentro de um report.


4) Criar um Report (entrada de dinheiro)

Objetivo: registrar uma remessa/entrada da Steam/Epic com um valor total.

Seus campos típicos:

platform (steam/epic/etc)

title (ex: STEAM 2025-09)

month_pay (mês/ano da referência)

gross_amount, net_amount, currency (depende do seu schema)

attachment (opcional: PDF/CSV do report)

Exemplo:

curl -s -X POST http://localhost:8080/api/reports \
  -H "Content-Type: application/json" \
  -d '{
    "title": "STEAM 2025-09",
    "platform": "steam",
    "month_pay": "2025-09-01",
    "currency": "USD",
    "gross_amount": 12000,
    "net_amount": 10000
  }' | jq


Guarde o id do report:

REPORT_ID=1



5) Alocar (allocate) o report por projeto (ReportProject)

Objetivo: dizer quais projetos fazem parte dessa remessa e quanto do líquido pertence a cada um.

Exemplo: todo o net_amount vai para o Lighthouse.

curl -s -X POST http://localhost:8080/api/reports/$REPORT_ID/allocate \
  -H "Content-Type: application/json" \
  -d "{
    \"allocations\": [
      {
        \"project_id\": $PROJECT_ID,
        \"project_net_amount\": 10000,
        \"currency\": \"USD\",
        \"units_sold\": 1000
      }
    ]
  }" | jq


Explicação:

Você está criando registros em report_projects (pivot)

Isso define a “base” para calcular payouts por parceiro

Se seu allocate suporta overwrite, você pode incluir:

"overwrite": true

6) Gerar payouts a partir do report

Objetivo: criar automaticamente as saídas para parceiros com base nas porcentagens do project_partner.

curl -s -X POST http://localhost:8080/api/reports/$REPORT_ID/generate-payouts | jq


Explicação:

Para cada report_project, o sistema encontra os project_partners ativos

Calcula amount = project_net_amount * (net_share_percent/100)

Cria um registro em payouts para cada parceiro

7) Listar payouts pendentes

Objetivo: ver o que ficou para pagar.

Se você tem filtro status=pending:

curl -s "http://localhost:8080/api/payouts?status=pending" | jq


Guarde o id de um payout:

PAYOUT_ID=1

8) Marcar payout como pago (com comprovante e nota fiscal do parceiro opcionais)

Aqui depende muito de como você implementou upload.

Opção A) Endpoint POST /payouts/{id}/mark-paid com JSON (sem arquivos)

Se você registrou apenas URLs/caminhos:

curl -s -X POST http://localhost:8080/api/payouts/$PAYOUT_ID/mark-paid \
  -H "Content-Type: application/json" \
  -d '{
    "paid_at": "2025-10-25 10:30:00",
    "payment_method": "pix",
    "payment_proof_path": "storage/payouts/proofs/proof-123.png",
    "partner_invoice_path": "storage/payouts/invoices/nf-123.pdf",
    "notes": "Pago via Pix"
  }' | jq

Opção B) Upload real (multipart/form-data)

Se seu controller recebe arquivos:

curl -s -X POST http://localhost:8080/api/payouts/$PAYOUT_ID/mark-paid \
  -F "paid_at=2025-10-25 10:30:00" \
  -F "payment_method=pix" \
  -F "notes=Pago via Pix" \
  -F "payment_proof=@./comprovante.png" \
  -F "partner_invoice=@./nota_fiscal.pdf" | jq


Explicação:

payment_proof = comprovante do pagamento (opcional)

partner_invoice = nota fiscal emitida pelo parceiro (opcional)

O payout passa para paid

9) Consultar tudo para validar o resultado
9.1) Listar reports
curl -s http://localhost:8080/api/reports | jq

9.2) Ver detalhe do report (incluindo allocations e payouts se seu show carregar)
curl -s http://localhost:8080/api/reports/$REPORT_ID | jq

9.3) Listar payouts (todos)
curl -s http://localhost:8080/api/payouts | jq

9.4) Ver payout específico
curl -s http://localhost:8080/api/payouts/$PAYOUT_ID | jq

9.5) Listar partners e projetos (sanidade)
curl -s http://localhost:8080/api/partners | jq
curl -s http://localhost:8080/api/projects | jq














