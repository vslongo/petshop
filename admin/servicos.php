<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

$page_title = 'Gerenciar Serviços';

$conn = getConnection();

// Processar ações
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        if ($_POST['acao'] === 'cadastrar' || $_POST['acao'] === 'editar') {
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $preco = floatval(str_replace(',', '.', $_POST['preco'] ?? 0));
            $duracao = intval($_POST['duracao'] ?? 30);
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            if ($_POST['acao'] === 'cadastrar') {
                $query = "INSERT INTO servicos (nome, descricao, preco, duracao, ativo) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssdii", $nome, $descricao, $preco, $duracao, $ativo);
            } else {
                $id = intval($_POST['id']);
                $query = "UPDATE servicos SET nome=?, descricao=?, preco=?, duracao=?, ativo=? WHERE id=?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssdiii", $nome, $descricao, $preco, $duracao, $ativo, $id);
            }
            
            if ($stmt->execute()) {
                $mensagem = 'Serviço ' . ($_POST['acao'] === 'cadastrar' ? 'cadastrado' : 'atualizado') . ' com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Erro ao salvar serviço.';
                $tipo_mensagem = 'danger';
            }
            $stmt->close();
        }
    }
}

// Excluir serviço
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $query = "DELETE FROM servicos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $mensagem = 'Serviço excluído com sucesso!';
        $tipo_mensagem = 'success';
    }
    $stmt->close();
}

// Buscar serviço para edição
$servico_edicao = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $query = "SELECT * FROM servicos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $servico_edicao = $result->fetch_assoc();
    $stmt->close();
}

// Listar serviços
$query = "SELECT * FROM servicos ORDER BY nome";
$servicos = $conn->query($query);
?>

<h1 class="h2 mb-4">Gerenciar Serviços</h1>

<?php if ($mensagem): ?>
    <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($mensagem); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $servico_edicao ? 'Editar' : 'Cadastrar'; ?> Serviço</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="acao" value="<?php echo $servico_edicao ? 'editar' : 'cadastrar'; ?>">
                    <?php if ($servico_edicao): ?>
                        <input type="hidden" name="id" value="<?php echo $servico_edicao['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" class="form-control" name="nome" 
                               value="<?php echo htmlspecialchars($servico_edicao['nome'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="descricao" rows="3"><?php echo htmlspecialchars($servico_edicao['descricao'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preço *</label>
                            <input type="text" class="form-control" name="preco" 
                                   placeholder="0.00" value="<?php echo number_format($servico_edicao['preco'] ?? 0, 2, ',', '.'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Duração (min) *</label>
                            <input type="number" class="form-control" name="duracao" 
                                   value="<?php echo $servico_edicao['duracao'] ?? 30; ?>" min="15" step="15" required>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="ativo" 
                               <?php echo (!$servico_edicao || $servico_edicao['ativo']) ? 'checked' : ''; ?>>
                        <label class="form-check-label">Ativo</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> <?php echo $servico_edicao ? 'Atualizar' : 'Cadastrar'; ?>
                    </button>
                    
                    <?php if ($servico_edicao): ?>
                        <a href="servicos.php" class="btn btn-secondary w-100 mt-2">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lista de Serviços</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Preço</th>
                                <th>Duração</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($servicos && $servicos->num_rows > 0): ?>
                                <?php while ($servico = $servicos->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $servico['id']; ?></td>
                                        <td><?php echo htmlspecialchars($servico['nome']); ?></td>
                                        <td>R$ <?php echo number_format($servico['preco'], 2, ',', '.'); ?></td>
                                        <td><?php echo $servico['duracao']; ?> min</td>
                                        <td>
                                            <span class="badge bg-<?php echo $servico['ativo'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $servico['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?editar=<?php echo $servico['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="?excluir=<?php echo $servico['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirmarExclusao('<?php echo htmlspecialchars($servico['nome']); ?>')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Nenhum serviço cadastrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>

