# Mark Finanças - Versão Web

Dashboard financeiro leve e moderno que funciona em qualquer servidor web.

## ✨ Principais Características

- **Ultra leve**: Apenas 3 arquivos principais
- **Moderno**: Design clean e responsivo
- **Simples**: localStorage para armazenar dados
- **Rápido**: Carrega em menos de 1 segundo
- **Compatível**: Funciona em qualquer navegador

## 🚀 Instalação Rápida

1. Baixe os arquivos da pasta `web-version`
2. Coloque no seu servidor web
3. Acesse `index.html` 
4. Pronto! Já está funcionando

## 📁 Estrutura dos Arquivos

```
web-version/
├── index.html              # Página principal
├── css/
│   └── style.css           # Estilos adicionais
├── js/
│   └── app.js              # Lógica da aplicação
├── api/
│   └── transactions.php    # API PHP (opcional)
├── data/                   # Pasta para armazenar dados
│   └── transactions.json   # Dados das transações
├── config.php              # Configurações do PHP
└── README.md              # Este arquivo
```

## 🎯 Funcionalidades

### Dashboard Principal
- Resumo financeiro (saldo, receitas, despesas)
- Formulário para adicionar transações
- Gráfico de despesas por categoria
- Lista de transações recentes

### Página de Transações
- Visualização de todas as transações
- Filtros e pesquisa
- Adicionar/editar/excluir transações

### Página de Relatórios
- Gráfico de receitas vs despesas mensais
- Resumo por categoria
- Análise de tendências

## 💾 Armazenamento de Dados

### localStorage (Padrão)
- Dados salvos localmente no navegador
- Não requer servidor PHP
- Dados perdidos se limpar o navegador

### Arquivo JSON (Com PHP)
- Dados salvos no servidor
- Backup automático
- Dados persistentes entre dispositivos

## 🔧 Configuração

### Configurações Básicas
Edite o arquivo `config.php` para personalizar:

```php
// Nome da aplicação
define('APP_NAME', 'Mark Finanças');

// Timezone
define('APP_TIMEZONE', 'America/Sao_Paulo');

// Configurações de moeda
define('CURRENCY_CODE', 'BRL');
define('CURRENCY_SYMBOL', 'R$');
```

### Categorias Personalizadas
Modifique as categorias em `config.php`:

```php
define('DEFAULT_CATEGORIES', [
    'nova_categoria' => [
        'label' => 'Nova Categoria',
        'color' => '#FF5733',
        'icon' => 'tag'
    ]
]);
```

## 📱 Recursos Mobile

- Interface responsiva
- Botão flutuante para adicionar transações
- Navegação otimizada para toque
- Gráficos adaptativos

## 🔒 Segurança

### Recomendações para Produção

1. **Altere as configurações de CORS** em `config.php`:
   ```php
   define('ALLOWED_ORIGINS', ['https://seudominio.com']);
   ```

2. **Desative o modo debug**:
   ```php
   define('DEBUG_MODE', false);
   ```

3. **Configure permissões de arquivo**:
   ```bash
   chmod 755 web-version/
   chmod 644 web-version/*.php
   chmod 755 web-version/data/
   ```

## 🔄 Backup e Restauração

### Backup Automático
- Backups automáticos a cada 24 horas
- Máximo de 30 arquivos de backup
- Arquivos salvos em `data/backups/`

### Backup Manual
```php
// Criar backup manualmente
include 'config.php';
createBackup();
```

## 🎨 Personalização

### Cores e Temas
Modifique o arquivo `css/style.css` para alterar cores:

```css
:root {
    --primary-color: #3b82f6;
    --success-color: #10b981;
    --danger-color: #ef4444;
}
```

### Ícones
Todos os ícones usam Lucide Icons. Para alterar:

```html
<i data-lucide="nome-do-icone"></i>
```

## 🐛 Solução de Problemas

### Problema: Dados não são salvos
**Solução**: Verifique se o PHP tem permissões de escrita na pasta `data/`

### Problema: Gráficos não carregam
**Solução**: Certifique-se de que o Chart.js está carregando corretamente

### Problema: Erro de CORS
**Solução**: Configure corretamente as origens permitidas em `config.php`

## 📊 API Endpoints (PHP)

### GET /api/transactions.php
Retorna todas as transações

### POST /api/transactions.php
Cria uma nova transação

### PUT /api/transactions.php?id=123
Atualiza uma transação existente

### DELETE /api/transactions.php?id=123
Remove uma transação

## 🔧 Requisitos do Sistema

### Mínimos
- Navegador moderno com suporte a ES6
- JavaScript habilitado

### Para versão PHP
- PHP 7.4 ou superior
- Extensão JSON habilitada
- Permissões de escrita no diretório

## 📈 Melhorias Futuras

- [ ] Autenticação de usuários
- [ ] Múltiplas moedas
- [ ] Exportação para Excel
- [ ] Notificações push
- [ ] Modo offline
- [ ] Integração com bancos

## 🤝 Contribuindo

1. Faça um fork do projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Envie um pull request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

## 📞 Suporte

Para suporte, entre em contato através do email: suporte@markfinancas.com

---

**Mark Finanças** - Gerencie suas finanças com simplicidade e eficiência!