<?php
// php/router.php
session_start();
require_once 'conexao.php';

$acao = $_REQUEST['acao'] ?? 'view'; // 'view' é um padrão seguro

switch ($acao) {

    // --- AÇÕES DE AUTENTICAÇÃO ---
    case 'cadastro':
        $nome = $_POST['name'];
        $sobrenome = $_POST['lastname'];
        $email = $_POST['email'];
        $senha = $_POST['password'];
        $confirmaSenha = $_POST['confirmpassword'];

        if ($senha !== $confirmaSenha) {
            header("Location: ../cadastro.php?status=erro_senha");
            exit();
        }
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO usuarios (nome, sobrenome, email, senha) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $sobrenome, $email, $senhaHash]);
            header("Location: ../login.php?status=sucesso");
            exit();
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                 header("Location: ../cadastro.php?status=erro_email");
            } else {
                 header("Location: ../cadastro.php?status=erro_generico");
            }
            exit();
        }
        break;

    case 'login':
        $email = $_POST['email'];
        $senha = $_POST['password'];

        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['user_name'] = $usuario['nome'];
                header("Location: ../index.php");
                exit();
            } else {
                header("Location: ../login.php?status=erro_login");
                exit();
            }
        } catch (\PDOException $e) {
            header("Location: ../login.php?status=erro_generico");
            exit();
        }
        break;

    case 'logout':
        session_unset();
        session_destroy();
        header("Location: ../index.php");
        exit();
        break;

    // --- AÇÕES DO CARRINHO ---
    case 'adicionar_carrinho':
        $produto_id = (int)($_POST['produto_id'] ?? 0);
        $quantidade = (int)($_POST['quantidade'] ?? 1);

        if ($produto_id <= 0 || $quantidade <= 0) {
            // Não faz nada se os dados forem inválidos
            header("Location: ../produtos.php?status=erro_adicionar");
            exit();
        }

        // Inicializa o carrinho na sessão se não existir
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }

        // Adiciona ou atualiza a quantidade
        if (isset($_SESSION['carrinho'][$produto_id])) {
            $_SESSION['carrinho'][$produto_id] += $quantidade;
        } else {
            $_SESSION['carrinho'][$produto_id] = $quantidade;
        }
        
        // Redireciona para a página do carrinho
        header("Location: ../carrinho.php");
        exit();
        break;

    case 'remover_carrinho':
        $produto_id = (int)($_GET['id'] ?? 0);

        if ($produto_id > 0 && isset($_SESSION['carrinho'][$produto_id])) {
            unset($_SESSION['carrinho'][$produto_id]);
        }
        header("Location: ../carrinho.php");
        exit();
        break;

    case 'finalizar_pedido':
        // Verifique se o usuário está logado
        if (!isset($_SESSION['user_id'])) {
             $_SESSION['destino'] = 'carrinho.php'; 
             header("Location: ../login.php?status=necessario");
             exit();
        }
        
        // Verifique se o carrinho não está vazio
        $carrinho = $_SESSION['carrinho'] ?? [];
        if (empty($carrinho)) {
             header("Location: ../carrinho.php?status=vazio");
             exit();
        }
        
        // Inicia o "modo de segurança" do banco de dados
        $pdo->beginTransaction();

        try {
            //Pegar os detalhes e preços atuais dos produtos do carrinho
            $ids = implode(',', array_keys($carrinho));
            $stmt = $pdo->prepare("SELECT id_produto, preco FROM produtos WHERE id_produto IN (" . str_repeat('?,', count(array_keys($carrinho)) - 1) . "?)");
            $stmt->execute(array_keys($carrinho));
            $produtos_db = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Transforma em [id => preco]

            $total_pedido = 0.0;
            $itens_para_salvar = [];

            foreach ($carrinho as $id_produto => $quantidade) {
                $preco_unitario = $produtos_db[$id_produto]; // Pega o preço atual do BD
                $total_pedido += ($preco_unitario * $quantidade);
                
                $itens_para_salvar[] = [
                    'id_produto' => $id_produto,
                    'quantidade' => $quantidade,
                    'preco_unitario' => $preco_unitario
                ];
            }

            //Salvar o Pedido (Pai) na tabela 'pedidos'
            $id_usuario = $_SESSION['user_id'];
            $sql_pedido = "INSERT INTO pedidos (id_usuario, total_pedido) VALUES (?, ?)";
            $stmt_pedido = $pdo->prepare($sql_pedido);
            $stmt_pedido->execute([$id_usuario, $total_pedido]);
            
            //Pegar o ID do pedido que acabamos de criar
            $id_pedido_novo = $pdo->lastInsertId();

            //Salvar os Itens do Pedido (Filhos) na tabela 'itens_pedido'
            $sql_itens = "INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?)";
            $stmt_itens = $pdo->prepare($sql_itens);
            
            foreach ($itens_para_salvar as $item) {
                $stmt_itens->execute([
                    $id_pedido_novo,
                    $item['id_produto'],
                    $item['quantidade'],
                    $item['preco_unitario']
                ]);
            }

            // SUCESSO! Salvar tudo permanentemente
            $pdo->commit();
            
            // Limpar o carrinho e redirecionar
            unset($_SESSION['carrinho']);
            header("Location: ../minhas_compras.php?status=sucesso"); // Redireciona para a nova página!
            exit();

        } catch (\Exception $e) {
            // FALHA! Desfazer todas as queries
            $pdo->rollBack();
            
            // Redireciona de volta ao carrinho com erro
            header("Location: ../carrinho.php?status=erro_pedido");
            exit();
        }
        
        break;

    default:
        // Ação desconhecida, volta para a home
        header("Location: ../index.php");
}