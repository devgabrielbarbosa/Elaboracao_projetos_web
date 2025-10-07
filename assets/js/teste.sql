-- Criar schema
DROP DATABASE IF EXISTS delivery_lanches;
CREATE DATABASE IF NOT EXISTS delivery_lanches DEFAULT CHARACTER SET utf8mb4;
USE delivery_lanches;

-- -----------------------------------------------------
-- Table administradores
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS administradores (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nome VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  nivel ENUM('admin', 'gerente', 'operador') NULL DEFAULT 'operador',
  data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  foto VARCHAR(255) NULL DEFAULT NULL,
  loja_id INT(11) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX email (email ASC)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table clientes
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nome VARCHAR(150) NOT NULL,
  cpf CHAR(11) NULL DEFAULT NULL,
  telefone VARCHAR(20) NOT NULL,
  email VARCHAR(150) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  foto_perfil VARCHAR(255) NULL DEFAULT NULL,
  data_nascimento DATE NULL DEFAULT NULL,
  status ENUM('ativo', 'inativo') NULL DEFAULT 'ativo',
  email_verificado TINYINT(1) NULL DEFAULT 0,
  data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  loja_id INT(11) NOT NULL DEFAULT 1,
  cep VARCHAR(10) NULL DEFAULT NULL,
  logradouro VARCHAR(255) NULL DEFAULT NULL,
  numero VARCHAR(20) NULL DEFAULT NULL,
  complemento VARCHAR(100) NULL DEFAULT NULL,
  bairro VARCHAR(100) NULL DEFAULT NULL,
  cidade VARCHAR(100) NULL DEFAULT NULL,
  estado VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX cpf (cpf ASC),
  UNIQUE INDEX email (email ASC)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table entregadores
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS entregadores (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nome VARCHAR(150) NOT NULL,
  telefone VARCHAR(20) NULL DEFAULT NULL,
  login VARCHAR(100) NULL DEFAULT NULL,
  senha VARCHAR(255) NULL DEFAULT NULL,
  status ENUM('disponivel', 'em_entrega', 'indisponivel') NULL DEFAULT 'disponivel',
  veiculo VARCHAR(50) NULL DEFAULT NULL,
  localizacao_atual VARCHAR(255) NULL DEFAULT NULL,
  data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE INDEX login (login ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table endereco_entrega
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS endereco_entrega (
  id INT(11) NOT NULL AUTO_INCREMENT,
  cliente_id INT(11) NOT NULL,
  cep CHAR(8) NOT NULL,
  cidade VARCHAR(100) NOT NULL,
  estado CHAR(2) NOT NULL,
  logradouro VARCHAR(150) NOT NULL,
  numero VARCHAR(10) NOT NULL,
  complemento VARCHAR(100) NULL DEFAULT NULL,
  bairro VARCHAR(100) NOT NULL,
  principal TINYINT(1) NULL DEFAULT 1,
  PRIMARY KEY (id),
  INDEX cliente_id (cliente_id ASC),
  CONSTRAINT endereco_entrega_ibfk_1 FOREIGN KEY (cliente_id) REFERENCES clientes (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table pedidos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS pedidos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  cliente_id INT(11) NOT NULL,
  entregador_id INT(11) NULL DEFAULT NULL,
  status ENUM('pendente', 'aceito', 'em_entrega', 'entregue', 'cancelado') NULL DEFAULT 'pendente',
  total DECIMAL(10,2) NOT NULL,
  taxa_entrega DECIMAL(10,2) NULL DEFAULT 0.00,
  endereco_id INT(11) NOT NULL,
  telefone VARCHAR(20) NOT NULL,
  localizacao VARCHAR(255) NULL DEFAULT NULL,
  metodo_pagamento ENUM('dinheiro', 'cartao', 'pix') NULL DEFAULT 'dinheiro',
  observacoes TEXT NULL DEFAULT NULL,
  data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  loja_id INT(11) NOT NULL,
  admin_id INT(11) NOT NULL,
  PRIMARY KEY (id),
  INDEX cliente_id (cliente_id ASC),
  INDEX entregador_id (entregador_id ASC),
  INDEX endereco_id (endereco_id ASC),
  CONSTRAINT pedidos_ibfk_1 FOREIGN KEY (cliente_id) REFERENCES clientes (id),
  CONSTRAINT pedidos_ibfk_2 FOREIGN KEY (entregador_id) REFERENCES entregadores (id),
  CONSTRAINT pedidos_ibfk_3 FOREIGN KEY (endereco_id) REFERENCES endereco_entrega (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ⚡ Segue o mesmo padrão pros outros (avaliacoes_pedido, categorias_produtos, produtos, avaliacoes_produto, configuracoes_sistema, promocoes, cupons_clientes, endereco, faixas_entrega, formas_pagamento, fotos_produto, historico_pedidos, itens_pedido, logs_sistema, lojas, notificacoes, pagamentos, taxa_entrega).


-- -----------------------------------------------------
-- Table `delivery_lanches`.`avaliacoes_pedido`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`avaliacoes_pedido` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` INT(11) NOT NULL,
  `nota_entrega` INT(11) NULL DEFAULT NULL,
  `comentario` TEXT NULL DEFAULT NULL,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `pedido_id` (`pedido_id` ASC) VISIBLE,
  CONSTRAINT `avaliacoes_pedido_ibfk_1`
    FOREIGN KEY (`pedido_id`)
    REFERENCES `delivery_lanches`.`pedidos` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

-- (segue para todas as tabelas igual ao que você mandou, só troquei o `CURRENT_TIMESTAMP()` por `CURRENT_TIMESTAMP` em todas)



-- -----------------------------------------------------
-- Table `delivery_lanches`.`categorias_produtos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`categorias_produtos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  `ativo` TINYINT(1) NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`produtos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`produtos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  `categoria` VARCHAR(100) NULL DEFAULT NULL,
  `preco` DECIMAL(10,2) NOT NULL,
  `imagem` VARCHAR(255) NULL DEFAULT NULL,
  `estoque` INT(11) NULL DEFAULT 0,
  `categoria_id` INT(11) NULL DEFAULT NULL,
  `disponivel` TINYINT(1) NULL DEFAULT 1,
  `imagem_principalfotos_produto` VARCHAR(255) NULL DEFAULT NULL,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `loja_id` INT(11) NOT NULL,
  `admin_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `categoria_id` (`categoria_id`),
  CONSTRAINT `produtos_ibfk_1`
    FOREIGN KEY (`categoria_id`)
    REFERENCES `delivery_lanches`.`categorias_produtos` (`id`)
) ENGINE = InnoDB
AUTO_INCREMENT = 11
DEFAULT CHARACTER SET = utf8mb4;

ALTER TABLE `delivery_lanches`.`lojas`
MODIFY COLUMN `logo` LONGBLOB NOT NULL;
select * from fotos_produto;
-- -----------------------------------------------------
-- Table `delivery_lanches`.`avaliacoes_produto`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`avaliacoes_produto` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `produto_id` INT(11) NOT NULL,
  `cliente_id` INT(11) NOT NULL,
  `nota` INT(11) NOT NULL,
  `comentario` TEXT NULL DEFAULT NULL,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `produto_id` (`produto_id`),
  INDEX `cliente_id` (`cliente_id`),
  CONSTRAINT `avaliacoes_produto_ibfk_1`
    FOREIGN KEY (`produto_id`)
    REFERENCES `delivery_lanches`.`produtos` (`id`),
  CONSTRAINT `avaliacoes_produto_ibfk_2`
    FOREIGN KEY (`cliente_id`)
    REFERENCES `delivery_lanches`.`clientes` (`id`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`configuracoes_sistema`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`configuracoes_sistema` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `taxa_entrega_padrao` DECIMAL(10,2) NULL DEFAULT 0.00,
  `valor_minimo_pedido` DECIMAL(10,2) NULL DEFAULT 0.00,
  `horario_abertura` TIME NULL DEFAULT '08:00:00',
  `horario_fechamento` TIME NULL DEFAULT '22:00:00',
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

ALTER TABLE fotos_produto
DROP FOREIGN KEY fotos_produto_ibfk_1,
ADD CONSTRAINT fotos_produto_ibfk_1
FOREIGN KEY (produto_id) REFERENCES produtos(id)
ON DELETE CASCADE;
-- -----------------------------------------------------
-- Table `delivery_lanches`.`promocoes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`promocoes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `admin_id` INT(11) NOT NULL,
  `codigo` VARCHAR(50) NOT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  `desconto` DECIMAL(5,2) NOT NULL,
  `ativo` TINYINT(1) NULL DEFAULT 1,
  `data_inicio` DATE NULL DEFAULT NULL,
  `data_fim` DATE NULL DEFAULT NULL,
  `imagem` VARCHAR(255) NOT NULL,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `loja_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `codigo` (`codigo`)
) ENGINE = InnoDB
AUTO_INCREMENT = 8
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`cupons_clientes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`cupons_clientes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` INT(11) NOT NULL,
  `promocao_id` INT(11) NOT NULL,
  `usado` TINYINT(1) NULL DEFAULT 0,
  `data_uso` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `cliente_id` (`cliente_id`),
  INDEX `promocao_id` (`promocao_id`),
  CONSTRAINT `cupons_clientes_ibfk_1`
    FOREIGN KEY (`cliente_id`)
    REFERENCES `delivery_lanches`.`clientes` (`id`),
  CONSTRAINT `cupons_clientes_ibfk_2`
    FOREIGN KEY (`promocao_id`)
    REFERENCES `delivery_lanches`.`promocoes` (`id`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`endereco`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`endereco` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` INT(11) NOT NULL,
  `logradouro` VARCHAR(255) NOT NULL,
  `numero` VARCHAR(20) NOT NULL,
  `complemento` VARCHAR(255) NULL DEFAULT NULL,
  `bairro` VARCHAR(100) NOT NULL,
  `cidade` VARCHAR(100) NOT NULL,
  `estado` VARCHAR(50) NOT NULL,
  `cep` VARCHAR(20) NULL DEFAULT NULL,
  `principal` TINYINT(1) NULL DEFAULT 0,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `cliente_id` (`cliente_id`),
  CONSTRAINT `endereco_ibfk_1`
    FOREIGN KEY (`cliente_id`)
    REFERENCES `delivery_lanches`.`clientes` (`id`)
    ON DELETE CASCADE
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`faixas_entrega`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`faixas_entrega` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome_faixa` VARCHAR(255) NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `loja_id` INT(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB
AUTO_INCREMENT = 10
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`formas_pagamento`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`formas_pagamento` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(50) NOT NULL,
  `tipo` ENUM('dinheiro', 'cartao', 'pix') NOT NULL,
  `chave_pix` VARCHAR(255) NULL DEFAULT NULL,
  `responsavel_nome` VARCHAR(100) NULL DEFAULT NULL,
  `responsavel_conta` VARCHAR(100) NULL DEFAULT NULL,
  `responsavel_doc` VARCHAR(20) NULL DEFAULT NULL,
  `ativo` TINYINT(1) NULL DEFAULT 1,
  `admin_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB
AUTO_INCREMENT = 15
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`fotos_produto`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`fotos_produto` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `produto_id` INT(11) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `produto_id` (`produto_id`),
  CONSTRAINT `fotos_produto_ibfk_1`
    FOREIGN KEY (`produto_id`)
    REFERENCES `delivery_lanches`.`produtos` (`id`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;

---------------
--- -----------------------------------------------------
-- Table `delivery_lanches`.`historico_pedidos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`historico_pedidos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` INT(11) NOT NULL,
  `status` ENUM('pendente', 'aceito', 'em_entrega', 'entregue', 'cancelado') DEFAULT NULL,
  `alterado_por` ENUM('cliente', 'admin', 'sistema') DEFAULT 'sistema',
  `data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `pedido_id` (`pedido_id` ASC),
  CONSTRAINT `historico_pedidos_ibfk_1`
    FOREIGN KEY (`pedido_id`)
    REFERENCES `delivery_lanches`.`pedidos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `delivery_lanches`.`itens_pedido`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`itens_pedido` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` INT(11) NOT NULL,
  `produto_id` INT(11) NOT NULL,
  `quantidade` INT(11) NOT NULL,
  `preco_unitario` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `pedido_id` (`pedido_id` ASC),
  INDEX `produto_id` (`produto_id` ASC),
  CONSTRAINT `itens_pedido_ibfk_1`
    FOREIGN KEY (`pedido_id`)
    REFERENCES `delivery_lanches`.`pedidos` (`id`),
  CONSTRAINT `itens_pedido_ibfk_2`
    FOREIGN KEY (`produto_id`)
    REFERENCES `delivery_lanches`.`produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `delivery_lanches`.`logs_sistema`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`logs_sistema` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_tipo` ENUM('cliente', 'admin', 'entregador') DEFAULT NULL,
  `usuario_id` INT(11) DEFAULT NULL,
  `acao` TEXT,
  `data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `delivery_lanches`.`lojas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`lojas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `endereco` VARCHAR(255) DEFAULT NULL,
  `telefone` VARCHAR(50) DEFAULT NULL,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('aberto', 'fechado') DEFAULT 'fechado',
  `horarios` TEXT,
  `logo` VARCHAR(255) DEFAULT NULL,
  `cidade` VARCHAR(100) DEFAULT NULL,
  `estado` VARCHAR(50) DEFAULT NULL,
  `mensagem` VARCHAR(255) DEFAULT NULL,
  `bairro` VARCHAR(150) DEFAULT NULL,
  `logradouro` VARCHAR(150) DEFAULT NULL,
  `rua` VARCHAR(150) DEFAULT NULL,
  `cep` VARCHAR(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `delivery_lanches`.`notificacoes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`notificacoes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_tipo` ENUM('cliente', 'admin', 'entregador') DEFAULT NULL,
  `usuario_id` INT(11) DEFAULT NULL,
  `mensagem` TEXT,
  `lida` TINYINT(1) DEFAULT 0,
  `data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `delivery_lanches`.`pagamentos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`pagamentos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` INT(11) NOT NULL,
  `forma_pagamento_id` INT(11) NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pendente', 'aprovado', 'cancelado') DEFAULT 'pendente',
  `comprovante_url` VARCHAR(255) DEFAULT NULL,
  `data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `pedido_id` (`pedido_id` ASC),
  INDEX `forma_pagamento_id` (`forma_pagamento_id` ASC),
  CONSTRAINT `pagamentos_ibfk_1`
    FOREIGN KEY (`pedido_id`)
    REFERENCES `delivery_lanches`.`pedidos` (`id`),
  CONSTRAINT `pagamentos_ibfk_2`
    FOREIGN KEY (`forma_pagamento_id`)
    REFERENCES `delivery_lanches`.`formas_pagamento` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table `delivery_lanches`.`taxa_entrega`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`taxa_entrega` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `valor` DECIMAL(10,2) NOT NULL,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `loja_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



SELECT * FROM lojas;

ALTER TABLE fotos_produto
DROP FOREIGN KEY fotos_produto_ibfk_1;

ALTER TABLE fotos_produto
ADD CONSTRAINT fotos_produto_ibfk_1
FOREIGN KEY (produto_id) REFERENCES produtos(id)
ON DELETE CASCADE;
