CREATE DATABASE IF NOT EXISTS black_friday;

USE black_friday;

CREATE TABLE categorias (
	id_categorias INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL
);

    CREATE TABLE produtos (
		id_produto INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(100) NOT NULL,
        id_categoria INT,
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categorias)
	);
    
    CREATE TABLE precos (
    
		id_preco INT PRIMARY KEY AUTO_INCREMENT,
        id_produto INT NOT NULL,
        preco_normal DECIMAL(10, 2),
        data_inicio_promocao DATE,
        data_fim_promocao DATE,
        FOREIGN KEY (id_produto) REFERENCES produtos(id_produto)
	);
    
    CREATE TABLE detalhes_produtos (
		id_detalhe INT PRIMARY KEY AUTO_INCREMENT,
        id_produto INT,
        chave_detalhe VARCHAR(50),
        valor_detalhe VARCHAR(100),
        FOREIGN KEY (id_produto) REFERENCES produtos(id_produto)
	);
    

	/*
 * Tabela 1: O Pedido
 */
CREATE TABLE pedidos (
    id_pedido INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_pedido DECIMAL(10, 2) NOT NULL,
    
    -- Link para a tabela de usuários
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

/*
 * Tabela 2: Os Itens do Pedido
 */
CREATE TABLE itens_pedido (
    id_item INT PRIMARY KEY AUTO_INCREMENT,
    id_pedido INT NOT NULL,
    id_produto INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10, 2) NOT NULL,
    
    -- Link para a tabela de pedidos
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido),
    -- Link para a tabela de produtos
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto)
);
    
    INSERT INTO categorias (nome) VALUES
	('Video Games'),
    ('Monitores'),
    ('Celulares');
    
INSERT INTO produtos (nome, id_categoria) VALUES
('PlayStation 5 Pro', (SELECT id_categorias FROM categorias WHERE nome = 'Video Games')),
('Xbox Series X/S', (SELECT id_categorias FROM categorias WHERE nome = 'Video Games')),
('Nintendo Switch 2', (SELECT id_categorias FROM categorias WHERE nome = 'Video Games')),
('Monitor Gamer AOC 27" | 27G2S/BK', (SELECT id_categorias FROM categorias WHERE nome = 'Monitores')),
('Monitor Gamer LG UltraGear 24" | 24GS60F-B', (SELECT id_categorias FROM categorias WHERE nome = 'Monitores')),
('Monitor Gamer AOC 23,8" | 24G25S/BK', (SELECT id_categorias FROM categorias WHERE nome = 'Monitores')),
('Xiaomi Redmi Note 14 pro 8GB RAM 256GB (5G)', (SELECT id_categorias FROM categorias WHERE nome = 'Celulares')),
('Samsung Galaxy S25 Ultra 12GB RAM 512GB (5G)', (SELECT id_categorias FROM categorias WHERE nome = 'Celulares')),
('Xiaomi POCO X7 Pro 12GB RAM 512GB (5GB)', (SELECT id_categorias FROM categorias WHERE nome = 'Celulares'));

ALTER TABLE precos ADD COLUMN preco_black_friday DECIMAL(10, 2);

INSERT INTO precos (id_produto, preco_normal, preco_black_friday, data_inicio_promocao, data_fim_promocao) VALUES
    (1, 4500.00, 3999.00, '2025-11-01', '2025-11-28'), -- PS5 Pro
    (2, 3500.00, 2999.00, '2025-11-01', '2025-11-28'), -- Xbox Series X/S
    (3, 2500.00, 2200.00, '2025-11-01', '2025-11-28'), -- Switch 2
    (4, 1800.00, 1500.00, '2025-11-01', '2025-11-28'), -- Monitor AOC 27"
    (5, 1200.00, 999.00, '2025-11-01', '2025-11-28'), -- Monitor LG 24"
    (6, 1100.00, 950.00, '2025-11-01', '2025-11-28'), -- Monitor AOC 23,8"
    (7, 2100.00, 1800.00, '2025-11-01', '2025-11-28'), -- Redmi Note 14 Pro
    (8, 9000.00, 7500.00, '2025-11-01', '2025-11-28'), -- Galaxy S25 Ultra
    (9, 2900.00, 2500.00, '2025-11-01', '2025-11-28'); --  POCO X7 Pro
    
    INSERT INTO detalhes_produtos (id_produto, chave_detalhe, valor_detalhe) VALUES
    (7, 'RAM', '8GB'),
    (7, 'Armazenamento', '256GB'),
    (7, 'Conectividade', '5G'),
    (8, 'RAM', '12GB'),
    (8, 'Armazenamento', '512GB'),
    (8, 'Conectividade', '5G'),
    (9, 'RAM', '8GB'),
    (9, 'Armazenamento', '2512GB'),
    (9, 'Conectividade', '5G');
    
    SELECT
		p.nome AS produto
        c.nome AS categoria
        pr.preco_normal
        pr.preco_black_friday
    FROM 
		produtos p
	JOIN
		categorias c ON p.id_categoria = c.id_categoria
	JOIN
		precos pr ON p.id_produto = pr.id_produto
	WHERE
		pr.preco_black_friday IS NOT FULL
	AND
		pr.data_inicio_promocao <= CURDATE()
	AND 
		pr.data_fim_promocao >= CURDATE();