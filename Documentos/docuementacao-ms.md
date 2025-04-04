# Documentação de Arquitetura e Funcionalidades - App de Finanças

## Visão Geral
Este documento detalha a arquitetura, tecnologias utilizadas, requisitos funcionais e especificações detalhadas dos microserviços que compõem o app de finanças.

### Tecnologias Utilizadas
- **Linguagem:** PHP (>= 8.0) + OpenSwoole
- **Mensageria:** RabbitMQ
- **Banco de Dados:** MySQL, MongoDB
- **Cache:** Redis
- **Autenticação:** JWT (com chave compartilhada JWT_SECRET)
- **PDF:** DomPDF
- **Validação e ORM:** Illuminate (Validation + Eloquent)
- **Variáveis de Ambiente:** vlucas/phpdotenv
- **Integração com MongoDB:** Recomendado usar [jenssegers/laravel-mongodb](https://github.com/jenssegers/laravel-mongodb) (compatível com Eloquent)

### API Gateway
- **Tecnologia:** NGINX
- **Função:** Load balancer, roteamento de requisições, endpoint principal

---

## Mensageria - RabbitMQ

### Exchanges
- **auth.user.inactivated (fanout)**: Usada para notificar os serviços quando um usuário é inativado. Serviços ouvintes devem responder desativando os dados relacionados.
- **auth.user.created (fanout)**: Usada para notificar serviços (como o de Caixa) para criação automática de um caixa inicial quando um usuário é registrado.
- **transacao.registrada (direct)**: Dispara eventos para atualização de saldo e geração de relatório após transações.
- **auth.user.inactivated (fanout)**: Usada para notificar os serviços quando um usuário é inativado. Serviços ouvintes devem responder desativando os dados relacionados.
- **transacao.registrada (direct)**: Dispara eventos para atualização de saldo e geração de relatório após transações.

### Filas
- **caixa.criar-inicial**: Consumida pelo serviço de Caixa para criar automaticamente o primeiro caixa ao registrar um novo usuário.
- **caixa.inativar-usuario**: Consumida pelo serviço de Caixa para desativar todos os caixas do usuário inativado.
- **saldo.inativar-usuario**: Consumida pelo serviço de Relatórios e Saldo para desativar os saldos do usuário.
- **saldo.atualizar**: Recebe eventos com `usuario_id`, `caixa_id`, `novo_valor` e `valor_total` para manter os saldos em sincronia após cada transação.
- **relatorio.gerar**: Usada para iniciar a geração de relatório com base nas transações registradas.
- **caixa.inativar-usuario**: Consumida pelo serviço de Caixa para desativar todos os caixas do usuário inativado.
- **saldo.inativar-usuario**: Consumida pelo serviço de Relatórios e Saldo para desativar os saldos do usuário.
- **saldo.atualizar**: Recebe eventos de atualização de saldo após cada transação.
- **relatorio.gerar**: Usada para iniciar a geração de relatório com base nas transações registradas.

### Fluxo: Inativação de Usuário
1. Serviço de Autenticação publica na exchange `auth.user.inactivated` com payload:
```jsonjson
{
  "usuario_id": 123
}
```
2. A exchange `fanout` envia esse evento para:
    - `caixa.inativar-usuario`
    - `saldo.inativar-usuario`
3. Cada serviço consome a mensagem e executa os devidos procedimentos de inativação.

---

### Fluxo: Criação de Usuário
1. Após o registro, o serviço de Autenticação publica na exchange `auth.user.created` com payload:
```json
{
  "usuario_id": 123,
  "email": "usuario@exemplo.com"
}
```
2. A mensagem é entregue à fila `caixa.criar-inicial`, onde o serviço de Caixa cria automaticamente um caixa padrão para o novo usuário.

---

### Fluxo: Registro de Transação
1. O serviço de Transações publica na exchange `transacao.registrada` com payload:
```json
{
  "usuario_id": 123,
  "caixa_id": 456,
  "nome_caixa": "Carteira Digital",
  "valor": 100.0
}
```
2. A fila `saldo.atualizar` é consumida pelo serviço de Relatórios e Saldo, que:
    - Atualiza o saldo da caixa (`caixa_id`)
    - Atualiza o saldo total do usuário (`usuario_id`)

---

## Requisitos Funcionais

1. O sistema deve permitir o cadastro e login de usuários com autenticação via JWT.
2. Cada usuário deve poder criar, atualizar e excluir seus próprios caixas (ex: carteiras, contas).
3. Usuários devem registrar transações de entrada e saída associadas a um caixa.
4. O saldo de cada usuário deve ser atualizado automaticamente após cada transação.
5. O sistema deve gerar relatórios financeiros em PDF por período.
6. O sistema deve permitir visualização em tempo real do saldo por meio de SSE (Server-Sent Events).
7. Deve existir controle de acesso em todos os serviços com verificação de token JWT.
8. O sistema deve ser escalável, com serviços desacoplados e comunicação via RabbitMQ.

---

## Microserviços

### 1. Serviço de Autenticação
#### Integração com Mensageria
- Ao inativar um usuário, o serviço publica mensagens no RabbitMQ para notificar os demais serviços sobre a mudança de status. Os serviços de Caixa e de Relatórios e Saldo devem reagir a essa mensagem para inativar os registros relacionados ao usuário.

#### Responsabilidades
- Cadastro e login de usuários com validação de dados.
- Geração de tokens JWT.
- Armazenamento de tokens gerados no Redis para fins de rastreamento e auditoria.

> Observação: A validação do token JWT será feita localmente em cada microserviço, utilizando a mesma chave secreta (JWT_SECRET) compartilhada entre todos os serviços. Portanto, este serviço **não** é responsável por verificar tokens emitidos anteriormente nem por renová-los. A cada login, um novo token será gerado e armazenado no Redis como parte do registro de sessões ativas. Portanto, este serviço **não** é responsável por verificar tokens emitidos anteriormente.

#### Funcionalidades
- Verificar unicidade de email no momento do cadastro.
- Criptografar senha com algoritmo seguro (bcrypt recomendado).
- Armazenar data/hora de último login.
- Armazenar o token JWT gerado no Redis com tempo de expiração.
- Limitar tentativas de login com bloqueio temporário por IP (opcional).
- Permitir consulta de informações de usuários autenticados.
- Permitir verificação externa da existência e status de um usuário (ativo ou não).
- Criptografar senha com algoritmo seguro (bcrypt recomendado).
- Armazenar data/hora de último login.
- Armazenar o token JWT gerado no Redis com tempo de expiração.
- Limitar tentativas de login com bloqueio temporário por IP (opcional).
- Criptografar senha com algoritmo seguro (bcrypt recomendado).
- Armazenar data/hora de último login.
- Limitar tentativas de login com bloqueio temporário por IP (opcional).

#### Endpoints
- `POST /register`: Cria um novo usuário com email e senha.
- `POST /login`: Verifica credenciais e retorna um novo JWT, armazenando o token no Redis.
- `GET /user`: Retorna os dados do usuário autenticado (baseado no JWT).
- `GET /user/{id}`: Retorna os dados de um usuário pelo ID (uso interno).
- `GET /users`: Lista todos os usuários (uso interno/restrito).
- `GET /user/verify/{id}`: Verifica se o usuário existe e está ativo (único endpoint externo).
- `PUT /user/{id}`: Atualiza os dados de um usuário.
- `DELETE /user/{id}`: Remove um usuário do sistema.

---

### 2. Serviço de Caixa
#### Banco de Dados
- **Tipo:** Relacional (MySQL)
- **Tabelas principais:** `caixas`
- **Campos:** id, usuario_id, nome, descricao, ativo, criado_em, atualizado_em

#### Dependências
- Antes de criar um novo caixa, o serviço consulta o endpoint `GET /user/verify/{id}` do Serviço de Autenticação para garantir que o usuário existe e está ativo.

#### Responsabilidades
- Criar caixas vinculados a usuários (ex: contas, carteiras, etc).
- Atualizar e gerenciar múltiplos caixas por usuário.
- Controle de permissões por usuário sobre cada caixa.

#### Funcionalidades
- Criar estrutura de caixas com nome, descrição, data de criação.
- Permitir múltiplos caixas por usuário.
- Garantir que um usuário não possa acessar caixa de outro.
- Possibilidade futura de compartilhamento de caixas com múltiplos usuários.

#### Endpoints
- `POST /caixas`: Cria um novo caixa.
- `GET /caixas/{id}`: Detalha um caixa específico.
- `PUT /caixas/{id}`: Atualiza dados de um caixa.
- `DELETE /caixas/{id}`: Remove um caixa.
- `GET /usuarios/{id}/caixas`: Lista todos os caixas de um usuário.

---

### 3. Serviço de Transações
#### Banco de Dados
- **Tipo:** Não Relacional (MongoDB)
- **Coleção principal:** `transacoes`
- **Campos:**
    - `_id`: Identificador único da transação
    - `usuario_id`: ID do usuário que realizou a transação
    - `caixa_id`: ID do caixa ao qual a transação pertence
    - `tipo`: Tipo da transação (`entrada` ou `saida`)
    - `valor`: Valor monetário da transação (positivo)
    - `descricao`: Texto descritivo sobre a transação
    - `data`: Data e hora em que a transação foi realizada
    - `criado_em`: Timestamp da criação do registro
    - `atualizado_em`: Timestamp da última modificação (se aplicável)
    - `referencia_externa`: Campo opcional para vincular a uma referência externa (ex: ID de pagamento externo)

#### Responsabilidades
- Registrar transações financeiras de entrada ou saída.
- Associar transações a caixas e usuários.
- Validar se o caixa e o usuário têm permissão para a operação.
- Consultar histórico de transações com filtros por data, tipo, valor, etc.

#### Funcionalidades
- Registrar transações com os campos: tipo (entrada/saída), valor, descrição, data, caixa_id.
- Verificar existência e pertencimento do caixa informado.
- Permitir filtros: período, tipo, caixa, valor mínimo/máximo.
- Validar que valores de transação não sejam negativos.

#### Endpoints
- `POST /transacoes`: Cria uma nova transação.
- `GET /transacoes`: Lista transações com filtros.
- `GET /transacoes/{id}`: Retorna detalhes de uma transação.

---

### 4. Serviço de Relatórios e Saldo
#### Dependências
- Para geração de relatórios, este serviço consome o microserviço de Transações através de chamadas HTTP para buscar todas as transações realizadas pelo usuário no período solicitado. Essas transações são utilizadas para compor os dados do relatório financeiro em PDF.

#### Banco de Dados
- **Tipo:** Não Relacional (MongoDB)
- **Coleções principais:**
    - `saldos`: Armazena o saldo atual de cada caixa e o total consolidado por usuário. O nome do caixa é incluído no documento por meio de mensagens recebidas via RabbitMQ.
        - **Campos:**
            - `_id`: identificador único
            - `usuario_id`: ID do usuário
            - `caixa_id`: ID do caixa
            - `valor`: valor atual do caixa
            - `valor_total`: valor total consolidado do usuário (campo presente apenas em documentos que representam o total consolidado)
            - `nome_caixa`: nome do caixa correspondente (recebido via RabbitMQ)
            - `ativo`: booleano indicando se está ativo
            - `atualizado_em`: data da última atualização
    - `relatorios`: Armazena registros de relatórios financeiros gerados.
        - **Campos:**
            - `_id`: identificador único
            - `usuario_id`: ID do usuário
            - `periodo_inicio`: início do período coberto
            - `periodo_fim`: fim do período coberto
            - `pdf_path`: caminho para o arquivo PDF gerado
            - `gerado_em`: data/hora da geração
            - `transacoes`: lista resumida de transações incluídas
            - `resumo`: totais por tipo (entrada/saída) e por caixa

#### Responsabilidades
- Cálculo de saldo de cada caixa e usuário.
- Atualização automática de saldo ao receber evento de transação.
- Disponibilização de dados via SSE (Server-Sent Events).
- Geração de relatórios financeiros em PDF.

#### Funcionalidades
- Cálculo de saldo total por usuário e por caixa.
- Atualização imediata ao registrar nova transação.
- Exportação de relatórios com transações agrupadas por tipo e data.
- Geração de gráficos simples para anexar no PDF (opcional).
- Cache de relatórios gerados para evitar retrabalho.

#### Endpoints
- `GET /saldo/{usuario_id}`: Retorna o saldo atual.
- `GET /relatorios/pdf?usuario_id=X&periodo=Y`: Gera e retorna PDF do relatório financeiro.

---