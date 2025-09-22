# Plano de Migração — Fornecedores (Legado → Laravel)

## 1) Objetivo

Migrar o endpoint legado de fornecedores (PHP 7.4 procedural) para **Laravel 12**, garantindo:

* Mesma funcionalidade (criar e listar com filtro).
* Segurança, validação e arquitetura limpa.
* Migração de dados sem perda e com possibilidade de rollback.

## 2) Estado atual (legado)

* Script único `fornecedor_legacy.php`:

  * `POST` insere com `mysqli` (sem transação/validação adequada).
  * `GET` lista com `LIKE` (risco de SQL injection).
* Tabela `fornecedores` (MySQL):

  ```
  id INT PK AI
  nome VARCHAR(255) NOT NULL
  cnpj VARCHAR(14) NOT NULL
  email VARCHAR(255) NULL
  criado_em DATETIME NOT NULL
  UNIQUE(cnpj)
  ```

## 3) Novo (Laravel)

* **Camadas**: Controller → FormRequest → Service → Model → Resource.
* **Validações**: `StoreFornecedorRequest` e `IndexFornecedorRequest`.
* **Regra customizada**: `App\Rules\Cnpj` (sanitiza/valida).
* **Transação** no `store`.
* **PSR-12** com Laravel Pint.
* **SoftDeletes** habilitado.
* **Endpoints**:

  * `POST /api/fornecedores`
  * `GET /api/fornecedores?nome=&limit=`

## 4) Mapeamento de dados

| Legado                | Novo (Laravel)           | Observações                               |         |
| --------------------- | ------------------------ | ----------------------------------------- | ------- |
| `id`                  | `id`                     | Mesma chave                               |         |
| `nome`                | `nome`                   | Validado (`min:3`)                        |         |
| `cnpj` (14, com mask) | `cnpj` (somente dígitos) | Sanitizar (`\D+`), único                  |         |
| `email`               | `email`                  | \`nullable                                | email\` |
| `criado_em`           | `created_at`             | `updated_at` recebe `created_at` na carga |         |
| —                     | `deleted_at`             | Para **soft deletes**                     |         |

## 5) Estratégia incremental (sem “big bang”)

1. **Preparação**

   * Subir nova API em ambiente paralelo (dev/stage).
   * Garantir variáveis de conexão ao **banco legado** via `DB::connection('legacy')` (se necessário para carga).
2. **Sincronização inicial (backfill)**

   * Migrar dados do legado → novo schema (ver §6).
   * Deduplicar por CNPJ.
3. **Testes**

   * Rodar `php artisan test` (sucesso, falha de validação, filtro).
   * Exercitar casos de borda (e-mails nulos, CNPJ com máscara).
4. **Dark launch**

   * Expor `/api/fornecedores` novo apenas para QA (feature flag/rota interna).
5. **Cutover**

   * Apontar o tráfego de produção para o **novo**.
   * Monitorar logs/erros; manter legado em *stand-by* por um ciclo.

## 6) Migração de dados (opções)

### 6.1 SQL direto (MySQL 8+)

```sql
-- Ajustar nomes de bancos conforme ambiente
INSERT INTO novo.fornecedores (id, nome, cnpj, email, created_at, updated_at)
SELECT  f.id,
        f.nome,
        REGEXP_REPLACE(f.cnpj, '[^0-9]', '') AS cnpj,
        NULLIF(f.email, ''),
        f.criado_em,
        f.criado_em
FROM legado.fornecedores f
ON DUPLICATE KEY UPDATE
  nome = VALUES(nome),
  email = VALUES(email);
```

> Sem `REGEXP_REPLACE`? Use `REPLACE(REPLACE(REPLACE(f.cnpj,'.',''),'/',''),'-','')`.

### 6.2 Comando Artisan (quando há 2 conexões)

Criar um *command* que leia `DB::connection('legacy')` e grave via Eloquent (ganha validação/sanitização reusando o Service).
Execução: `php artisan legacy:import-fornecedores`.

**Regras na importação**

* Sanitizar CNPJ para 14 dígitos.
* Ignorar/atualizar duplicados por CNPJ.
* `updated_at = created_at` na primeira carga.

## 7) Testes & Qualidade

* **Feature tests** cobrindo: criação ok, validação 422, filtro por nome.
* **Pint** para PSR-12: `./vendor/bin/pint` (CI opcional).

## 8) Observabilidade

* Logar falhas de validação (`422`) e exceções (Handler).
* Métricas simples: contagem de POST/GET e taxa de erro.

## 9) Riscos & Rollback

* **Risco**: divergência de dados durante o cutover.
* **Mitigação**: janela curta + congelar escrita no legado durante a carga final.
* **Rollback**: reverter rota para o legado e reexecutar import na retomada.

## 10) Checklist de saída

* [ ] Migrations aplicadas e índice único de CNPJ ativo
* [ ] Dados migrados e deduplicados
* [ ] Testes passando (`php artisan test`)
* [ ] Pint sem issues (`./vendor/bin/pint --test`)
* [ ] Rota `/api/fornecedores` respondendo igual/compatível ao legado
