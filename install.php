<?php
// Simple installation script for Mark Finanças
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Finanças - Instalação</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-2xl mx-auto p-6">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Mark Finanças</h1>
            <p class="text-gray-600">Instalação e Configuração</p>
        </div>

        <?php
        $errors = [];
        $success = [];
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $errors[] = "PHP 7.4 ou superior é necessário. Versão atual: " . PHP_VERSION;
        } else {
            $success[] = "PHP " . PHP_VERSION . " está OK";
        }
        
        // Check if JSON extension is loaded
        if (!extension_loaded('json')) {
            $errors[] = "Extensão JSON do PHP não está carregada";
        } else {
            $success[] = "Extensão JSON está OK";
        }
        
        // Check data directory
        $dataDir = __DIR__ . '/data';
        if (!is_dir($dataDir)) {
            if (mkdir($dataDir, 0755, true)) {
                $success[] = "Pasta 'data' criada com sucesso";
            } else {
                $errors[] = "Não foi possível criar a pasta 'data'";
            }
        } else {
            $success[] = "Pasta 'data' já existe";
        }
        
        // Check write permissions
        if (!is_writable($dataDir)) {
            $errors[] = "Pasta 'data' não tem permissões de escrita";
        } else {
            $success[] = "Permissões de escrita OK";
        }
        
        // Check backup directory
        $backupDir = $dataDir . '/backups';
        if (!is_dir($backupDir)) {
            if (mkdir($backupDir, 0755, true)) {
                $success[] = "Pasta 'backups' criada com sucesso";
            } else {
                $errors[] = "Não foi possível criar a pasta 'backups'";
            }
        } else {
            $success[] = "Pasta 'backups' já existe";
        }
        
        // Create initial transactions file
        $transactionsFile = $dataDir . '/transactions.json';
        if (!file_exists($transactionsFile)) {
            if (file_put_contents($transactionsFile, json_encode([]))) {
                $success[] = "Arquivo de transações criado";
            } else {
                $errors[] = "Não foi possível criar o arquivo de transações";
            }
        } else {
            $success[] = "Arquivo de transações já existe";
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $config = [
                'app_name' => $_POST['app_name'] ?? 'Mark Finanças',
                'timezone' => $_POST['timezone'] ?? 'America/Sao_Paulo',
                'currency' => $_POST['currency'] ?? 'BRL',
                'currency_symbol' => $_POST['currency_symbol'] ?? 'R$',
                'debug_mode' => isset($_POST['debug_mode'])
            ];
            
            $configContent = "<?php\n";
            $configContent .= "// Configuração gerada automaticamente\n";
            $configContent .= "define('APP_NAME', '" . addslashes($config['app_name']) . "');\n";
            $configContent .= "define('APP_TIMEZONE', '" . addslashes($config['timezone']) . "');\n";
            $configContent .= "define('CURRENCY_CODE', '" . addslashes($config['currency']) . "');\n";
            $configContent .= "define('CURRENCY_SYMBOL', '" . addslashes($config['currency_symbol']) . "');\n";
            $configContent .= "define('DEBUG_MODE', " . ($config['debug_mode'] ? 'true' : 'false') . ");\n";
            $configContent .= "date_default_timezone_set(APP_TIMEZONE);\n";
            $configContent .= "?>";
            
            if (file_put_contents('user_config.php', $configContent)) {
                $success[] = "Configuração salva com sucesso!";
            } else {
                $errors[] = "Erro ao salvar configuração";
            }
        }
        ?>

        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center mb-4">
                    <i data-lucide="check-circle" class="w-6 h-6 text-green-600 mr-2"></i>
                    <h2 class="text-lg font-semibold text-green-800">Verificações OK</h2>
                </div>
                <ul class="space-y-2">
                    <?php foreach ($success as $item): ?>
                        <li class="flex items-center text-green-600">
                            <i data-lucide="check" class="w-4 h-4 mr-2"></i>
                            <?= htmlspecialchars($item) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center mb-4">
                    <i data-lucide="alert-circle" class="w-6 h-6 text-red-600 mr-2"></i>
                    <h2 class="text-lg font-semibold text-red-800">Problemas Encontrados</h2>
                </div>
                <?php if (empty($errors)): ?>
                    <p class="text-gray-600">Nenhum problema encontrado!</p>
                <?php else: ?>
                    <ul class="space-y-2">
                        <?php foreach ($errors as $error): ?>
                            <li class="flex items-center text-red-600">
                                <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Configuration Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Configuração</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Aplicação</label>
                    <input type="text" name="app_name" value="Mark Finanças" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                    <select name="timezone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="America/Sao_Paulo">America/Sao_Paulo</option>
                        <option value="America/New_York">America/New_York</option>
                        <option value="Europe/London">Europe/London</option>
                        <option value="Asia/Tokyo">Asia/Tokyo</option>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Código da Moeda</label>
                        <input type="text" name="currency" value="BRL" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Símbolo da Moeda</label>
                        <input type="text" name="currency_symbol" value="R$" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="debug_mode" id="debug_mode" class="mr-2">
                    <label for="debug_mode" class="text-sm text-gray-700">Ativar modo debug (apenas para desenvolvimento)</label>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                    Salvar Configuração
                </button>
            </form>
        </div>

        <!-- Next Steps -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Próximos Passos</h2>
            <div class="space-y-3">
                <div class="flex items-center">
                    <i data-lucide="file-text" class="w-5 h-5 text-blue-600 mr-2"></i>
                    <span>Acesse <strong>index.html</strong> para usar a aplicação</span>
                </div>
                <div class="flex items-center">
                    <i data-lucide="book" class="w-5 h-5 text-blue-600 mr-2"></i>
                    <span>Leia o <strong>README.md</strong> para documentação completa</span>
                </div>
                <div class="flex items-center">
                    <i data-lucide="play" class="w-5 h-5 text-blue-600 mr-2"></i>
                    <span>Veja a <strong>demo.html</strong> para exemplos</span>
                </div>
                <div class="flex items-center">
                    <i data-lucide="settings" class="w-5 h-5 text-blue-600 mr-2"></i>
                    <span>Edite <strong>config.php</strong> para personalizações avançadas</span>
                </div>
            </div>
        </div>

        <div class="text-center mt-8">
            <a href="index.html" class="bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700 transition-colors inline-flex items-center">
                <i data-lucide="arrow-right" class="w-4 h-4 mr-2"></i>
                Ir para a Aplicação
            </a>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>