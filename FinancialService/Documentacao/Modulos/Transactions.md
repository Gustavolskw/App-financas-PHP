
## ✅ Módulo: Entradas e Saídas (Transações)

### 🎯 Finalidade

Registrar e controlar todo o fluxo financeiro da carteira em um determinado mês, separando claramente:

* **Entradas**: receitas (ex: salário, vale, saldo anterior, devoluções, resgates)
* **Despesas**: saídas (ex: mercado, transporte, investimentos, empréstimos)

> Este módulo representa o **movimento financeiro mensal da carteira** e é a base para os saldos e análises.

---

### 🔧 Requisitos Funcionais

#### 📥 Entradas (`entries`)

* **RF101**: Permitir criar uma nova entrada financeira para um mês ativo da carteira.
* **RF102**: Exigir tipo de entrada (ex: salário, vale, resgate de investimento, etc.).
* **RF103**: Associar cada entrada a um controle mensal (`month_control_id`).
* **RF104**: Calcular corretamente o saldo do mês conforme o tipo:

    * Se tipo for “Vale”, somar ao saldo vale
    * Se tipo for “Salário” ou “Saldo anterior”, somar ao saldo salário
    * Se tipo for resgate ou devolução, somar ao salário

#### 📤 Despesas (`expenses`)

* **RF105**: Permitir registrar uma nova despesa no mês atual da carteira.
* **RF106**: Exigir categoria (ex: mercado, transporte, investimento, etc.) e forma de pagamento (ex: dinheiro, vale, cartão).
* **RF107**: Descontar automaticamente o valor do saldo mensal correto:

    * Se forma de pagamento for “vale”, subtrair do saldo vale
    * Se for “dinheiro” ou outra, subtrair do saldo salário
* **RF108**: Se a categoria da despesa for “Investimento”, criar um registro no módulo de investimentos.
* **RF109**: Se a categoria da despesa for “Empréstimo”, criar um registro no módulo de empréstimos.

---

### 🔁 Fluxo de Uso

1. **Adicionar Entrada**

    * Usuário escolhe mês atual
    * Informa valor, tipo, data e descrição
    * Saldo é atualizado

2. **Adicionar Despesa**

    * Usuário escolhe mês atual
    * Informa valor, categoria, forma de pagamento, local e data
    * Saldo é subtraído
    * Se for investimento/empréstimo, cria registro vinculado

3. **Visualização do Mês**

    * Lista de todas as entradas e despesas agrupadas
    * Cálculo do saldo atual por tipo
    * Destaque para despesas recorrentes ou relevantes

---

### 🔍 Consultas principais

* **Q101: Obter todas as transações do mês (entradas e saídas)**

    * Filtro por `month_control_id`
    * Agrupadas por tipo e data

* **Q102: Obter total de entradas e despesas do mês**

    * Soma por tipo (vale, salário)

* **Q103: Obter detalhes de uma transação individual**

    * Mostra campos completos: valor, data, descrição, tipo, local

* **Q104: Obter transações filtradas por categoria ou tipo**

    * Ex: todas as despesas de “Mercado”, todas as entradas do tipo “Salário”

---

### 🔗 Relações

| Tabela               | Relação                                         |
| -------------------- | ----------------------------------------------- |
| `month_controls`     | Cada entrada/saída pertence a um mês específico |
| `entry_types`        | Cada entrada tem um tipo                        |
| `expense_categories` | Cada despesa tem uma categoria                  |
| `payment_methods`    | Cada despesa tem uma forma de pagamento         |

---
