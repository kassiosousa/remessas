## Arquitetura

A API segue o padrão Controller → Service → Repository.

Controllers:
- Responsáveis apenas por receber a requisição e retornar resposta.

Services:
- Concentram regras de negócio
- Validações complexas
- Transações

Repositories:
- Acesso ao banco
- Queries reutilizáveis
