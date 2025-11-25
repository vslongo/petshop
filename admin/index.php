<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

$page_title = 'Dashboard';

$conn = getConnection();

// Estatísticas
$stats = [];

// Total de produtos
$query = "SELECT COUNT(*) as total FROM produtos";
$result = $conn->query($query);
$stats['produtos'] = $result->fetch_assoc()['total'];

// Total de serviços
$query = "SELECT COUNT(*) as total FROM servicos WHERE ativo = 1";
$result = $conn->query($query);
$stats['servicos'] = $result->fetch_assoc()['total'];

// Agendamentos do mês
$query = "SELECT COUNT(*) as total FROM agendamentos WHERE MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())";
$result = $conn->query($query);
$stats['agendamentos_mes'] = $result->fetch_assoc()['total'];

// Agendamentos pendentes
$query = "SELECT COUNT(*) as total FROM agendamentos WHERE status = 'pendente' AND data >= CURDATE()";
$result = $conn->query($query);
$stats['pendentes'] = $result->fetch_assoc()['total'];

// Próximos agendamentos
$query = "SELECT a.*, s.nome as servico_nome 
          FROM agendamentos a 
          JOIN servicos s ON a.servico_id = s.id 
          WHERE a.data >= CURDATE() 
          ORDER BY a.data ASC, a.horario ASC 
          LIMIT 10";
$proximos = $conn->query($query);
?>

<h1 class="h2 mb-4">Dashboard</h1>

<!-- Cards de Estatísticas -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Produtos</h6>
                    <h2 class="mb-0"><?php echo $stats['produtos']; ?></h2>
                </div>
                <i class="bi bi-bag" style="font-size: 48px; color: #667eea;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Serviços Ativos</h6>
                    <h2 class="mb-0"><?php echo $stats['servicos']; ?></h2>
                </div>
                <i class="bi bi-heart-pulse" style="font-size: 48px; color: #667eea;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Agendamentos (Mês)</h6>
                    <h2 class="mb-0"><?php echo $stats['agendamentos_mes']; ?></h2>
                </div>
                <i class="bi bi-calendar-check" style="font-size: 48px; color: #667eea;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Pendentes</h6>
                    <h2 class="mb-0"><?php echo $stats['pendentes']; ?></h2>
                </div>
                <i class="bi bi-clock-history" style="font-size: 48px; color: #667eea;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Próximos Agendamentos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Próximos Agendamentos</h5>
    </div>
    <div class="card-body">
        <?php if ($proximos && $proximos->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Cliente</th>
                            <th>Pet</th>
                            <th>Serviço</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($agendamento = $proximos->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($agendamento['data'])); ?></td>
                                <td><?php echo date('H:i', strtotime($agendamento['horario'])); ?></td>
                                <td><?php echo htmlspecialchars($agendamento['cliente_nome']); ?></td>
                                <td><?php echo htmlspecialchars($agendamento['pet_nome']); ?></td>
                                <td><?php echo htmlspecialchars($agendamento['servico_nome']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $agendamento['status'] == 'confirmado' ? 'success' : 
                                            ($agendamento['status'] == 'pendente' ? 'warning' : 
                                            ($agendamento['status'] == 'cancelado' ? 'danger' : 'info')); 
                                    ?>">
                                        <?php echo ucfirst($agendamento['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-3">
                <a href="agendamentos.php" class="btn btn-primary">Ver Todos os Agendamentos</a>
            </div>
        <?php else: ?>
            <p class="text-muted text-center">Nenhum agendamento encontrado.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>

