<?php
    session_start();
    require_once 'php/conexao.php'; // Conecta ao BD

    // Busca todos os produtos usando 'id_produto'
    try {
        $stmt = $pdo->query("SELECT id_produto, nome, descricao, preco, imagem_url FROM produtos");
        $produtos = $stmt->fetchAll();
    } catch (\PDOException $e) {
        die("Erro ao buscar produtos: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
                        <a href="carrinho.php" class="nav-link"> <i class="bi bi-cart"></i>
                            <?php
                                $itens_no_carrinho = 0;
                                if (isset($_SESSION['carrinho'])) {
                                    $itens_no_carrinho = count($_SESSION['carrinho']);
                                }
                            ?>
                            <?php if ($itens_no_carrinho > 0): ?>
                                <span class="qty-info"> <?php echo $itens_no_carrinho; ?> </span>
                            <?php else: ?>
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
                        <a class="nav-link" aria-current="page" href="index.php">Home</a> </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="produtos.php">Catálogo</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    </div> </div> <div class="container py-5">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-funnel me-2"></i>Filtrar por Categoria
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item" href="#">Monitores</a></li>
                                <li><a class="dropdown-item" href="#">Celulares</a></li>
                                <li><a class="dropdown-item" href="#">Video Games</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">

            <?php if (empty($produtos)): ?>
                <div class="col-12">
                    <p class="text-white">Nenhum produto cadastrado no momento.</p>
                </div>
            <?php else: ?>
                <?php foreach ($produtos as $produto): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100 dark-bg-color"> <img src="<?php echo htmlspecialchars($produto['imagem_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produto['nome']); ?>" style="height: 220px; object-fit: contain; padding: 10px;">
                            <div class="card-body d-flex flex-column text-white"> <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                                <p class="card-text flex-grow-1"><?php echo htmlspecialchars($produto['descricao']); ?></p>
                                <p class="fw-bold" style="color: #c09578;">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                                
                                <form action="php/router.php" method="POST" class="mt-auto">
                                    <input type="hidden" name="acao" value="adicionar_carrinho">
                                    <input type="hidden" name="produto_id" value="<?php echo $produto['id_produto']; ?>">
                                    <input type="hidden" name="quantidade" value="1"> <button type="submit" class="btn btn-dark w-100">Adicionar ao Carrinho</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>

</body>

</html>