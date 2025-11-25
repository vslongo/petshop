<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

$page_title = 'Gerenciar Produtos';

$conn = getConnection();

// Processar ações
$mensagem = '';
$tipo_mensagem = '';

// Função para fazer upload de imagem
function uploadImagemProduto($file, $nome_produto, $imagem_atual = 'default.jpg') {
    $diretorio = '../assets/images/produtos/';
    
    // Verificar se o diretório existe, se não, criar
    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0755, true);
    }
    
    // Se não houver arquivo enviado, retornar imagem atual
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return $imagem_atual;
    }
    
    // Validar tipo de arquivo
    $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $tipo_arquivo = $file['type'];
    
    if (!in_array($tipo_arquivo, $tipos_permitidos)) {
        throw new Exception('Tipo de arquivo não permitido. Use apenas JPG, PNG, GIF ou WEBP.');
    }
    
    // Validar tamanho (máximo 5MB)
    $tamanho_maximo = 5 * 1024 * 1024; // 5MB em bytes
    if ($file['size'] > $tamanho_maximo) {
        throw new Exception('Arquivo muito grande. Tamanho máximo: 5MB.');
    }
    
    // Gerar nome único para o arquivo
    $extensao = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nome_limpo = preg_replace('/[^a-zA-Z0-9]/', '_', $nome_produto);
    $nome_arquivo = $nome_limpo . '_' . time() . '.' . $extensao;
    $caminho_completo = $diretorio . $nome_arquivo;
    
    // Mover arquivo
    if (move_uploaded_file($file['tmp_name'], $caminho_completo)) {
        // Se havia uma imagem anterior e não é a default, deletar
        if ($imagem_atual && $imagem_atual !== 'default.jpg' && file_exists($diretorio . $imagem_atual)) {
            @unlink($diretorio . $imagem_atual);
        }
        return $nome_arquivo;
    } else {
        throw new Exception('Erro ao fazer upload da imagem.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        if ($_POST['acao'] === 'cadastrar' || $_POST['acao'] === 'editar') {
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $descricao_curta = trim($_POST['descricao_curta'] ?? '');
            $preco = floatval(str_replace(',', '.', $_POST['preco'] ?? 0));
            $categoria = trim($_POST['categoria'] ?? '');
            $estoque = intval($_POST['estoque'] ?? 0);
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            // Processar imagem
            $imagem = 'default.jpg';
            
            // Se houver upload de arquivo, processar
            if (isset($_FILES['imagem_upload']) && $_FILES['imagem_upload']['error'] === UPLOAD_ERR_OK) {
                try {
                    $imagem_atual = $_POST['acao'] === 'editar' && isset($_POST['imagem_atual']) 
                        ? $_POST['imagem_atual'] 
                        : 'default.jpg';
                    $imagem = uploadImagemProduto($_FILES['imagem_upload'], $nome, $imagem_atual);
                } catch (Exception $e) {
                    $mensagem = 'Erro no upload: ' . $e->getMessage();
                    $tipo_mensagem = 'danger';
                    $imagem = $_POST['imagem_atual'] ?? 'default.jpg';
                }
            } 
            // Se não houver upload mas houver nome manual, usar o nome
            elseif (!empty(trim($_POST['imagem'] ?? ''))) {
                $imagem = trim($_POST['imagem']);
            }
            // Se estiver editando e não houver mudança, manter a imagem atual
            elseif ($_POST['acao'] === 'editar' && isset($_POST['imagem_atual'])) {
                $imagem = $_POST['imagem_atual'];
            }
            
            if (!isset($mensagem) || $tipo_mensagem !== 'danger') {
                if ($_POST['acao'] === 'cadastrar') {
                    $query = "INSERT INTO produtos (nome, descricao, descricao_curta, preco, categoria, estoque, ativo, imagem) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sssddsis", $nome, $descricao, $descricao_curta, $preco, $categoria, $estoque, $ativo, $imagem);
                } else {
                    $id = intval($_POST['id']);
                    $query = "UPDATE produtos SET nome=?, descricao=?, descricao_curta=?, preco=?, categoria=?, estoque=?, ativo=?, imagem=? WHERE id=?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sssddsisi", $nome, $descricao, $descricao_curta, $preco, $categoria, $estoque, $ativo, $imagem, $id);
                }
                
                if ($stmt->execute()) {
                    $mensagem = 'Produto ' . ($_POST['acao'] === 'cadastrar' ? 'cadastrado' : 'atualizado') . ' com sucesso!';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = 'Erro ao salvar produto.';
                    $tipo_mensagem = 'danger';
                }
                $stmt->close();
            }
        }
    }
}

// Excluir produto
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    
    // Buscar a imagem do produto antes de excluir
    $query_img = "SELECT imagem FROM produtos WHERE id = ?";
    $stmt_img = $conn->prepare($query_img);
    $stmt_img->bind_param("i", $id);
    $stmt_img->execute();
    $result_img = $stmt_img->get_result();
    
    if ($result_img->num_rows > 0) {
        $produto_img = $result_img->fetch_assoc();
        $imagem_produto = $produto_img['imagem'];
    }
    $stmt_img->close();
    
    // Excluir o produto
    $query = "DELETE FROM produtos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        // Excluir a imagem se não for a default
        if (isset($imagem_produto) && $imagem_produto && $imagem_produto !== 'default.jpg') {
            $caminho_imagem = '../assets/images/produtos/' . $imagem_produto;
            if (file_exists($caminho_imagem)) {
                @unlink($caminho_imagem);
            }
        }
        $mensagem = 'Produto excluído com sucesso!';
        $tipo_mensagem = 'success';
    } else {
        $mensagem = 'Erro ao excluir produto.';
        $tipo_mensagem = 'danger';
    }
    $stmt->close();
}

// Buscar produto para edição
$produto_edicao = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $query = "SELECT * FROM produtos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $produto_edicao = $result->fetch_assoc();
    $stmt->close();
}

// Listar produtos
$query = "SELECT * FROM produtos ORDER BY created_at DESC";
$produtos = $conn->query($query);
?>

<h1 class="h2 mb-4">Gerenciar Produtos</h1>

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
                <h5 class="mb-0"><?php echo $produto_edicao ? 'Editar' : 'Cadastrar'; ?> Produto</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="acao" value="<?php echo $produto_edicao ? 'editar' : 'cadastrar'; ?>">
                    <?php if ($produto_edicao): ?>
                        <input type="hidden" name="id" value="<?php echo $produto_edicao['id']; ?>">
                        <input type="hidden" name="imagem_atual" value="<?php echo htmlspecialchars($produto_edicao['imagem']); ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" class="form-control" name="nome" 
                               value="<?php echo htmlspecialchars($produto_edicao['nome'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descrição Curta</label>
                        <input type="text" class="form-control" name="descricao_curta" 
                               value="<?php echo htmlspecialchars($produto_edicao['descricao_curta'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descrição Completa</label>
                        <textarea class="form-control" name="descricao" rows="3"><?php echo htmlspecialchars($produto_edicao['descricao'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preço *</label>
                            <input type="text" class="form-control" name="preco" 
                                   placeholder="0.00" value="<?php echo number_format($produto_edicao['preco'] ?? 0, 2, ',', '.'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estoque</label>
                            <input type="number" class="form-control" name="estoque" 
                                   value="<?php echo $produto_edicao['estoque'] ?? 0; ?>" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Categoria</label>
                        <input type="text" class="form-control" name="categoria" 
                               value="<?php echo htmlspecialchars($produto_edicao['categoria'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Imagem do Produto</label>
                        
                        <?php if ($produto_edicao && $produto_edicao['imagem']): ?>
                            <div class="mb-2">
                                <label class="form-label small text-muted">Imagem Atual:</label>
                                <div class="border rounded p-2 text-center" style="background: #f8f9fa;">
                                    <img src="../assets/images/produtos/<?php echo htmlspecialchars($produto_edicao['imagem']); ?>" 
                                         alt="Imagem atual" 
                                         class="img-thumbnail" 
                                         style="max-height: 150px; max-width: 100%;"
                                         onerror="this.src='../assets/images/produtos/default.jpg'">
                                    <p class="small text-muted mt-2 mb-0"><?php echo htmlspecialchars($produto_edicao['imagem']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-2">
                            <label class="form-label small">Fazer Upload de Nova Imagem:</label>
                            <input type="file" class="form-control" name="imagem_upload" 
                                   id="imagem_upload" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                                   onchange="previewImagem(this)">
                            <small class="text-muted">Formatos aceitos: JPG, PNG, GIF, WEBP (máx. 5MB)</small>
                        </div>
                        
                        <div id="preview_container" class="mb-2" style="display: none;">
                            <label class="form-label small text-muted">Preview da Nova Imagem:</label>
                            <div class="border rounded p-2 text-center" style="background: #f8f9fa;">
                                <img id="preview_img" src="" alt="Preview" class="img-thumbnail" style="max-height: 150px; max-width: 100%;">
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label small">OU usar nome de arquivo existente:</label>
                            <input type="text" class="form-control" name="imagem" 
                                   value="<?php echo htmlspecialchars($produto_edicao['imagem'] ?? 'default.jpg'); ?>"
                                   placeholder="nome_arquivo.jpg">
                            <small class="text-muted">Deixe em branco se fizer upload acima</small>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="ativo" 
                               <?php echo (!$produto_edicao || $produto_edicao['ativo']) ? 'checked' : ''; ?>>
                        <label class="form-check-label">Ativo</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> <?php echo $produto_edicao ? 'Atualizar' : 'Cadastrar'; ?>
                    </button>
                    
                    <?php if ($produto_edicao): ?>
                        <a href="produtos.php" class="btn btn-secondary w-100 mt-2">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lista de Produtos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Imagem</th>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Preço</th>
                                <th>Estoque</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($produtos && $produtos->num_rows > 0): ?>
                                <?php while ($produto = $produtos->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <img src="../assets/images/produtos/<?php echo htmlspecialchars($produto['imagem']); ?>" 
                                                 alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                                                 class="img-thumbnail" 
                                                 style="width: 60px; height: 60px; object-fit: cover;"
                                                 onerror="this.src='../assets/images/produtos/default.jpg'">
                                        </td>
                                        <td><?php echo $produto['id']; ?></td>
                                        <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                        <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                                        <td><?php echo $produto['estoque']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $produto['ativo'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $produto['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?editar=<?php echo $produto['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="?excluir=<?php echo $produto['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirmarExclusao('<?php echo htmlspecialchars($produto['nome']); ?>')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Nenhum produto cadastrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImagem(input) {
    const previewContainer = document.getElementById('preview_container');
    const previewImg = document.getElementById('preview_img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.style.display = 'none';
    }
}

function confirmarExclusao(nome) {
    return confirm('Tem certeza que deseja excluir o produto "' + nome + '"?');
}
</script>

<?php
$conn->close();
require_once 'includes/footer.php';
?>

