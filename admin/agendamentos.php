<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

$page_title = 'Gerenciar Agendamentos';

$conn = getConnection();

// Processar alteração de status
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    
    $query = "UPDATE agendamentos SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        $mensagem = 'Status do agendamento atualizado com sucesso!';
        $tipo_mensagem = 'success';
    } else {
        $mensagem = 'Erro ao atualizar status.';
        $tipo_mensagem = 'danger';
    }
    $stmt->close();
}

// Excluir agendamento
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $query = "DELETE FROM agendamentos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $mensagem = 'Agendamento excluído com sucesso!';
        $tipo_mensagem = 'success';
    }
    $stmt->close();
}

// Filtros
$filtro_data = $_GET['data'] ?? '';
$filtro_status = $_GET['status'] ?? '';

// Query com filtros
$query = "SELECT a.*, s.nome as servico_nome FROM agendamentos a 
          JOIN servicos s ON a.servico_id = s.id WHERE 1=1";
$params = [];
$types = '';

if ($filtro_data) {
    $query .= " AND a.data = ?";
    $params[] = $filtro_data;
    $types .= 's';
}

if ($filtro_status) {
    $query .= " AND a.status = ?";
    $params[] = $filtro_status;
    $types .= 's';
}

$query .= " ORDER BY a.data DESC, a.horario DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $agendamentos = $stmt->get_result();
} else {
    $agendamentos = $conn->query($query);
}
?>

<h1 class="h2 mb-4">Gerenciar Agendamentos</h1>

<?php if ($mensagem): ?>
    <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($mensagem); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Filtrar por Data</label>
                <input type="date" class="form-control" name="data" value="<?php echo htmlspecialchars($filtro_data); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Filtrar por Status</label>
                <select class="form-select" name="status">
                    <option value="">Todos</option>
                    <option value="pendente" <?php echo $filtro_status === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="confirmado" <?php echo $filtro_status === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                    <option value="cancelado" <?php echo $filtro_status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                    <option value="concluido" <?php echo $filtro_status === 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="agendamentos.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Agendamentos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Lista de Agendamentos</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Cliente</th>
                        <th>Telefone</th>
                        <th>Pet</th>
                        <th>Serviço</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($agendamentos && $agendamentos->num_rows > 0): ?>
                        <?php while ($agendamento = $agendamentos->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($agendamento['data'])); ?></td>
                                <td><?php echo date('H:i', strtotime($agendamento['horario'])); ?></td>
                                <td><?php echo htmlspecialchars($agendamento['cliente_nome']); ?></td>
                                <td><?php echo htmlspecialchars($agendamento['cliente_telefone']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($agendamento['pet_nome']); ?>
                                    <?php if ($agendamento['pet_tipo']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($agendamento['pet_tipo']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($agendamento['servico_nome']); ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id" value="<?php echo $agendamento['id']; ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="pendente" <?php echo $agendamento['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                            <option value="confirmado" <?php echo $agendamento['status'] === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                                            <option value="cancelado" <?php echo $agendamento['status'] === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                            <option value="concluido" <?php echo $agendamento['status'] === 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                                        </select>
                                        <input type="hidden" name="alterar_status" value="1">
                                    </form>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalDetalhes<?php echo $agendamento['id']; ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="?excluir=<?php echo $agendamento['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirmarExclusao('Agendamento de <?php echo htmlspecialchars($agendamento['cliente_nome']); ?>')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            
                            <!-- Modal de Detalhes -->
                            <div class="modal fade" id="modalDetalhes<?php echo $agendamento['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detalhes do Agendamento</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($agendamento['data'])); ?></p>
                                            <p><strong>Horário:</strong> <?php echo date('H:i', strtotime($agendamento['horario'])); ?></p>
                                            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($agendamento['cliente_nome']); ?></p>
                                            <p><strong>Telefone:</strong> <?php echo htmlspecialchars($agendamento['cliente_telefone']); ?></p>
                                            <?php if ($agendamento['cliente_email']): ?>
                                                <p><strong>E-mail:</strong> <?php echo htmlspecialchars($agendamento['cliente_email']); ?></p>
                                            <?php endif; ?>
                                            <p><strong>Pet:</strong> <?php echo htmlspecialchars($agendamento['pet_nome']); ?></p>
                                            <?php if ($agendamento['pet_tipo']): ?>
                                                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($agendamento['pet_tipo']); ?></p>
                                            <?php endif; ?>
                                            <p><strong>Serviço:</strong> <?php echo htmlspecialchars($agendamento['servico_nome']); ?></p>
                                            <?php if ($agendamento['observacoes']): ?>
                                                <p><strong>Observações:</strong></p>
                                                <p><?php echo nl2br(htmlspecialchars($agendamento['observacoes'])); ?></p>
                                            <?php endif; ?>
                                            <p><strong>Status:</strong> 
                                                <span class="badge bg-<?php 
                                                    echo $agendamento['status'] == 'confirmado' ? 'success' : 
                                                        ($agendamento['status'] == 'pendente' ? 'warning' : 
                                                        ($agendamento['status'] == 'cancelado' ? 'danger' : 'info')); 
                                                ?>">
                                                    <?php echo ucfirst($agendamento['status']); ?>
                                                </span>
                                            </p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Nenhum agendamento encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
if (isset($stmt)) $stmt->close();
$conn->close();
require_once 'includes/footer.php';
?>

