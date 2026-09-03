# API de Integração AGHU / SIGA

API PHP "vanilla" (sem framework, sem Composer) que funciona como camada intermediária **somente leitura** entre o banco de dados hospitalar (AGHU/PostgreSQL, schema `aghu_stg`) e a API principal do sistema de Ficha Anestésica **SIGA (.NET)**.

A responsabilidade desta API é consultar os dados necessários no ambiente hospitalar e disponibilizá-los via endpoints REST em JSON para consumo pela API SIGA.

## Arquitetura

```
API SIGA (.NET)
       |
       | HTTP / JSON
       v
API de Integração PHP  (este repositório)
       |
       | PDO / PostgreSQL
       v
Banco AGHU Hospitalar (schema aghu_stg)
```

## Estrutura do projeto

```
public/
  index.php          Front controller: define as rotas e inicia o dispatch
  teste-db.php        Script avulso para testar a conexão com o banco
  .htaccess            Rewrite para Apache (ver "Pontos de atenção")

api/
  bootstrap.php        Autoload simples (sem Composer) de core/repositories/controllers/services
  config/config.php     Configuração de banco, API e CORS (lida via variáveis de ambiente)
  core/
    router.php          Router minimalista (GET/POST/PUT/DELETE, parâmetros {id} via regex)
    database.php         Conexão PDO singleton com PostgreSQL
    response.php          Helpers de resposta JSON (ok, badRequest, unauthorized, notFound, noContent)
  controllers/         Um controller por recurso, todos herdam de baseController
  repositories/        Consultas SQL ao schema aghu_stg, todos herdam de baseRepository (fetchAll/fetchOne/execute/scalar)
  services/
    AuthService.php     Orquestra autenticação (hoje sempre libera acesso — ver abaixo)
    LdapService.php      Bind LDAP contra o AD hospitalar (implementado, mas ainda não é chamado)

Dockerfile              PHP 8.3 + Apache + pdo_pgsql, DocumentRoot apontando para /public
```

## Requisitos

* PHP 8.3+ com extensões `pdo` e `pdo_pgsql`
* Acesso de rede ao PostgreSQL do AGHU
* Usuário de banco **somente leitura** no schema `aghu_stg`

## Configuração

Toda a configuração é feita por variáveis de ambiente, lidas em [api/config/config.php](api/config/config.php):

| Variável | Descrição |
| --- | --- |
| `DB_HOST` | Host/IP do PostgreSQL |
| `DB_PORT` | Porta do PostgreSQL |
| `DB_DATABASE` | Nome do banco |
| `DB_SCHEMA` | Schema do AGHU (hoje lido, mas ainda **não** aplicado à conexão — ver "Pontos de atenção") |
| `DB_USERNAME` | Usuário de leitura |
| `DB_PASSWORD` | Senha |

Outras opções ficam fixas no próprio `config.php`:

```php
'api' => [
    'basePath' => '/public',
    'timezone' => 'America/Sao_Paulo',
    'debug'    => true,       // desativar em produção
],
'cors' => [
    'enabled' => true,
    'origin'  => '*',         // restringir para o host da API SIGA em produção
],
```

## Como executar localmente

Sem Docker, usando o servidor embutido do PHP (rode a partir da pasta `public`, passando `index.php` como *router script* — sem isso, rotas como `/profissionais` retornam 404):

```bash
cd public
php -S localhost:8000 index.php
```

Exemplo de chamada: `http://localhost:8000/profissionais`

Com Docker:

```bash
docker build -t fa-uff-integrator .
docker run -p 8080:80 \
  -e DB_HOST=... -e DB_PORT=5432 -e DB_DATABASE=... \
  -e DB_SCHEMA=aghu_stg -e DB_USERNAME=... -e DB_PASSWORD=... \
  fa-uff-integrator
```

## Endpoints

Todas as respostas são JSON. Erros seguem o formato `{ "message": "..." }` com o status HTTP correspondente (`400`, `401`, `404`, `500`).

### `POST /auth`

Autenticação de usuário.

Corpo:
```json
{ "login": "usuario", "senha": "senha" }
```

Retorna os dados do profissional (`aghu_stg.profissionais`) em caso de sucesso, ou `401` se as credenciais forem inválidas.

### `GET /profissionais`

Lista os profissionais cadastrados (`aghu_stg.profissionais`).

### `GET /procedimentos`

Lista os procedimentos disponíveis (`aghu_stg.procedimentos`).

### `GET /medicamentos`

Lista os medicamentos ativos (`aghu_stg.medicamentos`).

### `GET /cirurgias`

Lista cirurgias, com paginação e filtros. Cada item já vem enriquecido com alergias do paciente e procedimentos da cirurgia.

Query params:

| Parâmetro | Descrição |
| --- | --- |
| `data` | Filtra por `data_cirurgia` |
| `termo` | Busca por nome do paciente ou número de prontuário |
| `status` | Filtra por status (`AGENDADA`, `EM_PREPARO`, `EM_PROGRESSO`, `CONCLUIDA`, `CANCELADA`) |
| `page` | Página (padrão 1) |
| `pageSize` | Itens por página (padrão 10) |

Resultado ordenado por prioridade de status (em progresso → em preparo → agendada → concluída → cancelada), depois por data e nome.

### `GET /cirurgias/por-ids`

Mesma listagem acima, mas restrita a um conjunto de IDs de cirurgia.

Query params: `ids` (lista separada por vírgula), `termo`, `status`, `page`, `pageSize`.

### `GET /cirurgias/{idPaciente}/{idCirurgia}`

Detalhe de uma cirurgia específica: dados do paciente, localização atual (unidade/leito/andar/quarto), alergias e procedimentos.

### `GET /saude`

Health check. Retorna `{ "online": true|false }` conforme a conectividade com o banco.

## Autenticação

`AuthService::autenticar()` hoje **sempre retorna `true`** — a validação de senha ainda não está ativa. Existe um `LdapService` já implementado (bind LDAP contra `ldap://servidor-ad`), mas ele ainda não é chamado pelo `AuthService`. Antes de ir para produção, é necessário decidir e ligar o mecanismo real de autenticação (LDAP ou outro).

## Pontos de atenção

* **`public/.htaccess` está vazio.** O `Dockerfile` habilita `mod_rewrite` e `AllowOverride All`, mas sem regras de rewrite no `.htaccess`, requisições para rotas como `/profissionais` não chegam ao `index.php` em um deploy Apache "puro" (fora do servidor embutido do PHP com router script). É necessário adicionar algo como:
  ```apache
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^ index.php [QSA,L]
  ```
* **`DB_SCHEMA` não é usado na conexão.** O schema é referenciado explicitamente em cada query (`aghu_stg.tabela`), então a variável de ambiente `DB_SCHEMA` hoje não tem efeito algum — ou ela é aplicada (via `search_path` na conexão) ou pode ser removida da configuração.
* **Sem gerenciamento de dependências (Composer).** O projeto usa um autoloader próprio (`api/bootstrap.php`), então não há `composer.json`/`vendor`. Qualquer nova dependência precisa ser avaliada quanto a essa limitação.
* **`public/teste-db.php`** é um script de diagnóstico manual de conexão (não faz parte da API); considerar removê-lo ou restringi-lo antes de publicar em produção, já que expõe mensagens de erro de conexão publicamente.
* **CORS liberado (`origin: '*'`)** e **`debug: true`** são adequados para desenvolvimento, mas devem ser restritos/desativados em produção.

## Checklist de implantação

**Banco**
- [ ] Configurar `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- [ ] Confirmar que o usuário tem apenas permissão de leitura no schema `aghu_stg`
- [ ] Validar conectividade via `GET /saude`

**API PHP**
- [ ] Resolver o rewrite do `.htaccess` (ou publicar via `php -S ... index.php`)
- [ ] Definir e ativar o mecanismo real de autenticação (`AuthService`/`LdapService`)
- [ ] Desativar `debug` e restringir `cors.origin` para o host da API SIGA
- [ ] Remover ou proteger `public/teste-db.php`

**Integração SIGA**
- [ ] Disponibilizar URL base da API para a equipe SIGA
- [ ] Validar consumo de todos os endpoints pela API .NET
