<?php
require_once 'config/database.php';
$page_title = 'Serviços';
include 'includes/header.php';

$conn = getConnection();

$busca = isset($_GET['busca']) ? $_GET['busca'] : '';

if ($busca) {
    $query = "SELECT * FROM servicos WHERE ativo = 1 AND (nome LIKE ? OR descricao LIKE ?) ORDER BY nome";
    $busca_param = "%$busca%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $busca_param, $busca_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query = "SELECT * FROM servicos WHERE ativo = 1 ORDER BY nome";
    $result = $conn->query($query);
}

// Função para buscar preços por peso
function getPrecosPorPeso($conn, $servico_id) {
    // Verificar se a tabela existe
    $check_table = $conn->query("SHOW TABLES LIKE 'servico_precos_peso'");
    if ($check_table->num_rows == 0) {
        return [];
    }
    
    $query = "SELECT tipo_animal, peso_min, peso_max, preco FROM servico_precos_peso WHERE servico_id = ? ORDER BY tipo_animal, peso_min";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param("i", $servico_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $precos = [];
    while ($row = $result->fetch_assoc()) {
        $precos[] = $row;
    }
    $stmt->close();
    return $precos;
}

// Função para verificar se campo existe na tabela
function campoExiste($conn, $tabela, $campo) {
    $query = "SHOW COLUMNS FROM $tabela LIKE '$campo'";
    $result = $conn->query($query);
    return $result && $result->num_rows > 0;
}
?>

<div class="page-header">
    <div class="container">
        <h1>Nossos Serviços</h1>
        <p class="lead">Cuidados completos para o seu pet com profissionais qualificados</p>
    </div>
</div>

<div class="container py-5">
    <?php
    // Verificar se banco está atualizado e mostrar aviso se necessário
    $check_table = $conn->query("SHOW TABLES LIKE 'servico_precos_peso'");
    $check_taxi = $conn->query("SHOW COLUMNS FROM servicos LIKE 'taxi_pet_disponivel'");
    $banco_atualizado = ($check_table->num_rows > 0 && $check_taxi->num_rows > 0);
    
    if (!$banco_atualizado):
    ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Banco de Dados Precisa ser Atualizado</h5>
        <p>Para exibir preços por peso e informações sobre Taxi Pet, execute o script de atualização do banco de dados.</p>
        <hr>
        <p class="mb-0">
            <a href="verificar_banco.php" class="btn btn-sm btn-warning">
                <i class="bi bi-database-check"></i> Verificar Status do Banco
            </a>
            <small class="d-block mt-2">Execute o arquivo <code>sql/database_updates.sql</code> no seu banco de dados MySQL.</small>
        </p>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Busca -->
    <div class="row mb-4">
        <div class="col-md-6">
            <form method="GET" class="d-flex">
                <input type="text" name="busca" class="form-control me-2" 
                       placeholder="Buscar serviço..." value="<?php echo htmlspecialchars($busca); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <?php if ($busca): ?>
                    <a href="servicos.php" class="btn btn-outline-secondary ms-2">Limpar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Lista de Serviços -->
    <div class="row g-4">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($servico = $result->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card service-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-heart-pulse text-primary"></i> 
                                <?php echo htmlspecialchars($servico['nome']); ?>
                            </h5>
                            <p class="card-text"><?php echo htmlspecialchars($servico['descricao'] ?? 'Sem descrição disponível.'); ?></p>
                            <hr>
                            
                            <?php 
                            $precos_peso = getPrecosPorPeso($conn, $servico['id']);
                            $tem_precos_peso = !empty($precos_peso);
                            
                            // Verificar se campos de taxi pet existem
                            $tem_taxi_pet = campoExiste($conn, 'servicos', 'taxi_pet_disponivel');
                            $taxi_disponivel = false;
                            $taxa_base = 0;
                            $taxa_km = 0;
                            
                            if ($tem_taxi_pet) {
                                $taxi_disponivel = isset($servico['taxi_pet_disponivel']) && $servico['taxi_pet_disponivel'] == 1;
                                $taxa_base = $servico['taxa_taxi_base'] ?? 0;
                                $taxa_km = $servico['taxa_taxi_por_km'] ?? 0;
                            }
                            ?>
                            
                            <div class="mb-3">
                                <?php if ($tem_precos_peso): ?>
                                    <div class="alert alert-info p-2 mb-2">
                                        <small><i class="bi bi-info-circle"></i> <strong>Preço varia conforme o peso do animal</strong></small>
                                    </div>
                                    <div class="small text-start">
                                        <?php 
                                        $precos_cao = array_filter($precos_peso, function($p) { return $p['tipo_animal'] === 'cao'; });
                                        $precos_gato = array_filter($precos_peso, function($p) { return $p['tipo_animal'] === 'gato'; });
                                        ?>
                                        <?php if (!empty($precos_cao)): ?>
                                            <strong><i class="bi bi-heart-fill text-primary"></i> Cães:</strong><br>
                                            <?php foreach ($precos_cao as $preco): ?>
                                                <?php 
                                                $peso_min = number_format($preco['peso_min'], 1, ',', '.');
                                                $peso_max = $preco['peso_max'] >= 999 ? '+' : ' a ' . number_format($preco['peso_max'], 1, ',', '.');
                                                ?>
                                                &nbsp;&nbsp;• <?php echo $peso_min . $peso_max; ?>kg: <strong>R$ <?php echo number_format($preco['preco'], 2, ',', '.'); ?></strong><br>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <?php if (!empty($precos_gato)): ?>
                                            <strong class="mt-2 d-block"><i class="bi bi-heart-fill text-primary"></i> Gatos:</strong>
                                            <?php foreach ($precos_gato as $preco): ?>
                                                <?php 
                                                $peso_min = number_format($preco['peso_min'], 1, ',', '.');
                                                $peso_max = $preco['peso_max'] >= 999 ? '+' : ' a ' . number_format($preco['peso_max'], 1, ',', '.');
                                                ?>
                                                &nbsp;&nbsp;• <?php echo $peso_min . $peso_max; ?>kg: <strong>R$ <?php echo number_format($preco['preco'], 2, ',', '.'); ?></strong><br>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div>
                                        <span class="h5 text-primary mb-0">
                                            R$ <?php echo number_format($servico['preco'], 2, ',', '.'); ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i> Preço fixo
                                        </small>
                                    </div>
                                <?php endif; ?>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> Duração: <?php echo $servico['duracao']; ?> minutos
                                </small>
                            </div>
                            
                            <?php if ($tem_taxi_pet && $taxi_disponivel): ?>
                                <div class="alert alert-warning p-3 mb-3" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(255, 184, 77, 0.1) 100%); border-left: 4px solid var(--cor-taxi);">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-car-front-fill me-2" style="font-size: 1.5rem; color: var(--cor-taxi);"></i>
                                        <strong style="color: var(--cor-taxi);">Taxi Pet Disponível!</strong>
                                    </div>
                                    <small class="text-dark">
                                        <i class="bi bi-currency-dollar"></i> <strong>Taxa base:</strong> R$ <?php echo number_format($taxa_base, 2, ',', '.'); ?><br>
                                        <i class="bi bi-signpost-split"></i> <strong>Taxa por km:</strong> R$ <?php echo number_format($taxa_km, 2, ',', '.'); ?>/km<br>
                                        <em class="text-muted">Cálculo: Taxa base + (distância × taxa/km)</em>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <a href="agendamento.php?servico=<?php echo $servico['id']; ?>" class="btn btn-primary w-100">
                                    <i class="bi bi-calendar-check"></i> Agendar Este Serviço
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <?php if (isset($stmt)) $stmt->close(); ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    <?php echo $busca ? 'Nenhum serviço encontrado para "' . htmlspecialchars($busca) . '".' : 'Nenhum serviço cadastrado ainda.'; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="text-center mt-5">
        <a href="agendamento.php" class="btn btn-primary btn-lg">
            <i class="bi bi-calendar-check"></i> Fazer um Agendamento
        </a>
    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>

