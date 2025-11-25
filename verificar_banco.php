<?php
// Script para verificar se o banco de dados está atualizado
require_once 'config/database.php';

$conn = getConnection();
$problemas = [];
$sucessos = [];

// Verificar se tabela servico_precos_peso existe
$check = $conn->query("SHOW TABLES LIKE 'servico_precos_peso'");
if ($check->num_rows == 0) {
    $problemas[] = "Tabela 'servico_precos_peso' não existe. Execute sql/database_updates.sql";
} else {
    $sucessos[] = "Tabela 'servico_precos_peso' existe";
}

// Verificar campos de taxi pet
$campos_taxi = ['taxi_pet_disponivel', 'taxa_taxi_base', 'taxa_taxi_por_km'];
foreach ($campos_taxi as $campo) {
    $check = $conn->query("SHOW COLUMNS FROM servicos LIKE '$campo'");
    if ($check->num_rows == 0) {
        $problemas[] = "Campo '$campo' não existe na tabela 'servicos'. Execute sql/database_updates.sql";
    } else {
        $sucessos[] = "Campo '$campo' existe";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação do Banco de Dados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="bi bi-database-check"></i> Verificação do Banco de Dados</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($problemas)): ?>
                            <div class="alert alert-success">
                                <h5><i class="bi bi-check-circle-fill"></i> Tudo OK!</h5>
                                <p>O banco de dados está atualizado e todas as funcionalidades estão disponíveis.</p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <h5><i class="bi bi-exclamation-triangle-fill"></i> Atenção!</h5>
                                <p>O banco de dados precisa ser atualizado para exibir preços por peso e taxi pet.</p>
                                <ul class="mb-0">
                                    <?php foreach ($problemas as $problema): ?>
                                        <li><?php echo htmlspecialchars($problema); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle"></i> Como resolver:</h6>
                                <ol>
                                    <li>Abra o phpMyAdmin ou seu cliente MySQL</li>
                                    <li>Selecione o banco de dados <code>petshop_db</code></li>
                                    <li>Execute o arquivo <code>sql/database_updates.sql</code></li>
                                    <li>Recarregue esta página para verificar</li>
                                </ol>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($sucessos)): ?>
                            <div class="alert alert-success">
                                <h6><i class="bi bi-check-circle"></i> Funcionalidades disponíveis:</h6>
                                <ul class="mb-0">
                                    <?php foreach ($sucessos as $sucesso): ?>
                                        <li><?php echo htmlspecialchars($sucesso); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <a href="servicos.php" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i> Voltar para Serviços
                            </a>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-house"></i> Página Inicial
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>




