-- =============================================
-- SCHEMA DELIVERY MULTI-LOJAS
-- =============================================
-- DROP DATABASE IF EXISTS delivery_lanches;
CREATE DATABASE IF NOT EXISTS delivery_lanches DEFAULT CHARACTER SET utf8mb4;
USE delivery_lanches;

-- -----------------------------------------------------
-- Table lojas
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS lojas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  cnpj CHAR(14) NULL DEFAULT NULL,
  email VARCHAR(150) NOT NULL,
  telefone VARCHAR(20) NULL DEFAULT NULL,
  endereco VARCHAR(255) NULL DEFAULT NULL,
  cidade VARCHAR(100) NULL DEFAULT NULL,
  estado CHAR(2) NULL DEFAULT NULL,
  cep CHAR(8) NULL DEFAULT NULL,
  logo LONGBLOB NULL DEFAULT NULL,
  taxa_entrega_padrao DECIMAL(10,2) DEFAULT 0.00,
  status ENUM('ativa','inativa') DEFAULT 'ativa',
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table administradores
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS administradores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  nivel ENUM('superadmin', 'admin', 'gerente', 'operador') DEFAULT 'operador',
  foto LONGBLOB NULL DEFAULT NULL,
  loja_id INT NULL DEFAULT NULL,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_admin_loja FOREIGN KEY (loja_id) REFERENCES lojas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE lojas
ADD COLUMN slug VARCHAR(100) NOT NULL UNIQUE AFTER nome;

-- -----------------------------------------------------
-- Table clientes
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Tabela: clientes
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  cpf CHAR(11) NULL DEFAULT NULL,
  telefone VARCHAR(20) NOT NULL,
  email VARCHAR(150) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  foto_perfil LONGBLOB NULL DEFAULT NULL,
  data_nascimento DATE NULL DEFAULT NULL,
  status ENUM('ativo', 'inativo') DEFAULT 'ativo',
  email_verificado TINYINT(1) DEFAULT 0,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_cpf (cpf),
  UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Tabela: endereco_cliente
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS endereco_cliente (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  loja_id INT NOT NULL,
  logradouro VARCHAR(255) NOT NULL,
  numero VARCHAR(20) NOT NULL,
  complemento VARCHAR(100) NULL DEFAULT NULL,
  bairro VARCHAR(100) NOT NULL,
  cidade VARCHAR(100) NOT NULL,
  estado VARCHAR(50) NOT NULL,
  cep VARCHAR(20) NULL DEFAULT NULL,
  principal TINYINT(1) DEFAULT 0,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_endereco_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
  CONSTRAINT fk_endereco_loja FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- -----------------------------------------------------
-- Table entregadores
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS entregadores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  telefone VARCHAR(20) NULL DEFAULT NULL,
  login VARCHAR(100) UNIQUE NULL DEFAULT NULL,
  senha VARCHAR(255) NULL DEFAULT NULL,
  status ENUM('disponivel', 'em_entrega', 'indisponivel') DEFAULT 'disponivel',
  veiculo VARCHAR(50) NULL DEFAULT NULL,
  localizacao_atual VARCHAR(255) NULL DEFAULT NULL,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  loja_id INT NOT NULL,
  CONSTRAINT fk_entregador_loja FOREIGN KEY (loja_id) REFERENCES lojas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =============================================
-- PARTE 2 - PRODUTOS, CATEGORIAS, PROMOÇÕES, CONFIGURAÇÕES E AVALIAÇÕES
-- =============================================

-- -----------------------------------------------------
-- Table categorias_produtos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias_produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao TEXT NULL DEFAULT NULL,
  ativo TINYINT(1) DEFAULT 1,
  loja_id INT NOT NULL,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_categoria_loja FOREIGN KEY (loja_id) REFERENCES lojas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table produtos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  descricao TEXT NULL DEFAULT NULL,
  preco DECIMAL(10,2) NOT NULL,
  imagem_principal LONGBLOB NULL DEFAULT NULL,
  estoque INT DEFAULT 0,
  disponivel TINYINT(1) DEFAULT 1,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ativo TINYINT(1) DEFAULT 1,
  categoria_id INT NULL DEFAULT NULL,
  loja_id INT NOT NULL,
  admin_id INT NOT NULL,
  CONSTRAINT fk_produto_categoria FOREIGN KEY (categoria_id) REFERENCES categorias_produtos(id),
  CONSTRAINT fk_produto_loja FOREIGN KEY (loja_id) REFERENCES lojas(id),
  CONSTRAINT fk_produto_admin FOREIGN KEY (admin_id) REFERENCES administradores(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------

-- -----------------------------------------------------

-- -----------------------------------------------------
-- Table configuracoes_sistema
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS configuracoes_sistema (
  id INT AUTO_INCREMENT PRIMARY KEY,
  loja_id INT NOT NULL,
  taxa_entrega_padrao DECIMAL(10,2) DEFAULT 0.00,
  valor_minimo_pedido DECIMAL(10,2) DEFAULT 0.00,
  horario_abertura TIME DEFAULT '08:00:00',
  horario_fechamento TIME DEFAULT '22:00:00',
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_config_loja FOREIGN KEY (loja_id) REFERENCES lojas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table promocoes
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS promocoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  codigo VARCHAR(50) NOT NULL,
  descricao TEXT NULL DEFAULT NULL,
  desconto DECIMAL(5,2) NOT NULL,
  ativo TINYINT(1) DEFAULT 1,
  data_inicio DATE NULL DEFAULT NULL,
  data_fim DATE NULL DEFAULT NULL,
  imagem LONGBLOB NULL DEFAULT NULL,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  loja_id INT NOT NULL,
  CONSTRAINT fk_promocao_loja FOREIGN KEY (loja_id) REFERENCES lojas(id),
  CONSTRAINT fk_promocao_admin FOREIGN KEY (admin_id) REFERENCES administradores(id),
  UNIQUE INDEX unique_codigo_loja (codigo, loja_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- MULTI-LOJA: CATEGORIAS, PRODUTOS, PROMOÇÕES, CONFIGURAÇÕES
-- =========================================================

-- -----------------------------------------------------
-- Tabela: lojas
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS lojas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  endereco VARCHAR(255),
  telefone VARCHAR(50),
  cidade VARCHAR(100),
  estado VARCHAR(50),
  bairro VARCHAR(100),
  cep VARCHAR(20),
  status ENUM('aberto', 'fechado') DEFAULT 'fechado',
  horario_funcionamento TEXT,
  logo LONGBLOB NULL,
  mensagem VARCHAR(255) DEFAULT NULL,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Tabela: categorias_produtos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias_produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao TEXT NULL DEFAULT NULL,
  ativo TINYINT(1) DEFAULT 1,
  loja_id INT NOT NULL,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_categoria_loja FOREIGN KEY (loja_id)
    REFERENCES lojas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Tabela: produtos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  descricao TEXT NULL DEFAULT NULL,
  preco DECIMAL(10,2) NOT NULL,
  estoque INT DEFAULT 0,
  disponivel TINYINT(1) DEFAULT 1,
  ativo TINYINT(1) DEFAULT 1,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  categoria_id INT NULL,
  imagem_principal LONGBLOB NULL DEFAULT NULL,
  loja_id INT NOT NULL,
  admin_id INT NOT NULL,
  CONSTRAINT fk_produto_categoria FOREIGN KEY (categoria_id)
    REFERENCES categorias_produtos(id) ON DELETE SET NULL,
  CONSTRAINT fk_produto_loja FOREIGN KEY (loja_id)
    REFERENCES lojas(id) ON DELETE CASCADE,
  CONSTRAINT fk_produto_admin FOREIGN KEY (admin_id)
    REFERENCES administradores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Tabela: fotos_produto
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS fotos_produto (
  id INT AUTO_INCREMENT PRIMARY KEY,
  produto_id INT NOT NULL,
  imagem LONGBLOB NOT NULL,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_foto_produto FOREIGN KEY (produto_id)
    REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Tabela: promocoes
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS promocoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(50) NOT NULL,
  descricao TEXT NULL DEFAULT NULL,
  desconto DECIMAL(5,2) NOT NULL,
  ativo TINYINT(1) DEFAULT 1,
  data_inicio DATE NULL DEFAULT NULL,
  data_fim DATE NULL DEFAULT NULL,
  imagem LONGBLOB NULL,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  loja_id INT NOT NULL,
  admin_id INT NOT NULL,
  CONSTRAINT fk_promocao_loja FOREIGN KEY (loja_id)
    REFERENCES lojas(id) ON DELETE CASCADE,
  CONSTRAINT fk_promocao_admin FOREIGN KEY (admin_id)
    REFERENCES administradores(id) ON DELETE CASCADE,
  UNIQUE INDEX unique_codigo_loja (codigo, loja_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Tabela: configuracoes_sistema (por loja)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS configuracoes_sistema (
  id INT AUTO_INCREMENT PRIMARY KEY,
  loja_id INT NOT NULL,
  taxa_entrega_padrao DECIMAL(10,2) DEFAULT 0.00,
  valor_minimo_pedido DECIMAL(10,2) DEFAULT 0.00,
  horario_abertura TIME DEFAULT '08:00:00',
  horario_fechamento TIME DEFAULT '22:00:00',
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_config_loja FOREIGN KEY (loja_id)
    REFERENCES lojas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Tabela: faixas_entrega (por loja)
-- -----------------------------------------------------
	CREATE TABLE IF NOT EXISTS faixas_entrega (
	  id INT AUTO_INCREMENT PRIMARY KEY,
	  loja_id INT NOT NULL,
	  nome_faixa VARCHAR(255) NOT NULL,
	  valor DECIMAL(10,2) NOT NULL,
	  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	  CONSTRAINT fk_faixa_loja FOREIGN KEY (loja_id)
		REFERENCES lojas(id) ON DELETE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE faixas_entrega 
ADD COLUMN setor VARCHAR(255) NOT NULL AFTER nome_faixa;




ALTER TABLE faixas_entrega 
ADD COLUMN ativo TINYINT(1) DEFAULT 1 AFTER valor;




ALTER TABLE faixas_entrega 
MODIFY COLUMN valor DECIMAL(10,2) NOT NULL;
-- -----------------------------------------------------
-- Tabela: formas_pagamento (por loja)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS formas_pagamento (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL,
  tipo ENUM('dinheiro', 'cartao', 'pix') NOT NULL,
  chave_pix VARCHAR(255) NULL DEFAULT NULL,
  ativo TINYINT(1) DEFAULT 1,
  loja_id INT NOT NULL,
  admin_id INT NOT NULL,
  CONSTRAINT fk_pagamento_loja FOREIGN KEY (loja_id)
    REFERENCES lojas(id) ON DELETE CASCADE,
  CONSTRAINT fk_pagamento_admin FOREIGN KEY (admin_id)
    REFERENCES administradores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- MULTI-LOJA: PEDIDOS, ITENS, PAGAMENTOS, AVALIAÇÕES
-- =========================================================

-- -----------------------------------------------------
-- Tabela: pedidos
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Tabela: pedidos (multi-loja)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  endereco_id INT NOT NULL,
  telefone VARCHAR(20) NOT NULL,
  localizacao VARCHAR(255) NULL,
  metodo_pagamento ENUM('dinheiro', 'cartao', 'pix') DEFAULT 'dinheiro',
  observacoes TEXT NULL,
  total DECIMAL(10,2) NOT NULL,
  taxa_entrega DECIMAL(10,2) DEFAULT 0.00,
  status ENUM('pendente', 'aceito', 'em_entrega', 'entregue', 'cancelado') DEFAULT 'pendente',
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  loja_id INT NOT NULL,
  admin_id INT NOT NULL,
  CONSTRAINT fk_pedido_cliente FOREIGN KEY (cliente_id) 
      REFERENCES clientes(id) ON DELETE CASCADE,
  CONSTRAINT fk_pedido_endereco FOREIGN KEY (endereco_id) 
      REFERENCES endereco_cliente(id) ON DELETE CASCADE,
  CONSTRAINT fk_pedido_loja FOREIGN KEY (loja_id) 
      REFERENCES lojas(id) ON DELETE CASCADE,
  CONSTRAINT fk_pedido_admin FOREIGN KEY (admin_id) 
      REFERENCES administradores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



---

-- -----------------------------------------------------
-- Tabela: itens_pedido
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS itens_pedido (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  produto_id INT NOT NULL,
  quantidade INT NOT NULL,
  preco_unitario DECIMAL(10,2) NOT NULL,
  loja_id INT NOT NULL,
  CONSTRAINT fk_item_pedido_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  CONSTRAINT fk_item_pedido_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
  CONSTRAINT fk_item_pedido_loja FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Tabela: pagamentos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS pagamentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  forma_pagamento_id INT NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  status ENUM('pendente', 'aprovado', 'cancelado') DEFAULT 'pendente',
  comprovante LONGBLOB NULL,
  data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  loja_id INT NOT NULL,
  CONSTRAINT fk_pagamento_pedido_pg FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  CONSTRAINT fk_pagamento_forma_pg FOREIGN KEY (forma_pagamento_id) REFERENCES formas_pagamento(id) ON DELETE CASCADE,
  CONSTRAINT fk_pagamento_loja_pg FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------


-- -----------------------------------------------------

-- -----------------------------------------------------
-- Tabela: historico_pedidos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS historico_pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  status ENUM('pendente', 'aceito', 'em_entrega', 'entregue', 'cancelado') DEFAULT 'pendente',
  alterado_por ENUM('cliente', 'admin', 'sistema') DEFAULT 'sistema',
  data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  loja_id INT NOT NULL,
  CONSTRAINT fk_historico_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  CONSTRAINT fk_historico_pedido_loja FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- MULTI-LOJA: NOTIFICAÇÕES, LOGS, CUPONS, FAIXAS ENTREGA
-- =========================================================

-- -----------------------------------------------------
-- Tabela: notificacoes
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS notificacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_tipo ENUM('cliente', 'admin', 'entregador') DEFAULT NULL,
  usuario_id INT DEFAULT NULL,
  mensagem TEXT NOT NULL,
  lida TINYINT(1) DEFAULT 0,
  data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  loja_id INT NOT NULL,
  CONSTRAINT fk_notificacao_loja FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Tabela: logs_sistema
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS logs_sistema (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_tipo ENUM('cliente', 'admin', 'entregador') DEFAULT NULL,
  usuario_id INT DEFAULT NULL,
  acao TEXT NOT NULL,
  data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  loja_id INT NOT NULL,
  CONSTRAINT fk_log_loja FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Tabela: cupons_clientes
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS cupons_clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  promocao_id INT NOT NULL,
  usado TINYINT(1) DEFAULT 0,
  data_uso TIMESTAMP NULL DEFAULT NULL,
  loja_id INT NOT NULL,
  CONSTRAINT fk_cupom_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
  CONSTRAINT fk_cupom_promocao FOREIGN KEY (promocao_id) REFERENCES promocoes(id) ON DELETE CASCADE,
  CONSTRAINT fk_cupom_loja FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Tabela: faixas_entrega
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS faixas_entrega (
  id INT AUTO_INCREMENT PRIMARY KEY,
  loja_id INT NOT NULL,
  nome_faixa VARCHAR(255) NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_faixa_loja FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categorias_produtos_lojas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  loja_id INT NOT NULL,
  nome_categoria VARCHAR(100) NOT NULL,
  descricao TEXT NULL,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categorias_produtos_lojas (loja_id, nome_categoria, descricao)
VALUES
(1, 'Lanches', 'Hambúrgueres e sanduíches'),
(1, 'Bebidas', 'Refrigerantes, sucos e águas'),
(1, 'Sobremesas', 'Doces, tortas e sorvetes');


select * from  administradores;
DESCRIBE categorias_produtos_lojas;

SHOW TABLES FROM delivery_lanches;

ALTER TABLE categorias_produtos_lojas 
ADD COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1;


ALTER TABLE produtos
DROP FOREIGN KEY fk_produto_categoria,
ADD CONSTRAINT fk_produto_categoria
FOREIGN KEY (categoria_id) REFERENCES categorias_produtos_lojas(id);

SELECT CONSTRAINT_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'delivery_lanches' 
  AND TABLE_NAME = 'produtos'
  AND COLUMN_NAME = 'categoria_id';
  
  ALTER TABLE produtos DROP FOREIGN KEY fk_produtos_categoria_id;

SELECT CONSTRAINT_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'delivery_lanches' 
  AND TABLE_NAME = 'produtos'
  AND COLUMN_NAME = 'categoria_id';
ALTER TABLE produtos
ADD CONSTRAINT fk_produtos_categoria_loja
FOREIGN KEY (categoria_id) REFERENCES categorias_produtos_lojas(id);


-- Remove as FKs antigas
ALTER TABLE produtos 
DROP FOREIGN KEY fk_produto_categoria,
DROP FOREIGN KEY fk_produtos_categoria_loja;

-- Cria a nova FK apontando para categorias_produtos_lojas
ALTER TABLE produtos
ADD CONSTRAINT fk_produto_categoria_loja
FOREIGN KEY (categoria_id) REFERENCES categorias_produtos_lojas(id);

ALTER TABLE produtos
ADD COLUMN imagem LONGBLOB NULL AFTER descricao;


ALTER TABLE promocoes
ADD imagem_tipo LONGBLOB NULL;


CREATE TABLE horarios_loja (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loja_id INT NOT NULL,
    dia_semana ENUM('Segunda','Terca','Quarta','Quinta','Sexta','Sabado','Domingo') NOT NULL,
    hora_abertura TIME NOT NULL,
    hora_fechamento TIME NOT NULL,
    status ENUM('aberto','fechado') NOT NULL DEFAULT 'aberto',
    FOREIGN KEY (loja_id) REFERENCES lojas(id)
);


ALTER TABLE formas_pagamento
ADD COLUMN responsavel_nome VARCHAR(150) NULL AFTER chave_pix,
ADD COLUMN responsavel_conta VARCHAR(100) NULL AFTER responsavel_nome,
ADD COLUMN responsavel_doc VARCHAR(50) NULL AFTER responsavel_conta;


-- VIEW para o DASHBOARD (resumo consolidado)
CREATE OR REPLACE VIEW dashboard_loja AS
SELECT
  l.id AS loja_id,
  COUNT(DISTINCT c.id) AS total_clientes,
  COUNT(DISTINCT p.id) AS total_produtos,
  SUM(CASE WHEN pd.status='entregue' THEN (pd.total + pd.taxa_entrega) ELSE 0 END) AS faturamento_total,
  SUM(CASE WHEN pd.status='entregue' THEN 1 ELSE 0 END) AS entregues,
  SUM(CASE WHEN pd.status IN ('pendente','aceito','em_entrega') THEN 1 ELSE 0 END) AS andamento,
  SUM(CASE WHEN pd.status='cancelado' THEN 1 ELSE 0 END) AS cancelados
FROM lojas l
LEFT JOIN clientes c ON c.loja_idadministradoresadministradores = l.id
LEFT JOIN produtos p ON p.loja_id = l.id
LEFT JOIN pedidos pd ON pd.loja_id = l.id
GROUP BY l.id;


SELECT TABLE_NAME, COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('clientes','produtos','pedidos','formas_pagamento','lojas')
  AND (COLUMN_NAME LIKE '%loja%' OR COLUMN_NAME LIKE '%id_loja%' OR COLUMN_NAME LIKE '%idloja%' OR COLUMN_NAME LIKE '%loja_id%' OR COLUMN_NAME LIKE '%store%')
ORDER BY TABLE_NAME, COLUMN_NAME;


ALTER TABLE clientes  ADD COLUMN loja_id INT(11) NULL AFTER id;
ALTER TABLE produtos  ADD COLUMN loja_id INT(11) NULL AFTER id;
ALTER TABLE pedidos   ADD COLUMN loja_id INT(11) NULL AFTER id;
ALTER TABLE formas_pagamento ADD COLUMN loja_id INT(11) NULL AFTER ativo;

CREATE OR REPLACE VIEW dashboard_loja AS
SELECT
  l.id AS loja_id,
  -- totais (clientes / produtos)
  (SELECT COUNT(*) FROM clientes c WHERE c.loja_id = l.id) AS total_clientes,
  (SELECT COUNT(*) FROM produtos p WHERE p.loja_id = l.id) AS total_produtos,
  -- faturamento somando total + taxa somente de pedidos entregues
  (SELECT COALESCE(SUM(pd.total + pd.taxa_entrega),0) FROM pedidos pd WHERE pd.loja_id = l.id AND pd.status = 'entregue') AS faturamento_total,
  (SELECT COUNT(*) FROM pedidos pd WHERE pd.loja_id = l.id AND pd.status = 'entregue') AS entregues,
  (SELECT COUNT(*) FROM pedidos pd WHERE pd.loja_id = l.id AND pd.status IN ('pendente','aceito','em_entrega')) AS andamento,
  (SELECT COUNT(*) FROM pedidos pd WHERE pd.loja_id = l.id AND pd.status = 'cancelado') AS cancelados
FROM lojas l;


select * from administradores;


ALTER TABLE administradores ADD COLUMN slug VARCHAR(150) UNIQUE;


ALTER TABLE a ADD COLUMN slug VARCHAR(255) UNIQUE;
UPDATE administradores SET slug = LOWER(REPLACE(nome, ' ', '-'));

-- 1️⃣ Remove espaços extras nos nomes
UPDATE lojas
SET nome = TRIM(nome);

-- 2️⃣ Gera o slug (nome em minúsculo e com traços) onde estiver vazio ou nulo
UPDATE lojas
SET slug = LOWER(REPLACE(REPLACE(REPLACE(nome, ' ', '-'), '–', '-'), '--', '-'))
WHERE slug IS NULL OR slug = '';

-- 3️⃣ Remove caracteres especiais do slug (mantém só letras, números e traços)
UPDATE lojas
SET slug = REGEXP_REPLACE(slug, '[^a-z0-9-]', '')
WHERE slug IS NOT NULL;

-- 4️⃣ Garante que não existam slugs duplicados (adiciona número no final se houver conflito)
-- ⚠️ Essa parte pode variar conforme o SGBD (MySQL 8.0+ é necessário)
WITH ranked AS (
  SELECT id, slug,
         ROW_NUMBER() OVER (PARTITION BY slug ORDER BY id) AS rnk
  FROM lojas
)
UPDATE lojas
JOIN ranked USING (id)
SET lojas.slug = CONCAT(lojas.slug, '-', rnk)
WHERE rnk > 1;


SET SQL_SAFE_UPDATES = 0;

UPDATE lojas
SET slug = CONCAT(slug, '-', id)
WHERE slug IN (
  SELECT s.slug
  FROM (SELECT slug FROM lojas GROUP BY slug HAVING COUNT(*) > 1) AS s
);
;

-- 1. Garante nomes limpos
SET SQL_SAFE_UPDATES = 0;
UPDATE lojas SET nome = TRIM(nome);

-- 2. Cria slugs onde não existir
UPDATE lojas
SET slug = LOWER(REPLACE(REPLACE(REPLACE(nome, ' ', '-'), '–', '-'), '--', '-'))
WHERE slug IS NULL OR slug = '';

-- 3. Remove caracteres especiais
UPDATE lojas
SET slug = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(slug, 'ç', 'c'), 'ã', 'a'), 'â', 'a'), 'é', 'e'), 'ê', 'e'), 'í', 'i'), 'ó', 'o');

-- 4. Garante unicidade sem erro (adiciona id)
UPDATE lojas
SET slug = CONCAT(slug, '-', id)
WHERE slug IN (
  SELECT slug FROM (
    SELECT slug FROM lojas GROUP BY slug HAVING COUNT(*) > 1
  ) AS sub
);


ALTER TABLE clientes ADD COLUMN loja_id INT(11) NOT NULL AFTER id;

ALTER TABLE clientes
ADD CONSTRAINT fk_clientes_lojas
FOREIGN KEY (loja_id) REFERENCES lojas(id)
ON DELETE CASCADE;
use delivery_lanches;

SELECT * FROM lojas ;

CREATE TABLE IF NOT EXISTS links_lojas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  loja_id INT NOT NULL,
  nome_link VARCHAR(150) NOT NULL,           -- Ex: 'Página inicial', 'Cadastro de cliente'
  slug VARCHAR(150) NOT NULL,                -- Ex: 'pizzaria-italia', 'lanchonete-do-ze'
  url_completa VARCHAR(255) GENERATED ALWAYS AS (CONCAT('/loja/', slug)) STORED, 
  tipo ENUM('publico', 'interno') DEFAULT 'publico',  -- Define se é um link visível pro cliente
  ativo TINYINT(1) DEFAULT 1,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_links_lojas FOREIGN KEY (loja_id) REFERENCES lojas(id) ON DELETE CASCADE,
  UNIQUE KEY (slug, loja_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


SELECT * FROM lojas;

UPDATE lojas
SET slug = LOWER(REPLACE(nome, ' ', '-'))
WHERE slug IS NULL OR slug = '';
ALTER TABLE lojas
ADD UNIQUE KEY slug_unique (slug);

SHOW COLUMNS FROM lojas LIKE 'slug';


ALTER TABLE lojas
ADD COLUMN slug VARCHAR(255) UNIQUE AFTER nome;

UPDATE lojas
SET slug = LOWER(REPLACE(TRIM(nome), ' ', '-'))
WHERE slug IS NULL OR slug = '';

SET @contador = 0;
UPDATE lojas
SET slug = CONCAT(slug, '-', id)
WHERE slug IN (
  SELECT slug FROM (
    SELECT slug FROM lojas GROUP BY slug HAVING COUNT(*) > 1
  ) AS duplicados
);

ALTER TABLE lojas
ADD CONSTRAINT unique_slug UNIQUE (slug);

SELECT * FROM promocoes;
