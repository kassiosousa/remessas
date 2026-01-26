# RemessasSteam API

Sistema backend para gestão de projetos, parceiros e divisão de receitas.
Este projeto é uma API REST em Laravel para gestão de pagamentos realizados (payouts) que devem ser repassados com os devidos percentuais para sócios/colaboradores (partners) a partir de reports (extratos/relatórios de receita por plataforma). 

O sistema permite:
- Autenticação via JWT (tymon/jwt-auth) com middleware jwt.
- CRUD de Partners (cadastro de sócios).
- CRUD de Projects (cadastro de projetos/jogos).
- Vínculo Project <-> Partners: Define os percentuais (share_percent) e metadados (função, datas de vigência) de cada sócio por projeto.
- CRUD de Reports (entradas de receita por plataforma e período).
- Alocação do pagamento (net_amount) do report em projetos (ReportProject), permitindo distribuir um report entre múltiplos projetos.
- Geração de Payouts (saídas/repasses) calculados por percentual do sócio (partner) no projeto.
- Gestão do ciclo de vida de payouts: listar, atualizar, marcar como pago, anexar comprovantes etc.

Principais Entidades
- User: usuário autenticado via JWT (inclui campo type com valores admin ou user).
- Partner: sócio/participante do projeto (nome, email, etc.).
- Project: projeto/jogo.
- ProjectPartner: relação de parceiro por projeto contendo a informação da sua porcentagem, função e outras informações.
- Report: pagamento recebido da plataforma/mês em forma de extrato/relatório (gross, fees, taxes, net).
- ReportProject: informação de quais projetos se refere o pagamento recebido, fazendo alocações do report por projeto (net por projeto, unidades etc.).
- Payout: repasse calculado para cada sócio por projeto por pagamento.

## Visão Geral
A RemessasSteam é uma API REST responsável por:
- Gerenciar projetos
- Vincular parceiros com percentuais de participação
- Validar divisão de receitas
- Controlar autenticação e usuários

## Tecnologias
- PHP 8.4
- Laravel 10
- MySQL
- Docker
- JWT

## Documentação
- [Arquitetura](docs/architecture.md)
- [Regras de Negócio](docs/domain.md)
- [API](http://localhost:8080/api/documentation)
- [Autenticação](docs/auth.md)
- [Setup](docs/setup.md)


