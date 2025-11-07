<?php
    session_start();
    require_once 'php/conexao.php'; // Conecta ao BD

    $carrinho = $_SESSION['carrinho'] ?? [];
    $produtos_no_carrinho = [];
    $total_carrinho = 0.0;

    if (!empty($carrinho)) {
        // Pega os IDs dos produtos para uma consulta
        // Usando 'id_produto'
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id_produto IN (" . str_repeat('?,', count(array_keys($carrinho)) - 1) . "?)");
        $stmt->execute(array_keys($carrinho));
        $produtos_db = $stmt->fetchAll();

        foreach ($produtos_db as $produto) {
            $quantidade = $carrinho[$produto['id_produto']]; // Usando 'id_produto'
            $subtotal = $produto['preco'] * $quantidade;
            $total_carrinho += $subtotal;
            
            $produtos_no_carrinho[] = [
                'id_produto' => $produto['id_produto'], // Usando 'id_produto'
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'quantidade' => $quantidade,
                'subtotal' => $subtotal,
            ];
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho</title>
    <!-- CSS Bootstrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- CSS do Projeto-->
    <link rel="stylesheet" href="assets/css/styles.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- JS Bootstrap-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"
        defer></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark" id="navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">ByteShop</a>
            <div class="navbar-items">
                <div></div>
                <form class="d-flex" role="search" id="search-form">
                    <input class="form-control me-2" type="search" placeholder="Busque o seu produto..."
                        aria-label="Search" />
                    <button class="btn btn-secondary" type="submit">Pesquisar</button>
                </form>
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                Olá, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="php/router.php?acao=logout" class="nav-link">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="login.php" class="nav-link">
                                <i class="bi bi-person"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item" id="cart-item">
                        <a href="carrinho.php" class="nav-link">
                            <i class="bi bi-cart"></i>
                            <?php
                                $itens_no_carrinho = 0;
                                if (isset($_SESSION['carrinho'])) {
                                    $itens_no_carrinho = count($_SESSION['carrinho']);
                                }
                            ?>
                            <?php if ($itens_no_carrinho > 0): ?>
                                <span class="qty-info"> <?php echo $itens_no_carrinho; ?> </span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <nav class="navbar navbar-expand-lg light-bg-color" id="bottom-navbar-container">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01"
                aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="produtos.php">Catálogo</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    </div> <div class="container py-5">
        <h2> Meu Carrinho</h2>

        <?php if (empty($produtos_no_carrinho)): ?>
            <div class="alert alert-info mt-4"> Seu carrinho está vazio. <a href="produtos.php">Começe a Comprar!</a> </div>

        <?php else: ?>
            <div class="container-responsive mt-4"> <table class="table text-white"> <thead>
                        <tr>
                            <th scope="col">Produto</th>
                            <th scope="col">Preço</th>
                            <th scope="col">Quantidade</th>
                            <th scope="col">Subtotal</th>
                            <th scope="col">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos_no_carrinho as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nome']); ?></td>
                                <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                                <td><?php echo $item['quantidade']; ?></td>
                                <td>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                                <td>
                                    <a href="php/router.php?acao=remover_carrinho&id=<?php echo $item['id_produto']; ?>" class="btn btn-danger btn-sm">
                                        <i class="bi bi-trash"></i> Remover
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th>R$ <?php echo number_format($total_carrinho, 2, ',', '.'); ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-end mt-4"> <a href="produtos.php" class="btn btn-secondary"> Continuar comprando </a>
                <a href="php/router.php?acao=finalizar_pedido" class="btn btn-success">Finalizar Compra</a>
            </div>
        
        <?php endif; ?>

    </div>
</body>

</html>