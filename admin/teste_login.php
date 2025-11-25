<?php
// Script de diagnóstico para problemas de login
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

echo "<h2>Diagnóstico de Login - Área Administrativa</h2>";
echo "<hr>";

// Teste 1: Conexão com banco
echo "<h3>1. Teste de Conexão com Banco de Dados</h3>";
try {
    $conn = getConnection();
    echo "✅ <strong>Sucesso:</strong> Conexão estabelecida com o banco de dados.<br>";
    
    // Teste 2: Verificar se a tabela usuarios existe
    echo "<h3>2. Verificação da Tabela 'usuarios'</h3>";
    $result = $conn->query("SHOW TABLES LIKE 'usuarios'");
    if ($result->num_rows > 0) {
        echo "✅ <strong>Sucesso:</strong> Tabela 'usuarios' existe.<br>";
    } else {
        echo "❌ <strong>Erro:</strong> Tabela 'usuarios' não encontrada. Execute o arquivo database.sql primeiro!<br>";
        $conn->close();
        exit;
    }
    
    // Teste 3: Verificar se existe usuário admin
    echo "<h3>3. Verificação de Usuários no Banco</h3>";
    $query = "SELECT id, nome, email, tipo, LENGTH(senha) as tamanho_senha FROM usuarios";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        echo "✅ <strong>Sucesso:</strong> Encontrados " . $result->num_rows . " usuário(s):<br>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Tipo</th><th>Tamanho Hash</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['tipo']) . "</td>";
            echo "<td>" . $row['tamanho_senha'] . " caracteres</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ <strong>Erro:</strong> Nenhum usuário encontrado no banco de dados!<br>";
        echo "<p>Vou criar o usuário administrador padrão agora...</p>";
        
        // Criar usuário admin
        $senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $query = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $nome = 'Administrador';
        $email = 'admin@petshop.com';
        $tipo = 'admin';
        $stmt->bind_param("ssss", $nome, $email, $senha_hash, $tipo);
        
        if ($stmt->execute()) {
            echo "✅ <strong>Sucesso:</strong> Usuário administrador criado!<br>";
            echo "<strong>Credenciais:</strong><br>";
            echo "E-mail: admin@petshop.com<br>";
            echo "Senha: admin123<br>";
        } else {
            echo "❌ <strong>Erro:</strong> Não foi possível criar o usuário: " . $stmt->error . "<br>";
        }
        $stmt->close();
    }
    
    // Teste 4: Verificar hash da senha
    echo "<h3>4. Teste de Validação de Senha</h3>";
    $query = "SELECT senha FROM usuarios WHERE email = 'admin@petshop.com'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        $hash_banco = $usuario['senha'];
        
        // Testar senha
        if (password_verify('admin123', $hash_banco)) {
            echo "✅ <strong>Sucesso:</strong> A senha 'admin123' está correta e o hash é válido!<br>";
        } else {
            echo "❌ <strong>Erro:</strong> O hash da senha no banco não corresponde a 'admin123'.<br>";
            echo "<p>Vou atualizar o hash da senha agora...</p>";
            
            $novo_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $query = "UPDATE usuarios SET senha = ? WHERE email = 'admin@petshop.com'";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $novo_hash);
            
            if ($stmt->execute()) {
                echo "✅ <strong>Sucesso:</strong> Hash da senha atualizado!<br>";
            } else {
                echo "❌ <strong>Erro:</strong> Não foi possível atualizar o hash: " . $stmt->error . "<br>";
            }
            $stmt->close();
        }
    } else {
        echo "⚠️ <strong>Aviso:</strong> Usuário admin@petshop.com não encontrado.<br>";
    }
    
    // Teste 5: Verificar sessão
    echo "<h3>5. Teste de Sessão PHP</h3>";
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "✅ <strong>Sucesso:</strong> Sessão PHP está ativa.<br>";
        echo "Session ID: " . session_id() . "<br>";
    } else {
        echo "❌ <strong>Erro:</strong> Sessão PHP não está ativa.<br>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ <strong>Erro:</strong> " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>Próximos Passos</h3>";
echo "<ol>";
echo "<li>Se todos os testes passaram, tente fazer login novamente com:<br>";
echo "<strong>E-mail:</strong> admin@petshop.com<br>";
echo "<strong>Senha:</strong> admin123</li>";
echo "<li>Se ainda não funcionar, verifique os logs de erro do PHP.</li>";
echo "<li>Certifique-se de que o banco de dados 'petshop_db' existe e está acessível.</li>";
echo "</ol>";

echo "<p><a href='login.php'>← Voltar para a página de login</a></p>";
?>




