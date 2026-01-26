# RemessasAPI — curl.md
# Guia completo de uso da API

########################################
# CONFIGURAÇÃO INICIAL
########################################

export API="http://127.0.0.1:8000/api"
export TOKEN="COLE_AQUI_SEU_TOKEN_JWT"

########################################
# HEALTHCHECK
########################################

curl -s "$API"

########################################
# AUTENTICAÇÃO
########################################

# Registrar usuário
curl -X POST "$API/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Admin",
    "email": "admin@example.com",
    "password": "secret12",
    "password_confirmation": "secret12"
  }'

# Login
curl -X POST "$API/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "secret12"
  }'

# Usuário autenticado
curl "$API/auth/user" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Logout
curl -X POST "$API/auth/logout" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

########################################
# PARTNERS
########################################

# Listar partners
curl "$API/partners" \
  -H "Authorization: Bearer $TOKEN"

# Criar partner
curl -X POST "$API/partners" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "created_by": 1,
    "name": "Alice Dev",
    "email": "alice@example.com"
  }'

# Detalhar partner
curl "$API/partners/1" \
  -H "Authorization: Bearer $TOKEN"

# Atualizar partner
curl -X PUT "$API/partners/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Alice Dev Senior"
  }'

# Remover partner
curl -X DELETE "$API/partners/1" \
  -H "Authorization: Bearer $TOKEN"

########################################
# PROJECTS
########################################

# Listar projetos
curl "$API/projects" \
  -H "Authorization: Bearer $TOKEN"

# Criar projeto
curl -X POST "$API/projects" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "created_by": 1,
    "title": "Lighthouse",
    "description": "Puzzle narrativo",
    "finished": false
  }'

# Detalhar projeto
curl "$API/projects/1" \
  -H "Authorization: Bearer $TOKEN"

# Atualizar projeto
curl -X PUT "$API/projects/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Lighthouse Remastered"
  }'

# Remover projeto
curl -X DELETE "$API/projects/1" \
  -H "Authorization: Bearer $TOKEN"

########################################
# PROJECT ↔ PARTNERS (SPLIT)
########################################

# Listar partners do projeto
curl "$API/projects/1/partners" \
  -H "Authorization: Bearer $TOKEN"

# Sync partners (soma <= 100%)
curl -X PUT "$API/projects/1/partners" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "mode": "sync",
    "detach_missing": true,
    "partners": [
      { "partner_id": 1, "share_percent": 60 },
      { "partner_id": 2, "share_percent": 40 }
    ]
  }'

########################################
# REPORTS
########################################

# Listar reports
curl "$API/reports" \
  -H "Authorization: Bearer $TOKEN"

# Criar report
curl -X POST "$API/reports" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Steam 2025-09",
    "platform": "steam",
    "period_month": "2025-09",
    "currency": "USD",
    "gross_amount": 10000,
    "fees": 500,
    "taxes": 1000
  }'

# Detalhar report
curl "$API/reports/1" \
  -H "Authorization: Bearer $TOKEN"

########################################
# ALOCAR REPORT EM PROJETOS
########################################
# ATENÇÃO: remover "die()" no controller para funcionar

curl -X POST "$API/reports/1/allocate" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "overwrite": true,
    "allocations": [
      {
        "project_id": 1,
        "project_net_amount": 8500,
        "currency": "USD"
      }
    ]
  }'

########################################
# PAYOUTS
########################################

# Gerar payouts
curl -X POST "$API/reports/1/generate-payouts" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reset_existing": true
  }'

# Listar payouts
curl "$API/payouts?report_id=1" \
  -H "Authorization: Bearer $TOKEN"

# Detalhar payout
curl "$API/payouts/1" \
  -H "Authorization: Bearer $TOKEN"

# Atualizar payout
curl -X PUT "$API/payouts/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "scheduled",
    "method": "pix"
  }'

# Marcar payout como pago (com anexo)
curl -X POST "$API/payouts/1/mark-paid" \
  -H "Authorization: Bearer $TOKEN" \
  -F "paid_at=2026-01-25 10:00:00" \
  -F "method=pix" \
  -F "receipt=@/caminho/comprovante.pdf"

# Remover payout
curl -X DELETE "$API/payouts/1" \
  -H "Authorization: Bearer $TOKEN"

########################################
# FIM DO ARQUIVO
########################################
