<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

$page_title = 'Configurar Horários';

$conn = getConnection();

// Processar ações
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        if ($_POST['acao'] === 'cadastrar' || $_POST['acao'] === 'editar') {
            $dia_semana = intval($_POST['dia_semana']);
            $hora_inicio = $_POST['hora_inicio'];
            $hora_fim = $_POST['hora_fim'];
            $hora_inicio_almoco = !empty($_POST['hora_inicio_almoco']) ? $_POST['hora_inicio_almoco'] : null;
            $hora_fim_almoco = !empty($_POST['hora_fim_almoco']) ? $_POST['hora_fim_almoco'] : null;
            $intervalo = intval($_POST['intervalo']);
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            if ($_POST['acao'] === 'cadastrar') {
                // Verificar se já existe configuração para este dia
                $check_query = "SELECT id FROM horarios_config WHERE dia_semana = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param("i", $dia_semana);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    $mensagem = 'Já existe uma configuração para este dia da semana. Use a opção de editar.';
                    $tipo_mensagem = 'warning';
                } else {
                    $query = "INSERT INTO horarios_config (dia_semana, hora_inicio, hora_fim, hora_inicio_almoco, hora_fim_almoco, intervalo, ativo) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    if ($stmt) {
                        $stmt->bind_param("issssii", $dia_semana, $hora_inicio, $hora_fim, $hora_inicio_almoco, $hora_fim_almoco, $intervalo, $ativo);
                        
                        if ($stmt->execute()) {
                            $mensagem = 'Horário cadastrado com sucesso!';
                            $tipo_mensagem = 'success';
                        } else {
                            $mensagem = 'Erro ao salvar horário: ' . $stmt->error;
                            $tipo_mensagem = 'danger';
                        }
                        $stmt->close();
                    } else {
                        $mensagem = 'Erro ao preparar query: ' . $conn->error;
                        $tipo_mensagem = 'danger';
                    }
                    
                    // Fechado no bloco anterior
                }
                $check_stmt->close();
            } else {
                $id = intval($_POST['id']);
                $query = "UPDATE horarios_config SET dia_semana=?, hora_inicio=?, hora_fim=?, hora_inicio_almoco=?, hora_fim_almoco=?, intervalo=?, ativo=? WHERE id=?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("issssiii", $dia_semana, $hora_inicio, $hora_fim, $hora_inicio_almoco, $hora_fim_almoco, $intervalo, $ativo, $id);
                
                if ($stmt->execute()) {
                    $mensagem = 'Horário atualizado com sucesso!';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = 'Erro ao atualizar horário.';
                    $tipo_mensagem = 'danger';
                }
                $stmt->close();
            }
        }
    }
}

// Excluir horário
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $query = "DELETE FROM horarios_config WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $mensagem = 'Horário excluído com sucesso!';
        $tipo_mensagem = 'success';
    }
    $stmt->close();
}

// Buscar horário para edição
$horario_edicao = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $query = "SELECT * FROM horarios_config WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $horario_edicao = $result->fetch_assoc();
    $stmt->close();
}

// Listar horários
$query = "SELECT id, dia_semana, TIME_FORMAT(hora_inicio, '%H:%i') as hora_inicio, 
          TIME_FORMAT(hora_fim, '%H:%i') as hora_fim,
          TIME_FORMAT(hora_inicio_almoco, '%H:%i') as hora_inicio_almoco,
          TIME_FORMAT(hora_fim_almoco, '%H:%i') as hora_fim_almoco,
          intervalo, ativo 
          FROM horarios_config ORDER BY dia_semana";
$horarios = $conn->query($query);

$dias_semana = [
    0 => 'Domingo',
    1 => 'Segunda-feira',
    2 => 'Terça-feira',
    3 => 'Quarta-feira',
    4 => 'Quinta-feira',
    5 => 'Sexta-feira',
    6 => 'Sábado'
];
?>

<h1 class="h2 mb-4">Configurar Horários de Atendimento</h1>

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
                <h5 class="mb-0"><?php echo $horario_edicao ? 'Editar' : 'Cadastrar'; ?> Horário</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="acao" value="<?php echo $horario_edicao ? 'editar' : 'cadastrar'; ?>">
                    <?php if ($horario_edicao): ?>
                        <input type="hidden" name="id" value="<?php echo $horario_edicao['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Dia da Semana *</label>
                        <select class="form-select" name="dia_semana" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($dias_semana as $num => $nome): ?>
                                <option value="<?php echo $num; ?>" 
                                        <?php echo ($horario_edicao && $horario_edicao['dia_semana'] == $num) ? 'selected' : ''; ?>>
                                    <?php echo $nome; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hora Início *</label>
                            <input type="time" class="form-control" name="hora_inicio" 
                                   value="<?php echo $horario_edicao['hora_inicio'] ?? '09:00'; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hora Fim *</label>
                            <input type="time" class="form-control" name="hora_fim" 
                                   value="<?php echo $horario_edicao['hora_fim'] ?? '18:00'; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Início do Almoço</label>
                            <input type="time" class="form-control" name="hora_inicio_almoco" 
                                   value="<?php echo $horario_edicao['hora_inicio_almoco'] ?? '12:00'; ?>"
                                   placeholder="Ex: 12:00">
                            <small class="text-muted">Deixe vazio se não houver intervalo de almoço</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fim do Almoço</label>
                            <input type="time" class="form-control" name="hora_fim_almoco" 
                                   value="<?php echo $horario_edicao['hora_fim_almoco'] ?? '13:00'; ?>"
                                   placeholder="Ex: 13:00">
                            <small class="text-muted">Deixe vazio se não houver intervalo de almoço</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Intervalo (minutos) *</label>
                        <input type="number" class="form-control" name="intervalo" 
                               value="<?php echo $horario_edicao['intervalo'] ?? 60; ?>" 
                               min="15" step="15" required>
                        <small class="text-muted">Intervalo entre agendamentos. Use 60 para mostrar horários de hora em hora (9h, 10h, 11h...). Use 30 para meia em meia hora.</small>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="ativo" 
                               <?php echo (!$horario_edicao || $horario_edicao['ativo']) ? 'checked' : ''; ?>>
                        <label class="form-check-label">Ativo</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> <?php echo $horario_edicao ? 'Atualizar' : 'Cadastrar'; ?>
                    </button>
                    
                    <?php if ($horario_edicao): ?>
                        <a href="agenda.php" class="btn btn-secondary w-100 mt-2">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Horários Configurados</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Dia da Semana</th>
                                <th>Horário</th>
                                <th>Intervalo</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($horarios && $horarios->num_rows > 0): ?>
                                <?php while ($horario = $horarios->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $dias_semana[$horario['dia_semana']]; ?></td>
                                        <td>
                                    <?php echo $horario['hora_inicio']; ?> - <?php echo $horario['hora_fim']; ?>
                                    <?php if ($horario['hora_inicio_almoco']): ?>
                                        <br><small class="text-muted">Almoço: <?php echo $horario['hora_inicio_almoco']; ?> - <?php echo $horario['hora_fim_almoco']; ?></small>
                                    <?php endif; ?>
                                </td>
                                        <td><?php echo $horario['intervalo']; ?> minutos</td>
                                        <td>
                                            <span class="badge bg-<?php echo $horario['ativo'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $horario['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?editar=<?php echo $horario['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="?excluir=<?php echo $horario['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirmarExclusao('<?php echo $dias_semana[$horario['dia_semana']]; ?>')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Nenhum horário configurado.</td>
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

