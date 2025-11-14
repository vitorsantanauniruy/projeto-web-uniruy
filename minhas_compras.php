<?php
    session_start();
    require_once 'php/conexao.php';

    //Proteger a página: Se não está logado, manda para o login.
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $id_usuario = $_SESSION['user_id'];

    // Buscar todos os pedidos deste usuário, do mais novo para o mais antigo
    try {
        $stmt_pedidos = $pdo->prepare("
            SELECT * FROM pedidos 
            WHERE id_usuario = ? 
            ORDER BY data_pedido DESC
        ");
        $stmt_pedidos->execute([$id_usuario]);
        $pedidos = $stmt_pedidos->fetchAll();

    } catch (\PDOException $e) {
        die("Erro ao buscar pedidos: " . $e->getMessage());
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Compras - ByteShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body>
    
    <div class="container py-5">
        <h2>Minhas Compras</h2>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'sucesso'): ?>
            <div class="alert alert-success">
                Pedido realizado com sucesso! Obrigado por comprar conosco.
            </div>
        <?php endif; ?>

        <?php if (empty($pedidos)): ?>
            <div class="alert alert-info mt-4">
                Você ainda não fez nenhum pedido. <a href="produtos.php">Ver produtos</a>
            </div>
        <?php else: ?>
            <div class="accordion mt-4" id="accordionPedidos">
                
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="accordion-item dark-bg-color text-white">
                        <h2 class="accordion-header" id="heading-<?php echo $pedido['id_pedido']; ?>">
                            <button class="accordion-button collapsed dark-bg-color text-white" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse-<?php echo $pedido['id_pedido']; ?>" aria-expanded="false" 
                                    aria-controls="collapse-<?php echo $pedido['id_pedido']; ?>">
                                
                                <div class="d-flex justify-content-between w-100 pe-3">
                                    <strong>Pedido #<?php echo $pedido['id_pedido']; ?></strong>
                                    <span>Data: <?php echo date("d/m/Y H:i", strtotime($pedido['data_pedido'])); ?></span>
                                    <span>Total: R$ <?php echo number_format($pedido['total_pedido'], 2, ',', '.'); ?></span>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse-<?php echo $pedido['id_pedido']; ?>" class="accordion-collapse collapse" 
                             aria-labelledby="heading-<?php echo $pedido['id_pedido']; ?>" data-bs-parent="#accordionPedidos">
                            
                            <div class="accordion-body">
                                <p><strong>Itens neste pedido:</strong></p>
                                
                                <?php
                                    // Buscar os itens para ESTE pedido
                                    $stmt_itens = $pdo->prepare("
                                        SELECT i.*, p.nome 
                                        FROM itens_pedido i
                                        JOIN produtos p ON i.id_produto = p.id_produto
                                        WHERE i.id_pedido = ?
                                    ");
                                    $stmt_itens->execute([$pedido['id_pedido']]);
                                    $itens = $stmt_itens->fetchAll();
                                ?>
                                
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($itens as $item): ?>
                                        <li class="list-group-item bg-transparent text-white">
                                            <div class="d-flex justify-content-between">
                                                <span><?php echo htmlspecialchars($item['nome']); ?></span>
                                                <span>
                                                    <?php echo $item['quantidade']; ?>x 
                                                    (R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?> cada)
                                                </span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>
    </div>
    
    </body>
</html>