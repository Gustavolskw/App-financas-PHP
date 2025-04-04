Entendido! Vou te fornecer uma documentação detalhada para o seu sistema com base na última explicação que você gostou, incluindo:

- **Detalhes dos bancos**: Tabelas e entidades para MySQL (SQL), coleções para MongoDB (NoSQL), e chaves para Redis.
- **RabbitMQ**: Filas e funções para mensageria entre os microserviços.
- **Endpoints HTTP**: Para chamadas entre microserviços e para o API Gateway, focando em Autenticação, Caixas, Transações e Relatórios (sem Notificações, como você pediu).

Vou organizar tudo de forma clara e prática para você aplicar no seu projeto com PHP (Laravel/OpenSwoole), Vue.js e RabbitMQ. Vamos lá!

---

## Arquitetura Geral

- **Bancos**:
  - MySQL: Autenticação e Caixas.
  - MongoDB: Transações.
  - Redis: Cache para Relatórios e saldos/transações recentes.
- **Comunicação**:
  - HTTP: Para chamadas síncronas entre microserviços e com o API Gateway.
  - RabbitMQ: Para eventos assíncronos entre serviços.

---

## 1. Serviço de Autenticação

### Banco de Dados: MySQL

- **Tabela: `users`**
  - `id` (int, auto_increment, primary key): ID do usuário.
  - `name` (varchar): Nome do usuário.
  - `email` (varchar, unique): E-mail do usuário.
  - `password` (varchar): Senha criptografada (ex.: bcrypt).
  - `created_at` (timestamp): Data de criação.
  - `updated_at` (timestamp): Data de atualização.

### Endpoints HTTP

- **Base URL**: `/auth`
- **Métodos**:
  1. **POST /login**
     - **Descrição**: Autentica o usuário e retorna um token JWT.
     - **Body**: `{ "email": "string", "password": "string" }`
     - **Resposta**: `{ "token": "jwt_token", "user": { "id": int, "name": "string", "email": "string" } }`
     - **Status**: 200 (OK), 401 (Unauthorized).
  2. **POST /register**
     - **Descrição**: Registra um novo usuário.
     - **Body**: `{ "name": "string", "email": "string", "password": "string" }`
     - **Resposta**: `{ "user": { "id": int, "name": "string", "email": "string" }, "token": "jwt_token" }`
     - **Status**: 201 (Created), 400 (Bad Request).
  3. **GET /me**
     - **Descrição**: Retorna os dados do usuário autenticado.
     - **Headers**: `Authorization: Bearer {token}`
     - **Resposta**: `{ "id": int, "name": "string", "email": "string" }`
     - **Status**: 200 (OK), 401 (Unauthorized).

### RabbitMQ

- Não publica eventos diretamente, mas pode ser consumido por outros serviços (ex.: validar usuário via HTTP).

---

## 2. Serviço de Caixas

### Banco de Dados: MySQL

- **Tabela: `caixas`**
  - `id` (int, auto_increment, primary key): ID do caixa.
  - `user_id` (int, foreign key → users.id): ID do usuário dono do caixa.
  - `name` (varchar): Nome do caixa (ex.: "Caixa Corrente").
  - `balance` (decimal(15,2)): Saldo atual do caixa.
  - `created_at` (timestamp): Data de criação.
  - `updated_at` (timestamp): Data de atualização.

### Cache: Redis

- **Chave**: `caixa:user:{user_id}:{caixa_id}`
  - **Valor**: Saldo atual (ex.: `700.50`).
  - **TTL**: 10 minutos (atualizado após transações).

### Endpoints HTTP

- **Base URL**: `/caixas`
- **Métodos** (todos exigem `Authorization: Bearer {token}`):
  1. **GET /**
     - **Descrição**: Lista todos os caixas do usuário autenticado.
     - **Resposta**: `[ { "id": int, "name": "string", "balance": float } ]`
     - **Status**: 200 (OK).
  2. **POST /**
     - **Descrição**: Cria um novo caixa.
     - **Body**: `{ "name": "string" }`
     - **Resposta**: `{ "id": int, "name": "string", "balance": 0.00 }`
     - **Status**: 201 (Created).
  3. **GET /{id}**
     - **Descrição**: Retorna detalhes de um caixa específico.
     - **Resposta**: `{ "id": int, "name": "string", "balance": float }`
     - **Status**: 200 (OK), 404 (Not Found).
  4. **PUT /{id}**
     - **Descrição**: Atualiza o nome de um caixa.
     - **Body**: `{ "name": "string" }`
     - **Resposta**: `{ "id": int, "name": "string", "balance": float }`
     - **Status**: 200 (OK), 404 (Not Found).
  5. **DELETE /{id}**
     - **Descrição**: Deleta um caixa (se saldo for zero).
     - **Resposta**: `{ "message": "Caixa deletado" }`
     - **Status**: 200 (OK), 400 (Bad Request).

### RabbitMQ

- **Fila Consumida**: `transacao.atualizar_saldo`
  - **Mensagem**: `{ "caixa_id": int, "valor": float, "tipo": "entrada|saida|movimentacao" }`
  - **Função**: Atualiza o saldo do caixa no MySQL e no Redis.
- **Fila Publicada**: Nenhuma (atualizações são síncronas via HTTP ou assíncronas via Transações).

---

## 3. Serviço de Transações

### Banco de Dados: MongoDB

- **Coleção: `transacoes`**
  - `_id` (ObjectId): ID da transação.
  - `user_id` (int): ID do usuário.
  - `type` (string): Tipo da transação ("entrada", "saida", "movimentacao").
  - `amount` (float): Valor da transação.
  - `caixa_origem_id` (int): ID do caixa de origem.
  - `caixa_destino_id` (int, opcional): ID do caixa de destino (para movimentações).
  - `description` (string, opcional): Descrição (ex.: "Salário").
  - `created_at` (timestamp): Data da transação.

### Cache: Redis

- **Chave**: `transacoes:user:{user_id}:recentes`
  - **Valor**: Lista das últimas 5 transações (JSON serializado).
  - **TTL**: 10 minutos.

### Endpoints HTTP

- **Base URL**: `/transacoes`
- **Métodos** (todos exigem `Authorization: Bearer {token}`):
  1. **GET /**
     - **Descrição**: Lista transações do usuário (filtráveis por data ou caixa).
     - **Query Params**: `?caixa_id=int&start_date=string&end_date=string`
     - **Resposta**: `[ { "id": string, "type": "string", "amount": float, "caixa_origem_id": int, "caixa_destino_id": int|null, "description": "string", "created_at": "string" } ]`
     - **Status**: 200 (OK).
  2. **POST /entrada**
     - **Descrição**: Registra uma entrada em um caixa.
     - **Body**: `{ "caixa_id": int, "amount": float, "description": "string" }`
     - **Resposta**: `{ "id": string, "type": "entrada", "amount": float, "caixa_id": int }`
     - **Ação**: Publica em `transacao.atualizar_saldo`.
     - **Status**: 201 (Created).
  3. **POST /saida**
     - **Descrição**: Registra uma saída de um caixa.
     - **Body**: `{ "caixa_id": int, "amount": float, "description": "string" }`
     - **Resposta**: `{ "id": string, "type": "saida", "amount": float, "caixa_id": int }`
     - **Ação**: Publica em `transacao.atualizar_saldo`.
     - **Status**: 201 (Created).
  4. **POST /movimentacao**
     - **Descrição**: Registra uma movimentação entre caixas.
     - **Body**: `{ "caixa_origem_id": int, "caixa_destino_id": int, "amount": float, "description": "string" }`
     - **Resposta**: `{ "id": string, "type": "movimentacao", "amount": float, "caixa_origem_id": int, "caixa_destino_id": int }`
     - **Ação**: Publica em `transacao.atualizar_saldo` (duas mensagens: saída da origem, entrada no destino).
     - **Status**: 201 (Created).

### RabbitMQ

- **Fila Publicada**: `transacao.atualizar_saldo`
  - **Mensagem**: `{ "caixa_id": int, "valor": float, "tipo": "entrada|saida|movimentacao" }`
  - **Função**: Notifica o Serviço de Caixas para atualizar o saldo.

---

## 4. Serviço de Relatórios

### Banco de Dados: Redis

- **Chave**: `relatorio:user:{user_id}:saldo_total`
  - **Valor**: Saldo consolidado de todos os caixas (float).
- **Chave**: `relatorio:user:{user_id}:entradas:{mes}`
  - **Valor**: Total de entradas no mês (float).
- **Chave**: `relatorio:user:{user_id}:saidas:{mes}`
  - **Valor**: Total de saídas no mês (float).

### Fonte de Dados

- MySQL (saldos via HTTP do Serviço de Caixas).
- MongoDB (transações via HTTP do Serviço de Transações).

### Endpoints HTTP

- **Base URL**: `/relatorios`
- **Métodos** (todos exigem `Authorization: Bearer {token}`):
  1. **GET /saldo-total**
     - **Descrição**: Retorna o saldo total de todos os caixas.
     - **Resposta**: `{ "saldo_total": float }`
     - **Ação**: Consulta Redis ou recalcula via HTTP (Caixas).
     - **Status**: 200 (OK).
  2. **GET /mensal**
     - **Descrição**: Retorna entradas e saídas de um mês.
     - **Query Params**: `?mes=YYYY-MM`
     - **Resposta**: `{ "entradas": float, "saidas": float, "saldo": float }`
     - **Ação**: Consulta Redis ou recalcula via HTTP (Transações).
     - **Status**: 200 (OK).
  3. **GET /por-caixa**
     - **Descrição**: Retorna relatório por caixa.
     - **Query Params**: `?caixa_id=int&mes=YYYY-MM`
     - **Resposta**: `{ "caixa_id": int, "entradas": float, "saidas": float, "saldo": float }`
     - **Ação**: Consulta Redis + HTTP (Caixas e Transações).
     - **Status**: 200 (OK).

### RabbitMQ

- Não publica ou consome filas diretamente (usa HTTP para buscar dados).

---

## API Gateway

### Função

Centraliza as requisições do frontend (Vue.js) e roteia para os microserviços.

### Endpoints Expostos

- **/auth/** → Serviço de Autenticação.
- **/caixas/** → Serviço de Caixas.
- **/transacoes/** → Serviço de Transações.
- **/relatorios/** → Serviço de Relatórios.

---

## Considerações Finais

- **HTTP**: Use Laravel com Guzzle para chamadas entre serviços. Autenticação via JWT em todas as rotas protegidas.
- **RabbitMQ**: Configure uma exchange `transacao` com a fila `transacao.atualizar_saldo` para garantir que o Serviço de Caixas receba eventos de forma confiável.
- **Escalabilidade**: Comece com essa estrutura e, se necessário, separe o MySQL em instâncias distintas no futuro.

Se precisar de exemplos de código (ex.: modelo Laravel, consumidor RabbitMQ ou script MongoDB), é só pedir! O que achou?
