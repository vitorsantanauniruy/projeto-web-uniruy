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
        
    // --- FINALIZAÇÃO DE PEDIDO ---
    case 'finalizar_pedido':
        // 1. Verifique se o usuário está logado
        if (!isset($_SESSION['user_id'])) {
             // Salva o destino para redirecionar após o login
             $_SESSION['destino'] = 'carrinho.php'; 
             header("Location: ../login.php?status=necessario");
             exit();
        }
        
        // 2. Verifique se o carrinho não está vazio
        if (empty($_SESSION['carrinho'])) {
             header("Location: ../carrinho.php?status=vazio");
             exit();
        }
        
        /* * LÓGICA DE PEDIDO (Simples)
         * Em um projeto real, aqui você salvaria o pedido em tabelas
         * `pedidos` e `itens_pedido` no banco de dados.
         * Para este projeto, vamos apenas limpar o carrinho e agradecer.
         */
        
        unset($_SESSION['carrinho']);
        header("Location: ../index.php?status=pedido_sucesso");
        exit();
        break;

    default:
        // Ação desconhecida, volta para a home
        header("Location: ../index.php");
}