// Mark Finanças - Lightweight JavaScript
class FinancialApp {
    constructor() {
        this.storageKey = 'mark-financas-data';
        this.transactions = this.loadTransactions();
        this.categories = {
            salario: { label: 'Salário', color: '#16a34a' },
            alimentacao: { label: 'Alimentação', color: '#dc2626' },
            transporte: { label: 'Transporte', color: '#2563eb' },
            saude: { label: 'Saúde', color: '#ca8a04' },
            lazer: { label: 'Lazer', color: '#7c3aed' },
            ayla: { label: 'Ayla', color: '#ea580c' },
            outros: { label: 'Outros', color: '#64748b' }
        };
        this.charts = {};
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.updateSummary();
        this.updateTransactionsList();
        this.updateCharts();
        this.setTodayDate();
    }

    setupEventListeners() {
        // Navigation
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.showPage(e.target.dataset.page));
        });

        // Forms
        document.getElementById('transaction-form').addEventListener('submit', (e) => this.handleSubmit(e));
        document.getElementById('modal-form').addEventListener('submit', (e) => this.handleSubmit(e));

        // Modal
        document.getElementById('modal').addEventListener('click', (e) => {
            if (e.target.id === 'modal') this.closeModal();
        });
    }

    showPage(pageId) {
        // Hide all pages
        document.querySelectorAll('.page').forEach(page => page.classList.remove('active'));
        document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));

        // Show selected page
        document.getElementById(pageId).classList.add('active');
        document.querySelector(`[data-page="${pageId}"]`).classList.add('active');

        // Update content
        if (pageId === 'transactions') this.updateAllTransactions();
        if (pageId === 'reports') this.updateReports();
    }

    handleSubmit(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const transaction = {
            id: Date.now().toString(),
            description: formData.get('description'),
            amount: parseFloat(formData.get('amount')),
            date: formData.get('date'),
            category: formData.get('category'),
            type: formData.get('type')
        };

        this.addTransaction(transaction);
        e.target.reset();
        this.setTodayDate();
        this.closeModal();
        this.showToast('Transação adicionada com sucesso!');
    }

    addTransaction(transaction) {
        this.transactions.push(transaction);
        this.saveTransactions();
        this.updateSummary();
        this.updateTransactionsList();
        this.updateCharts();
    }

    deleteTransaction(id) {
        if (confirm('Deseja remover esta transação?')) {
            this.transactions = this.transactions.filter(t => t.id !== id);
            this.saveTransactions();
            this.updateSummary();
            this.updateTransactionsList();
            this.updateCharts();
            this.showToast('Transação removida!');
        }
    }

    loadTransactions() {
        try {
            return JSON.parse(localStorage.getItem(this.storageKey)) || [];
        } catch {
            return [];
        }
    }

    saveTransactions() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.transactions));
    }

    updateSummary() {
        const income = this.transactions.filter(t => t.type === 'income').reduce((sum, t) => sum + t.amount, 0);
        const expense = this.transactions.filter(t => t.type === 'expense').reduce((sum, t) => sum + t.amount, 0);
        const balance = income - expense;

        document.getElementById('balance-amount').textContent = this.formatCurrency(balance);
        document.getElementById('income-amount').textContent = this.formatCurrency(income);
        document.getElementById('expense-amount').textContent = this.formatCurrency(expense);
    }

    updateTransactionsList() {
        const recent = this.transactions.slice(-5).reverse();
        const tbody = document.getElementById('transactions-tbody');
        
        tbody.innerHTML = recent.length ? recent.map(t => `
            <tr>
                <td>${this.formatDate(t.date)}</td>
                <td>${t.description}</td>
                <td>${this.categories[t.category].label}</td>
                <td class="amount-${t.type}">${this.formatCurrency(t.amount)}</td>
                <td><button class="btn-danger" onclick="app.deleteTransaction('${t.id}')">×</button></td>
            </tr>
        `).join('') : '<tr><td colspan="5" class="empty-state">Nenhuma transação encontrada</td></tr>';
    }

    updateAllTransactions() {
        const tbody = document.getElementById('all-transactions-tbody');
        const sorted = [...this.transactions].sort((a, b) => new Date(b.date) - new Date(a.date));
        
        tbody.innerHTML = sorted.length ? sorted.map(t => `
            <tr>
                <td>${this.formatDate(t.date)}</td>
                <td>${t.description}</td>
                <td>${this.categories[t.category].label}</td>
                <td>${t.type === 'income' ? 'Receita' : 'Despesa'}</td>
                <td class="amount-${t.type}">${this.formatCurrency(t.amount)}</td>
                <td><button class="btn-danger" onclick="app.deleteTransaction('${t.id}')">×</button></td>
            </tr>
        `).join('') : '<tr><td colspan="6" class="empty-state">Nenhuma transação encontrada</td></tr>';
    }

    updateCharts() {
        this.updateExpenseChart();
        this.updateMonthlyChart();
    }

    updateExpenseChart() {
        const ctx = document.getElementById('expense-chart').getContext('2d');
        const expenses = this.transactions.filter(t => t.type === 'expense');
        const byCategory = {};
        
        expenses.forEach(t => {
            byCategory[t.category] = (byCategory[t.category] || 0) + t.amount;
        });

        if (this.charts.expense) this.charts.expense.destroy();

        const data = Object.entries(byCategory);
        this.charts.expense = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(([key]) => this.categories[key].label),
                datasets: [{
                    data: data.map(([, value]) => value),
                    backgroundColor: data.map(([key]) => this.categories[key].color)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    updateMonthlyChart() {
        const ctx = document.getElementById('monthly-chart');
        if (!ctx) return;

        const monthly = {};
        this.transactions.forEach(t => {
            const month = t.date.substring(0, 7);
            if (!monthly[month]) monthly[month] = { income: 0, expense: 0 };
            monthly[month][t.type] += t.amount;
        });

        if (this.charts.monthly) this.charts.monthly.destroy();

        const months = Object.keys(monthly).sort();
        this.charts.monthly = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months.map(m => {
                    const [year, month] = m.split('-');
                    return `${month}/${year}`;
                }),
                datasets: [
                    {
                        label: 'Receitas',
                        data: months.map(m => monthly[m].income),
                        backgroundColor: '#16a34a'
                    },
                    {
                        label: 'Despesas',
                        data: months.map(m => monthly[m].expense),
                        backgroundColor: '#dc2626'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    updateReports() {
        this.updateMonthlyChart();
        this.updateCategorySummary();
    }

    updateCategorySummary() {
        const container = document.getElementById('category-summary');
        const expenses = this.transactions.filter(t => t.type === 'expense');
        const byCategory = {};
        
        expenses.forEach(t => {
            byCategory[t.category] = (byCategory[t.category] || 0) + t.amount;
        });

        container.innerHTML = Object.entries(byCategory).length ? 
            Object.entries(byCategory).map(([key, value]) => `
                <div class="category-item">
                    <div class="category-label">
                        <div class="category-color" style="background: ${this.categories[key].color}"></div>
                        <span>${this.categories[key].label}</span>
                    </div>
                    <span class="category-amount">${this.formatCurrency(value)}</span>
                </div>
            `).join('') : '<div class="empty-state">Nenhuma despesa encontrada</div>';
    }

    openModal() {
        document.getElementById('modal').classList.add('active');
    }

    closeModal() {
        document.getElementById('modal').classList.remove('active');
    }

    showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = `toast ${type} show`;
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    setTodayDate() {
        const today = new Date().toISOString().split('T')[0];
        document.querySelectorAll('input[type="date"]').forEach(input => {
            if (!input.value) input.value = today;
        });
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(amount);
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('pt-BR');
    }
}

// Global functions
function openModal() {
    app.openModal();
}

function closeModal() {
    app.closeModal();
}

// Initialize app
let app;
document.addEventListener('DOMContentLoaded', () => {
    app = new FinancialApp();
});