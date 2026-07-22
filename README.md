\# API de Integração AGHU / SIGA



\## Objetivo



Esta API PHP tem como objetivo funcionar como uma camada intermediária entre o banco de dados hospitalar (AGHU) e a API principal do SIGA.



A responsabilidade desta API é consultar os dados necessários no ambiente hospitalar e disponibilizá-los através de endpoints REST em formato JSON para consumo da API SIGA (.NET).



\## Arquitetura



```

API SIGA (.NET)

&#x20;       |

&#x20;       | HTTP / JSON

&#x20;       |

API Integração PHP

&#x20;       |

&#x20;       | PostgreSQL

&#x20;       |

Banco AGHU Hospitalar

```



\---



\# 1. Configuração do banco de dados



Antes da execução da API, deve ser configurado o acesso ao banco de dados hospitalar.



Arquivo:



```

/config/config.php

```



Configuração atual de desenvolvimento:



```php

'db' => \[

&#x20;   'driver'   => 'pgsql',

&#x20;   'host'     => 'localhost',

&#x20;   'port'     => 5432,

&#x20;   'database' => 'nextudb',

&#x20;   'username' => 'postgres',

&#x20;   'password' => '13676616766',

&#x20;   'charset'  => 'utf8'

]

```



Essa configuração deve ser substituída pelos dados reais do ambiente hospitalar.



Exemplo:



```php

'db' => \[

&#x20;   'driver'   => 'pgsql',

&#x20;   'host'     => 'SERVIDOR\_POSTGRES',

&#x20;   'port'     => 5432,

&#x20;   'database' => 'BANCO\_AGHU',

&#x20;   'username' => 'USUARIO\_LEITURA',

&#x20;   'password' => 'SENHA',

&#x20;   'charset'  => 'utf8'

]

```



\## Informações necessárias



O TI deverá disponibilizar:



\* Host/IP do PostgreSQL;

\* Porta de conexão;

\* Nome do banco;

\* Usuário de acesso;

\* Senha;

\* Schema utilizado pelo AGHU.



\---



\# 2. Permissões do banco



O usuário utilizado pela API deve possuir apenas permissão de leitura.



Necessário:



\* `SELECT` nas tabelas/views utilizadas;

\* acesso ao schema do AGHU.


Não são necessárias permissões de:



\* INSERT;

\* UPDATE;

\* DELETE;

\* CREATE.



A API possui comportamento somente leitura.



\---



\# 3. Implementação das consultas



As consultas estão localizadas nos repositories:



```

/repositories

```



Durante o desenvolvimento foram utilizadas consultas temporárias apenas para validar conexão.



Exemplo:



```sql

SELECT \*

FROM users

LIMIT 10

```



Essas consultas devem ser substituídas pelas consultas reais do banco AGHU.



\---



\# 4. Repository de profissionais



Arquivo:



```

/repositories/profissionalRepository.php

```



A consulta definitiva deverá retornar os seguintes campos:



| Campo         | Descrição                     |

| ------------- | ----------------------------- |

| id            | Identificador do profissional |

| nome          | Nome completo                 |

| login         | Usuário de rede               |

| especialidade | Especialidade                 |

| matricula     | Matrícula funcional           |

| email         | Email institucional           |

| setor         | Setor/unidade                 |



Exemplo de retorno:



```json

\[

&#x20;   {

&#x20;       "id": 1,

&#x20;       "nome": "Jonatas Silva",

&#x20;       "login": "jonatas.silva",

&#x20;       "especialidade": "Anestesiologia",

&#x20;       "matricula": "123456",

&#x20;       "email": "usuario@hospital.com",

&#x20;       "setor": "Centro Cirúrgico"

&#x20;   }

]

```



\---



\# 5. Endpoints disponibilizados



\## Usuários



Responsável por retornar dados dos usuários do sistema.



Informações esperadas:



\* Login;

\* Nome;

\* Matrícula;

\* Email;

\* Setor;

\* Perfil/permissão (caso exista).



\---



\## Profissionais



Endpoint:



```

GET /profissionais

```



Responsável por retornar os profissionais disponíveis no hospital.



\---



\## Cirurgias



Endpoint:



```

GET /cirurgias

```



Responsável por retornar as cirurgias programadas.



Dados esperados:



\* Identificação da cirurgia;

\* Paciente;

\* Procedimento;

\* Data/hora;

\* Sala;

\* Equipe;

\* Status.



\---



\# 6. Configuração da API



Arquivo:



```

/config/config.php

```



Configuração:



```php

'api' => \[

&#x20;   'basePath' => '/aghu-api/public',

&#x20;   'timezone' => 'America/Sao\_Paulo',

&#x20;   'debug' => true

]

```



Em ambiente produtivo:



Alterar:



```php

'debug' => false

```



\---



\# 7. Publicação



A API pode ser publicada utilizando:



\* Apache;

\* Nginx;

\* Docker.

\* Php somente

Publicar a API somente com o PHP

Dentro da pasta 'public' rodar : php -S localhost:8000 ( outra porta que precise )
Exemplo de chamada: http://localhost:8000/profissionais




Após publicação deverá ser disponibilizada uma URL interna.



Exemplo:



```

http://servidor-hospital/aghu-api/public

```



\---



\# 8. Configuração de CORS



Configuração atual:



```php

'cors' => \[

&#x20;   'enabled' => true,

&#x20;   'origin' => '\*'

]

```



Em produção recomenda-se restringir o acesso para a API SIGA.



Exemplo:



```php

'origin' => 'http://servidor-siga'

```



\---



\# 9. Validação da integração



Após configuração devem ser realizados os testes:



\## Banco



\* Validar conexão PostgreSQL;

\* Validar acesso ao schema AGHU;

\* Validar execução das consultas.



\## API



Validar:



\* Retorno de usuários;

\* Retorno de profissionais;

\* Retorno de cirurgias.



\## Integração



Validar consumo pela API SIGA (.NET).



\---



\# 10. Informações para disponibilizar à equipe SIGA



Após implantação, fornecer:



\* URL base da API PHP;

\* Lista dos endpoints;

\* Documentação dos retornos JSON;

\* Ambiente de homologação;

\* Usuários de teste (caso necessário).



\---



\# Checklist de implantação



\## Banco



\* \[ ] Configurar conexão PostgreSQL AGHU

\* \[ ] Validar usuário de leitura

\* \[ ] Validar permissões no schema



\## API PHP



\* \[ ] Atualizar arquivo de configuração

\* \[ ] Substituir consultas temporárias

\* \[ ] Implementar consultas definitivas

\* \[ ] Desativar debug



\## Integração SIGA



\* \[ ] Disponibilizar URL da API

\* \[ ] Validar endpoints

\* \[ ] Validar consumo pela API .NET



