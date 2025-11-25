<?php
require_once 'config/database.php';
$page_title = 'Home';
include 'includes/header.php';

$conn = getConnection();

// Buscar produtos em destaque (últimos 4)
$query_produtos = "SELECT * FROM produtos WHERE ativo = 1 ORDER BY created_at DESC LIMIT 4";
$result_produtos = $conn->query($query_produtos);

// Buscar serviços ativos
$query_servicos = "SELECT * FROM servicos WHERE ativo = 1 ORDER BY nome LIMIT 6";
$result_servicos = $conn->query($query_servicos);
?>

<!-- Hero Section com Carrossel -->
<section class="hero-section">
    <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
            <div class="carousel-item active" style="background-image: url('assets/images/banner/pet1.jpg');">
            </div>
        </div>
        <div class="hero-content">
            <h1>Bem-vindo ao Cão e Gato Princes</h1>
            <p class="lead">Cuidando do seu melhor amigo com amor e dedicação</p>
            <div class="hero-buttons">
                <a href="agendamento.php" class="btn btn-light btn-lg">
                    <i class="bi bi-calendar-check"></i> Agendar Serviço
                </a>
                <a href="produtos.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-bag"></i> Ver Produtos
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Produtos em Destaque -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Produtos em Destaque</h2>
        <div class="row g-4">
            <?php if ($result_produtos && $result_produtos->num_rows > 0): ?>
                <?php while ($produto = $result_produtos->fetch_assoc()): ?>
                    <div class="col-md-3">
                        <div class="card product-card h-100">
                            <img src="assets/images/produtos/<?php echo htmlspecialchars($produto['imagem']); ?>" 
                                 class="card-img-top product-img" 
                                 alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                                 onerror="this.src='assets/images/produtos/default.jpg'">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars($produto['descricao_curta'] ?? ''); ?></p>
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
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">Nenhum produto cadastrado ainda.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="produtos.php" class="btn btn-outline-primary">Ver Todos os Produtos</a>
        </div>
    </div>
</section>

<!-- Serviços -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Nossos Serviços</h2>
        <div class="row g-4">
            <?php if ($result_servicos && $result_servicos->num_rows > 0): ?>
                <?php while ($servico = $result_servicos->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card service-card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-heart-pulse text-primary"></i> 
                                    <?php echo htmlspecialchars($servico['nome']); ?>
                                </h5>
                                <p class="card-text"><?php echo htmlspecialchars($servico['descricao'] ?? ''); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h6 text-primary mb-0">
                                        R$ <?php echo number_format($servico['preco'], 2, ',', '.'); ?>
                                    </span>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> <?php echo $servico['duracao']; ?> min
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">Nenhum serviço cadastrado ainda.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="servicos.php" class="btn btn-primary">Ver Todos os Serviços</a>
            <a href="agendamento.php" class="btn btn-outline-primary ms-2">Agendar Agora</a>
        </div>
    </div>
</section>

<!-- Sobre Nós -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2>Sobre o Cão e Gato Princes</h2>
                <p>Somos uma equipe apaixonada por animais, dedicada a oferecer os melhores produtos e serviços para o cuidado do seu pet. Com mais de 10 anos de experiência, nossa missão é proporcionar bem-estar e saúde aos animais de estimação.</p>
                <ul class="list-unstyled">
                    <li><i class="bi bi-check-circle text-success"></i> Profissionais qualificados</li>
                    <li><i class="bi bi-check-circle text-success"></i> Produtos de alta qualidade</li>
                    <li><i class="bi bi-check-circle text-success"></i> Ambiente acolhedor e seguro</li>
                    <li><i class="bi bi-check-circle text-success"></i> Atendimento personalizado</li>
                </ul>
            </div>
            <div class="col-md-6 text-center">
                <i class="bi bi-heart-pulse-fill" style="font-size: 150px; color: #667eea;"></i>
            </div>
        </div>
    </div>
</section>

<?php
$conn->close();
include 'includes/footer.php';
?>

