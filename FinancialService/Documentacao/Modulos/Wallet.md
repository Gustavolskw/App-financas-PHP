## ✅ Módulo: Carteira (Wallet)

### 🎯 Finalidade

Gerenciar a carteira financeira pessoal de cada usuário. A carteira é o ponto central que conecta todos os dados financeiros do sistema.

### 🔄 Integração com AuthService

* A carteira é **criada, inativada e reativada** exclusivamente a partir dos eventos enviados pelo `AuthService` via RabbitMQ:

  * `user.created` → cria uma nova carteira com `status = true`
  * `user.inactivated` → atualiza a carteira para `status = false`
  * `user.reactivated` → atualiza a carteira para `status = true`

> 🔒 **A carteira não pode ser inativada ou reativada por ações locais no FinancialService.**

### 🔧 Requisitos Funcionais

* **RF001**: Criar uma nova carteira vinculada a um usuário único (`user_id`, `user_email`) com `status = true`.
* **RF002**: Inativar ou reativar a carteira automaticamente com base em mensagens recebidas das filas de eventos do AuthService.
* **RF003**: Impedir qualquer operação financeira se a carteira estiver inativa (`status = false`).
* **RF004**: Garantir unicidade da carteira por usuário (`user_id`, `user_email`).

### 🔍 Consultas principais

As consultas abaixo representam as informações essenciais para operação e apresentação no dashboard:

* **Q001: Obter dados da carteira por `user_id`**

  * Retorna: status, nome, e-mail e ID da carteira.

* **Q002: Obter saldos consolidados da carteira no mês atual**

  * Retorna:

    * Saldo salário (`saldo_mes_salario`)
    * Saldo vale-alimentação (`saldo_mes_vale_alimentacao`)
    * Total de entradas
    * Total de despesas
    * Saldo final consolidado

* **Q003: Obter histórico dos últimos meses**

  * Retorna: lista dos últimos meses com:

    * Mês/ano
    * Saldo final
    * Total investido
    * Total emprestado
    * Comparativo com mês anterior

> 🔁 Todas as consultas operam com base na associação da carteira → controle de mês (`month_controls`) → entradas e despesas.

### 🔗 Relações

* 1:1 com `users` (via dados recebidos do AuthService)
* 1\:N com `month_controls`
* Através dos controles mensais, acessa `entries`, `expenses`, `investments`, `loans` e saldos mensais.

---
