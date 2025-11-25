<?php
require_once 'config/database.php';
$page_title = 'Produtos';
include 'includes/header.php';

$conn = getConnection();

// Ver detalhes de um produto específico
$produto_id = isset($_GET['ver']) ? (int)$_GET['ver'] : 0;
$busca = isset($_GET['busca']) ? $_GET['busca'] : '';

if ($produto_id > 0) {
    // Exibir detalhes do produto
    $query = "SELECT * FROM produtos WHERE id = ? AND ativo = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $produto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $produto = $result->fetch_assoc();
    $stmt->close();
    
    if ($produto): ?>
        <div class="page-header">
            <div class="container">
                <h1>Detalhes do Produto</h1>
            </div>
        </div>
        
        <div class="container py-5">
            <a href="produtos.php" class="btn btn-outline-primary mb-4">
                <i class="bi bi-arrow-left"></i> Voltar para Produtos
            </a>
            
            <div class="row">
                <div class="col-md-6">
                    <img src="assets/images/produtos/<?php echo htmlspecialchars($produto['imagem']); ?>" 
                         class="img-fluid rounded" 
                         alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                         onerror="this.src='assets/images/produtos/default.jpg'">
                </div>
                <div class="col-md-6">
                    <h2><?php echo htmlspecialchars($produto['nome']); ?></h2>
                    <?php if ($produto['categoria']): ?>
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($produto['categoria']); ?></span>
                    <?php endif; ?>
                    <hr>
                    <p class="h3 text-primary mb-4">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                    <p class="lead"><?php echo nl2br(htmlspecialchars($produto['descricao'] ?? $produto['descricao_curta'] ?? '')); ?></p>
                    
                    <?php if ($produto['estoque'] > 0): ?>
                        <p class="text-success">
                            <i class="bi bi-check-circle"></i> Em estoque (<?php echo $produto['estoque']; ?> unidades)
                        </p>
                    <?php else: ?>
                        <p class="text-danger">
                            <i class="bi bi-x-circle"></i> Fora de estoque
                        </p>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <button class="btn btn-primary btn-lg" onclick="alert('Entre em contato para comprar este produto!')">
                            <i class="bi bi-cart-plus"></i> Comprar Agora
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="container py-5">
            <div class="alert alert-warning">
                <h4>Produto não encontrado</h4>
                <p>O produto que você está procurando não existe ou foi removido.</p>
                <a href="produtos.php" class="btn btn-primary">Ver Todos os Produtos</a>
            </div>
        </div>
    <?php endif;
} else {
    // Listar todos os produtos
    if ($busca) {
        $query = "SELECT * FROM produtos WHERE ativo = 1 AND (nome LIKE ? OR descricao LIKE ? OR descricao_curta LIKE ?) ORDER BY nome";
        $busca_param = "%$busca%";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $busca_param, $busca_param, $busca_param);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $query = "SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome";
        $result = $conn->query($query);
    }
    ?>
    
    <div class="page-header">
        <div class="container">
            <h1>Nossos Produtos</h1>
        </div>
    </div>
    
    <div class="container py-5">
        <!-- Busca -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="text" name="busca" class="form-control me-2" 
                           placeholder="Buscar produto..." value="<?php echo htmlspecialchars($busca); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <?php if ($busca): ?>
                        <a href="produtos.php" class="btn btn-outline-secondary ms-2">Limpar</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Lista de Produtos -->
        <div class="row g-4">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($produto = $result->fetch_assoc()): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="card product-card h-100">
                            <img src="assets/images/produtos/<?php echo htmlspecialchars($produto['imagem']); ?>" 
                                 class="card-img-top product-img" 
                                 alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                                 onerror="this.src='assets/images/produtos/default.jpg'">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars(mb_substr($produto['descricao_curta'] ?? $produto['descricao'] ?? '', 0, 80)) . '...'; ?></p>
                                <div class="mt-auto">
                                    <p class="h5 text-primary mb-3">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                    <a href="produtos.php?ver=<?php echo $produto['id']; ?>" class="btn btn-primary w-100">
                                        Ver Detalhes
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
                        <?php echo $busca ? 'Nenhum produto encontrado para "' . htmlspecialchars($busca) . '".' : 'Nenhum produto cadastrado ainda.'; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php }

$conn->close();
include 'includes/footer.php';
?>

