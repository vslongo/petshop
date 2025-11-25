<?php
require_once 'config/database.php';
$page_title = 'Agendamento';
include 'includes/header.php';

$conn = getConnection();

// Processar agendamento
$agendado = false;
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_nome = trim($_POST['cliente_nome'] ?? '');
    $cliente_telefone = trim($_POST['cliente_telefone'] ?? '');
    $cliente_email = trim($_POST['cliente_email'] ?? '');
    $pet_nome = trim($_POST['pet_nome'] ?? '');
    $pet_tipo = trim($_POST['pet_tipo'] ?? '');
    $servico_id = (int)($_POST['servico_id'] ?? 0);
    $data = $_POST['data'] ?? '';
    $horario = $_POST['horario'] ?? '';
    $observacoes = trim($_POST['observacoes'] ?? '');
    
    // Validações
    if (empty($cliente_nome) || empty($cliente_telefone) || empty($pet_nome) || $servico_id <= 0 || empty($data) || empty($horario)) {
        $erro = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        // Verificar se o horário já está ocupado
        $check_query = "SELECT id FROM agendamentos WHERE data = ? AND horario = ? AND status NOT IN ('cancelado')";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ss", $data, $horario);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $erro = 'Este horário já está ocupado. Por favor, escolha outro horário.';
            $check_stmt->close();
        } else {
            $check_stmt->close();
            
            // Inserir agendamento
            // Converter horário para formato TIME se necessário
            $horario_formatado = date('H:i:s', strtotime($horario));
            
            // Garantir que email e observacoes não sejam null
            if (empty($cliente_email)) {
                $cliente_email = '';
            }
            if (empty($observacoes)) {
                $observacoes = '';
            }
            if (empty($pet_tipo)) {
                $pet_tipo = '';
            }
            
            $insert_query = "INSERT INTO agendamentos (cliente_nome, cliente_telefone, cliente_email, pet_nome, pet_tipo, servico_id, data, horario, observacoes, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente')";
            $insert_stmt = $conn->prepare($insert_query);
            
            // Verificar se a preparação foi bem sucedida
            if (!$insert_stmt) {
                $erro = 'Erro ao preparar query: ' . $conn->error;
            } else {
                // Bind dos parâmetros: s=string, i=integer
                // 1. cliente_nome (s), 2. cliente_telefone (s), 3. cliente_email (s), 
                // 4. pet_nome (s), 5. pet_tipo (s), 6. servico_id (i), 
                // 7. data (s), 8. horario (s), 9. observacoes (s)
                // Total: 9 parâmetros = "sssssisss" (9 caracteres)
                $bind_result = $insert_stmt->bind_param("sssssisss", 
                    $cliente_nome, 
                    $cliente_telefone, 
                    $cliente_email, 
                    $pet_nome, 
                    $pet_tipo, 
                    $servico_id, 
                    $data, 
                    $horario_formatado, 
                    $observacoes
                );
                
                if (!$bind_result) {
                    $erro = 'Erro ao vincular parâmetros: ' . $insert_stmt->error;
                } elseif ($insert_stmt->execute()) {
                    $agendado = true;
                    // Limpar os campos após sucesso (opcional)
                    $_POST = [];
                } else {
                    $erro = 'Erro ao realizar agendamento: ' . $insert_stmt->error;
                    // Debug adicional
                    error_log("Erro SQL: " . $insert_stmt->error);
                    error_log("Dados: nome=$cliente_nome, tel=$cliente_telefone, email=$cliente_email, pet=$pet_nome, tipo=$pet_tipo, servico=$servico_id, data=$data, horario=$horario_formatado");
                }
                $insert_stmt->close();
            }
        }
    }
}

// Buscar serviços
$query_servicos = "SELECT * FROM servicos WHERE ativo = 1 ORDER BY nome";
$result_servicos = $conn->query($query_servicos);

// Buscar horários ocupados para os próximos 30 dias
$query_ocupados = "SELECT data, TIME_FORMAT(horario, '%H:%i') as horario FROM agendamentos 
                   WHERE data >= CURDATE() AND data <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
                   AND status NOT IN ('cancelado')
                   ORDER BY data, horario";
$result_ocupados = $conn->query($query_ocupados);
$horarios_ocupados = [];
if ($result_ocupados) {
    while ($row = $result_ocupados->fetch_assoc()) {
        $data_formatada = $row['data'];
        if (!isset($horarios_ocupados[$data_formatada])) {
            $horarios_ocupados[$data_formatada] = [];
        }
        $horarios_ocupados[$data_formatada][] = $row['horario'];
    }
}

// Buscar configuração de horários
$query_config = "SELECT dia_semana, TIME_FORMAT(hora_inicio, '%H:%i') as hora_inicio, 
                 TIME_FORMAT(hora_fim, '%H:%i') as hora_fim, 
                 TIME_FORMAT(hora_inicio_almoco, '%H:%i') as hora_inicio_almoco,
                 TIME_FORMAT(hora_fim_almoco, '%H:%i') as hora_fim_almoco,
                 intervalo 
                 FROM horarios_config WHERE ativo = 1";
$result_config = $conn->query($query_config);
$horarios_config = [];
if ($result_config) {
    while ($row = $result_config->fetch_assoc()) {
        $horarios_config[$row['dia_semana']] = $row;
    }
}

// Verificar se há horários configurados
if (empty($horarios_config)) {
    $erro_config = 'Nenhum horário de atendimento configurado. Por favor, configure os horários no painel administrativo.';
}

// Serviço pré-selecionado
$servico_selecionado = isset($_GET['servico']) ? (int)$_GET['servico'] : 0;
?>

<div class="page-header">
    <div class="container">
        <h1>Agendar Serviço</h1>
        <p class="lead">Escolha o melhor horário para cuidar do seu pet</p>
    </div>
</div>

<div class="container py-5">
    <?php if ($agendado): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h4 class="alert-heading"><i class="bi bi-check-circle-fill"></i> Agendamento realizado com sucesso!</h4>
            <p>Seu agendamento foi confirmado. Entraremos em contato em breve para confirmar os detalhes.</p>
            <hr>
            <p class="mb-0">Agradecemos pela confiança em nossos serviços!</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif (isset($erro_config)): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($erro_config); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($erro): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($erro); ?>
            <?php if (isset($_POST) && !empty($_POST)): ?>
                <br><small>Debug: Verifique se todos os campos foram preenchidos corretamente.</small>
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <form method="POST" id="formAgendamento" class="needs-validation" novalidate>
        <div class="row">
            <!-- Calendário -->
            <div class="col-md-6 mb-4">
                <h3 class="mb-3">Selecione a Data</h3>
                <div id="calendario" class="calendar-container">
                    <!-- Calendário será gerado via JavaScript -->
                </div>
                <input type="hidden" name="data" id="dataSelecionada" required>
                <div class="invalid-feedback">Por favor, selecione uma data.</div>
            </div>
            
            <!-- Horários -->
            <div class="col-md-6 mb-4">
                <h3 class="mb-3">Selecione o Horário</h3>
                <div id="horariosDisponiveis" class="mb-3">
                    <p class="text-muted">Selecione uma data primeiro para ver os horários disponíveis.</p>
                </div>
                <input type="hidden" name="horario" id="horarioSelecionado" required>
                <div class="invalid-feedback">Por favor, selecione um horário.</div>
            </div>
        </div>
        
        <hr class="my-4">
        
        <!-- Dados do Cliente -->
        <h3 class="mb-3">Dados do Cliente</h3>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="cliente_nome" class="form-label">Nome Completo *</label>
                <input type="text" class="form-control" id="cliente_nome" name="cliente_nome" required>
                <div class="invalid-feedback">Por favor, informe seu nome.</div>
            </div>
            <div class="col-md-6">
                <label for="cliente_telefone" class="form-label">Telefone *</label>
                <input type="tel" class="form-control" id="cliente_telefone" name="cliente_telefone" 
                       placeholder="(11) 99999-9999" required>
                <div class="invalid-feedback">Por favor, informe um telefone válido.</div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="cliente_email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="cliente_email" name="cliente_email">
            </div>
            <div class="col-md-6">
                <label for="servico_id" class="form-label">Serviço *</label>
                <select class="form-select" id="servico_id" name="servico_id" required>
                    <option value="">Selecione um serviço...</option>
                    <?php while ($servico = $result_servicos->fetch_assoc()): ?>
                        <option value="<?php echo $servico['id']; ?>" 
                                <?php echo $servico['id'] == $servico_selecionado ? 'selected' : ''; ?>
                                data-duracao="<?php echo $servico['duracao']; ?>"
                                data-preco="<?php echo $servico['preco']; ?>">
                            <?php echo htmlspecialchars($servico['nome']); ?> 
                            - R$ <?php echo number_format($servico['preco'], 2, ',', '.'); ?>
                            (<?php echo $servico['duracao']; ?> min)
                        </option>
                    <?php endwhile; ?>
                </select>
                <div class="invalid-feedback">Por favor, selecione um serviço.</div>
            </div>
        </div>
        
        <hr class="my-4">
        
        <!-- Dados do Pet -->
        <h3 class="mb-3">Dados do Pet</h3>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="pet_nome" class="form-label">Nome do Pet *</label>
                <input type="text" class="form-control" id="pet_nome" name="pet_nome" required>
                <div class="invalid-feedback">Por favor, informe o nome do pet.</div>
            </div>
            <div class="col-md-6">
                <label for="pet_tipo" class="form-label">Tipo de Animal</label>
                <select class="form-select" id="pet_tipo" name="pet_tipo">
                    <option value="">Selecione...</option>
                    <option value="Cachorro">Cachorro</option>
                    <option value="Gato">Gato</option>
                    <option value="Ave">Ave</option>
                    <option value="Coelho">Coelho</option>
                    <option value="Hamster">Hamster</option>
                    <option value="Outro">Outro</option>
                </select>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea class="form-control" id="observacoes" name="observacoes" rows="3" 
                      placeholder="Informações adicionais sobre seu pet ou o serviço desejado..."></textarea>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-calendar-check"></i> Confirmar Agendamento
            </button>
        </div>
    </form>
</div>

<script>
// Dados para o JavaScript
const horariosOcupados = <?php echo json_encode($horarios_ocupados, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
const horariosConfig = <?php echo json_encode($horarios_config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

// Debug (remover em produção)
console.log('Horários ocupados:', horariosOcupados);
console.log('Configuração de horários:', horariosConfig);
</script>
<script src="assets/js/agendamento.js"></script>

<?php
$conn->close();
include 'includes/footer.php';
?>

