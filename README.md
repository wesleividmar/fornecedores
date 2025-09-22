# Fornecedores API — Migração do Legado (Laravel)

API em **Laravel** que migra uma parte do legado PHP 7.4 (procedural) para uma arquitetura moderna com **FormRequest**, **Service Layer**, **Eloquent**, **Resources** e **Testes de Feature**.
Foco: cadastro e listagem de fornecedores com sanitização/validação de **CNPJ**.

## Stack

* PHP 8.2+ (testado em 8.4)
* Laravel 12
* SQLite (default)
* PHPUnit 11
* Laravel Pint (PSR-12)

---

## 🚀 Como rodar

### 1) Clonar & instalar

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 2) Banco (SQLite)

Crie o arquivo do banco e aponte no `.env`.

**Windows (PowerShell):**

```powershell
New-Item -ItemType File -Path .\database\database.sqlite -Force | Out-Null
# No .env:
# DB_CONNECTION=sqlite
# DB_DATABASE=C:\wamp64\www\fornecedorestest\database\database.sqlite
```

**Linux/Mac:**

```bash
touch database/database.sqlite
# No .env:
# DB_CONNECTION=sqlite
# DB_DATABASE=/caminho/absoluto/para/o/projeto/database/database.sqlite
```

### 3) Migrar (e opcionalmente semear)

```bash
php artisan migrate
# Opcional (se houver seeder):
# php artisan db:seed --class=FornecedorSeeder
```

### 4) Subir servidor

```bash
php artisan serve
# http://127.0.0.1:8000
```

---

## 📚 Endpoints

Base URL: `/api`

### POST `/fornecedores`

Cria um fornecedor.

**Body (JSON):**

```json
{
  "nome": "Acme Ltda",
  "cnpj": "11.222.333/0001-81",
  "email": "contato@acme.com"
}
```

* O **CNPJ** é **sanitizado** (mantém apenas dígitos) e **validado** por regra customizada.
* **Retornos:**

  * `201 Created` com objeto `data`
  * `422 Unprocessable Content` com `{ message, errors }` se falhar validação

**Exemplo (curl):**

```bash
curl -s -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"nome":"Acme Ltda","cnpj":"11.222.333/0001-81","email":"contato@acme.com"}' \
  http://127.0.0.1:8000/api/fornecedores
```

### GET `/fornecedores?nome=ac&limit=50`

Lista fornecedores (filtro por nome e limite de resultados).

**Query params:**

* `nome` (opcional): filtro `LIKE`
* `limit` (opcional, padrão 50, 1..1000)

**Exemplo (curl):**

```bash
curl -s "http://127.0.0.1:8000/api/fornecedores?nome=ac&limit=10"
```

**Formato de resposta (Resource):**

```json
{
  "data": [
    {
      "id": 1,
      "nome": "Acme Ltda",
      "cnpj": "11222333000181",
      "email": "contato@acme.com",
      "created_at": "2025-09-22T17:03:08.000000Z",
      "updated_at": "2025-09-22T17:03:08.000000Z"
    }
  ]
}
```

---

## 🧱 Decisões de Arquitetura

* **Validação**: `FormRequest`

  * `StoreFornecedorRequest` (sanitiza `cnpj` no `prepareForValidation` e valida)
  * `IndexFornecedorRequest` (normaliza `nome`/`limit` vindos como query)
* **Regra customizada**: `App\Rules\Cnpj` (valida formato/lógica do CNPJ)
* **Service Layer**: `FornecedorService`

  * Sanitiza CNPJ, verifica unicidade, usa **transação** no `store`
* **Model**: `Fornecedor` (Eloquent, `fillable`, `SoftDeletes`)
* **Migration**: cria `fornecedores` com `unique` em `cnpj`
* **Resource**: `FornecedorResource` padroniza o JSON
* **Rotas**: `routes/api.php` (préfixo `/api`, middleware `api`)
* **Provider**: `AppServiceProvider` faz o bind da interface do serviço

Estrutura relevante:

```
app/
  Http/
    Controllers/Api/FornecedorController.php
    Requests/ApiRequest.php
    Requests/StoreFornecedorRequest.php
    Requests/IndexFornecedorRequest.php
    Resources/FornecedorResource.php
  Models/Fornecedor.php
  Rules/Cnpj.php
  Services/Contracts/FornecedorServiceInterface.php
  Services/FornecedorService.php
database/
  migrations/xxxx_xx_xx_xxxxxx_create_fornecedors_table.php
  factories/FornecedorFactory.php
routes/
  api.php
tests/
  Feature/Api/FornecedorApiTest.php
legacy/
  fornecedor_legacy.php  (código legado para referência)
```

---

## ✅ Testes

Executar todos:

```bash
php artisan test
```

Os testes de Feature cobrem:

* **Criação com sucesso** (`201`)
* **Falha de validação** (nome curto, CNPJ inválido) (`422`)
* **Busca filtrada** por `nome`

---

## 🧹 Padrão de código (PSR-12)

Projeto formatado com **Laravel Pint**.

* Testar estilo:

  ```bash
  ./vendor/bin/pint --test
  ```
* Corrigir:

  ```bash
  ./vendor/bin/pint
  ```

Para evitar problemas de EOL no Windows, adicione (já incluso no projeto):

**.editorconfig**

```
root = true
[*]
charset = utf-8
indent_style = space
indent_size = 4
end_of_line = lf
insert_final_newline = true
trim_trailing_whitespace = true
```

**.gitattributes**

```
* text=auto eol=lf
```

---

## 🔒 Segurança & Boas Práticas

* **SQL Injection**: prevenido via Eloquent/Query Builder (binds) e validação.
* **Transações**: usadas no `store` para consistência.
* **Validação forte**: `FormRequest` + regra de CNPJ.
* **Soft Deletes**: habilitado em `Fornecedor` (bônus).
* **PSR-12**: garantido por Pint.

---

## 🗺️ Plano de Migração

Documento curto com as etapas (dados, mapeamento de campos, validações e estratégia incremental) está em: `docs/PlanoMigracao.md`.
(O código legado `fornecedor_legacy.php` está em `legacy/` apenas para referência.)

---

## 🧪 Dados de teste (opcional)

Gerar alguns fornecedores via factory (Tinker):

```bash
php artisan tinker
>>> \App\Models\Fornecedor::factory()->count(10)->create();
```

---

## 🧩 Troubleshooting

* **Erros HTML em validação** → enviar `Accept: application/json` no header.
* **Windows/PowerShell** → ao usar `Invoke-RestMethod`, passe tudo na mesma linha.
* **SQLite não conecta** → confirme o caminho **absoluto** no `DB_DATABASE`.

---

## Licença

Uso apenas para avaliação técnica / estudo.

