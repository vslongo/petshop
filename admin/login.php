<?php
require_once '../config/database.php';

// Se já estiver logado, redirecionar
if (isset($_SESSION['admin_logado']) && $_SESSION['admin_logado'] === true) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (!empty($email) && !empty($senha)) {
        try {
            $conn = getConnection();
            
            // Verificar se a tabela existe
            $check_table = $conn->query("SHOW TABLES LIKE 'usuarios'");
            if ($check_table->num_rows === 0) {
                $erro = 'Erro: Tabela de usuários não encontrada. Execute o arquivo database.sql primeiro.';
                $conn->close();
            } else {
                $query = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";
                $stmt = $conn->prepare($query);
                
                if ($stmt === false) {
                    $erro = 'Erro ao preparar consulta: ' . $conn->error;
                } else {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 1) {
                        $usuario = $result->fetch_assoc();
                        
                        // Verificar se o hash da senha está vazio ou inválido
                        if (empty($usuario['senha'])) {
                            $erro = 'Erro: Senha do usuário não configurada. Execute o script de diagnóstico.';
                        } elseif (password_verify($senha, $usuario['senha'])) {
                            $_SESSION['admin_logado'] = true;
                            $_SESSION['admin_id'] = $usuario['id'];
                            $_SESSION['admin_nome'] = $usuario['nome'];
                            $_SESSION['admin_tipo'] = $usuario['tipo'];
                            header('Location: index.php');
                            exit;
                        } else {
                            $erro = 'E-mail ou senha incorretos.';
                        }
                    } else {
                        $erro = 'E-mail ou senha incorretos.';
                    }
                    $stmt->close();
                }
                $conn->close();
            }
        } catch (Exception $e) {
            $erro = 'Erro ao conectar com o banco de dados: ' . $e->getMessage();
        }
    } else {
        $erro = 'Por favor, preencha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Área Administrativa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-lock-fill" style="font-size: 48px; color: #667eea;"></i>
                            <h2 class="mt-3">Área Administrativa</h2>
                            <p class="text-muted">Petshop Premium</p>
                        </div>
                        
                        <?php if ($erro): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($erro); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Problemas com login?</strong> 
                            <a href="teste_login.php" class="alert-link">Execute o diagnóstico</a>
                        </div>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Entrar
                            </button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <a href="../index.php" class="text-decoration-none">
                                    <i class="bi bi-arrow-left"></i> Voltar ao site
                                </a>
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-white">
                        <strong>Login padrão:</strong><br>
                        E-mail: admin@petshop.com<br>
                        Senha: admin123
                    </small>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

