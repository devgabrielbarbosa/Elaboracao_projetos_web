-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema delivery_lanches
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema delivery_lanches
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `delivery_lanches` DEFAULT CHARACTER SET utf8mb4 ;
USE `delivery_lanches` ;

-- -----------------------------------------------------
-- Table `delivery_lanches`.`lojas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`lojas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `cnpj` CHAR(14) NULL DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL,
  `telefone` VARCHAR(20) NULL DEFAULT NULL,
  `endereco` VARCHAR(255) NULL DEFAULT NULL,
  `cidade` VARCHAR(100) NULL DEFAULT NULL,
  `estado` CHAR(2) NULL DEFAULT NULL,
  `cep` CHAR(8) NULL DEFAULT NULL,
  `logo` LONGBLOB NULL DEFAULT NULL,
  `taxa_entrega_padrao` DECIMAL(10,2) NULL DEFAULT 0.00,
  `status` ENUM('ativa', 'inativa') NULL DEFAULT 'ativa',
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 9
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`administradores`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`administradores` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `nivel` ENUM('superadmin', 'admin', 'gerente', 'operador') NULL DEFAULT 'operador',
  `foto` LONGBLOB NULL DEFAULT NULL,
  `loja_id` INT(11) NULL DEFAULT NULL,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email` (`email` ASC) VISIBLE,
  INDEX `fk_admin_loja` (`loja_id` ASC) VISIBLE,
  CONSTRAINT `fk_admin_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 9
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`categorias_produtos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`categorias_produtos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  `ativo` TINYINT(1) NULL DEFAULT 1,
  `loja_id` INT(11) NOT NULL,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  INDEX `fk_categoria_loja` (`loja_id` ASC) VISIBLE,
  CONSTRAINT `fk_categoria_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`categorias_produtos_lojas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`categorias_produtos_lojas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `loja_id` INT(11) NOT NULL,
  `nome_categoria` VARCHAR(100) NOT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `loja_id` (`loja_id` ASC) VISIBLE,
  CONSTRAINT `categorias_produtos_lojas_ibfk_1`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 12
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`clientes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`clientes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `cpf` CHAR(11) NULL DEFAULT NULL,
  `telefone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `foto_perfil` LONGBLOB NULL DEFAULT NULL,
  `data_nascimento` DATE NULL DEFAULT NULL,
  `status` ENUM('ativo', 'inativo') NULL DEFAULT 'ativo',
  `email_verificado` TINYINT(1) NULL DEFAULT 0,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `unique_email` (`email` ASC) VISIBLE,
  UNIQUE INDEX `unique_cpf` (`cpf` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`configuracoes_sistema`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`configuracoes_sistema` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `loja_id` INT(11) NOT NULL,
  `taxa_entrega_padrao` DECIMAL(10,2) NULL DEFAULT 0.00,
  `valor_minimo_pedido` DECIMAL(10,2) NULL DEFAULT 0.00,
  `horario_abertura` TIME NULL DEFAULT '08:00:00',
  `horario_fechamento` TIME NULL DEFAULT '22:00:00',
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  INDEX `fk_config_loja` (`loja_id` ASC) VISIBLE,
  CONSTRAINT `fk_config_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


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
  `imagem` LONGBLOB NULL DEFAULT NULL,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `loja_id` INT(11) NOT NULL,
  `imagem_blob` LONGBLOB NULL DEFAULT NULL,
  `imagem_tipo` VARCHAR(50) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `unique_codigo_loja` (`codigo` ASC, `loja_id` ASC) VISIBLE,
  INDEX `fk_promocao_loja` (`loja_id` ASC) VISIBLE,
  INDEX `fk_promocao_admin` (`admin_id` ASC) VISIBLE,
  CONSTRAINT `fk_promocao_admin`
    FOREIGN KEY (`admin_id`)
    REFERENCES `delivery_lanches`.`administradores` (`id`),
  CONSTRAINT `fk_promocao_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 6
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
  `loja_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_cupom_cliente` (`cliente_id` ASC) VISIBLE,
  INDEX `fk_cupom_promocao` (`promocao_id` ASC) VISIBLE,
  INDEX `fk_cupom_loja` (`loja_id` ASC) VISIBLE,
  CONSTRAINT `fk_cupom_cliente`
    FOREIGN KEY (`cliente_id`)
    REFERENCES `delivery_lanches`.`clientes` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_cupom_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_cupom_promocao`
    FOREIGN KEY (`promocao_id`)
    REFERENCES `delivery_lanches`.`promocoes` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`endereco_cliente`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`endereco_cliente` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` INT(11) NOT NULL,
  `loja_id` INT(11) NOT NULL,
  `logradouro` VARCHAR(255) NOT NULL,
  `numero` VARCHAR(20) NOT NULL,
  `complemento` VARCHAR(100) NULL DEFAULT NULL,
  `bairro` VARCHAR(100) NOT NULL,
  `cidade` VARCHAR(100) NOT NULL,
  `estado` VARCHAR(50) NOT NULL,
  `cep` VARCHAR(20) NULL DEFAULT NULL,
  `principal` TINYINT(1) NULL DEFAULT 0,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  INDEX `fk_endereco_cliente` (`cliente_id` ASC) VISIBLE,
  INDEX `fk_endereco_loja` (`loja_id` ASC) VISIBLE,
  CONSTRAINT `fk_endereco_cliente`
    FOREIGN KEY (`cliente_id`)
    REFERENCES `delivery_lanches`.`clientes` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_endereco_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`entregadores`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`entregadores` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `telefone` VARCHAR(20) NULL DEFAULT NULL,
  `login` VARCHAR(100) NULL DEFAULT NULL,
  `senha` VARCHAR(255) NULL DEFAULT NULL,
  `status` ENUM('disponivel', 'em_entrega', 'indisponivel') NULL DEFAULT 'disponivel',
  `veiculo` VARCHAR(50) NULL DEFAULT NULL,
  `localizacao_atual` VARCHAR(255) NULL DEFAULT NULL,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `loja_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `login` (`login` ASC) VISIBLE,
  INDEX `fk_entregador_loja` (`loja_id` ASC) VISIBLE,
  CONSTRAINT `fk_entregador_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`faixas_entrega`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`faixas_entrega` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `loja_id` INT(11) NOT NULL,
  `nome_faixa` VARCHAR(255) NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  INDEX `fk_faixa_loja` (`loja_id` ASC) VISIBLE,
  CONSTRAINT `fk_faixa_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`formas_pagamento`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`formas_pagamento` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(50) NOT NULL,
  `tipo` ENUM('dinheiro', 'cartao', 'pix') NOT NULL,
  `chave_pix` VARCHAR(255) NULL DEFAULT NULL,
  `ativo` TINYINT(1) NULL DEFAULT 1,
  `loja_id` INT(11) NOT NULL,
  `admin_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_pagamento_loja` (`loja_id` ASC) VISIBLE,
  INDEX `fk_pagamento_admin` (`admin_id` ASC) VISIBLE,
  CONSTRAINT `fk_pagamento_admin`
    FOREIGN KEY (`admin_id`)
    REFERENCES `delivery_lanches`.`administradores` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_pagamento_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`produtos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`produtos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  `imagem` LONGBLOB NULL DEFAULT NULL,
  `preco` DECIMAL(10,2) NOT NULL,
  `imagem_principal` LONGBLOB NULL DEFAULT NULL,
  `estoque` INT(11) NULL DEFAULT 0,
  `disponivel` TINYINT(1) NULL DEFAULT 1,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `ativo` TINYINT(1) NULL DEFAULT 1,
  `categoria_id` INT(11) NULL DEFAULT NULL,
  `loja_id` INT(11) NOT NULL,
  `admin_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_produto_loja` (`loja_id` ASC) VISIBLE,
  INDEX `fk_produto_admin` (`admin_id` ASC) VISIBLE,
  INDEX `fk_produto_categoria_loja` (`categoria_id` ASC) VISIBLE,
  CONSTRAINT `fk_produto_admin`
    FOREIGN KEY (`admin_id`)
    REFERENCES `delivery_lanches`.`administradores` (`id`),
  CONSTRAINT `fk_produto_categoria_loja`
    FOREIGN KEY (`categoria_id`)
    REFERENCES `delivery_lanches`.`categorias_produtos_lojas` (`id`),
  CONSTRAINT `fk_produto_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 14
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`fotos_produto`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`fotos_produto` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `produto_id` INT(11) NOT NULL,
  `imagem` LONGBLOB NOT NULL,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  INDEX `fk_foto_produto` (`produto_id` ASC) VISIBLE,
  CONSTRAINT `fk_foto_produto`
    FOREIGN KEY (`produto_id`)
    REFERENCES `delivery_lanches`.`produtos` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`pedidos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`pedidos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` INT(11) NOT NULL,
  `endereco_id` INT(11) NOT NULL,
  `telefone` VARCHAR(20) NOT NULL,
  `localizacao` VARCHAR(255) NULL DEFAULT NULL,
  `metodo_pagamento` ENUM('dinheiro', 'cartao', 'pix') NULL DEFAULT 'dinheiro',
  `observacoes` TEXT NULL DEFAULT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `taxa_entrega` DECIMAL(10,2) NULL DEFAULT 0.00,
  `status` ENUM('pendente', 'aceito', 'em_entrega', 'entregue', 'cancelado') NULL DEFAULT 'pendente',
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `loja_id` INT(11) NOT NULL,
  `admin_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_pedido_cliente` (`cliente_id` ASC) VISIBLE,
  INDEX `fk_pedido_endereco` (`endereco_id` ASC) VISIBLE,
  INDEX `fk_pedido_loja` (`loja_id` ASC) VISIBLE,
  INDEX `fk_pedido_admin` (`admin_id` ASC) VISIBLE,
  CONSTRAINT `fk_pedido_admin`
    FOREIGN KEY (`admin_id`)
    REFERENCES `delivery_lanches`.`administradores` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_pedido_cliente`
    FOREIGN KEY (`cliente_id`)
    REFERENCES `delivery_lanches`.`clientes` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_pedido_endereco`
    FOREIGN KEY (`endereco_id`)
    REFERENCES `delivery_lanches`.`endereco_cliente` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_pedido_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`historico_pedidos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`historico_pedidos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` INT(11) NOT NULL,
  `status` ENUM('pendente', 'aceito', 'em_entrega', 'entregue', 'cancelado') NULL DEFAULT 'pendente',
  `alterado_por` ENUM('cliente', 'admin', 'sistema') NULL DEFAULT 'sistema',
  `data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `loja_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_historico_pedido` (`pedido_id` ASC) VISIBLE,
  INDEX `fk_historico_pedido_loja` (`loja_id` ASC) VISIBLE,
  CONSTRAINT `fk_historico_pedido`
    FOREIGN KEY (`pedido_id`)
    REFERENCES `delivery_lanches`.`pedidos` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_historico_pedido_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`itens_pedido`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`itens_pedido` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` INT(11) NOT NULL,
  `produto_id` INT(11) NOT NULL,
  `quantidade` INT(11) NOT NULL,
  `preco_unitario` DECIMAL(10,2) NOT NULL,
  `loja_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_item_pedido_pedido` (`pedido_id` ASC) VISIBLE,
  INDEX `fk_item_pedido_produto` (`produto_id` ASC) VISIBLE,
  INDEX `fk_item_pedido_loja` (`loja_id` ASC) VISIBLE,
  CONSTRAINT `fk_item_pedido_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_item_pedido_pedido`
    FOREIGN KEY (`pedido_id`)
    REFERENCES `delivery_lanches`.`pedidos` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_item_pedido_produto`
    FOREIGN KEY (`produto_id`)
    REFERENCES `delivery_lanches`.`produtos` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`logs_sistema`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`logs_sistema` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_tipo` ENUM('cliente', 'admin', 'entregador') NULL DEFAULT NULL,
  `usuario_id` INT(11) NULL DEFAULT NULL,
  `acao` TEXT NOT NULL,
  `data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `loja_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_log_loja` (`loja_id` ASC) VISIBLE,
  CONSTRAINT `fk_log_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`notificacoes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`notificacoes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_tipo` ENUM('cliente', 'admin', 'entregador') NULL DEFAULT NULL,
  `usuario_id` INT(11) NULL DEFAULT NULL,
  `mensagem` TEXT NOT NULL,
  `lida` TINYINT(1) NULL DEFAULT 0,
  `data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `loja_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_notificacao_loja` (`loja_id` ASC) VISIBLE,
  CONSTRAINT `fk_notificacao_loja`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `delivery_lanches`.`pagamentos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_lanches`.`pagamentos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` INT(11) NOT NULL,
  `forma_pagamento_id` INT(11) NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pendente', 'aprovado', 'cancelado') NULL DEFAULT 'pendente',
  `comprovante` LONGBLOB NULL DEFAULT NULL,
  `data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `loja_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_pagamento_pedido_pg` (`pedido_id` ASC) VISIBLE,
  INDEX `fk_pagamento_forma_pg` (`forma_pagamento_id` ASC) VISIBLE,
  INDEX `fk_pagamento_loja_pg` (`loja_id` ASC) VISIBLE,
  CONSTRAINT `fk_pagamento_forma_pg`
    FOREIGN KEY (`forma_pagamento_id`)
    REFERENCES `delivery_lanches`.`formas_pagamento` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_pagamento_loja_pg`
    FOREIGN KEY (`loja_id`)
    REFERENCES `delivery_lanches`.`lojas` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_pagamento_pedido_pg`
    FOREIGN KEY (`pedido_id`)
    REFERENCES `delivery_lanches`.`pedidos` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;




CREATE TABLE IF NOT EXISTS `promocao_produtos` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `promocao_id` INT(11) NOT NULL,
    `produto_id` INT(11) NOT NULL,
    `preco_original` DECIMAL(10,2) NOT NULL,
    `preco_com_desconto` DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_promocao` (`promocao_id`),
    INDEX `idx_produto` (`produto_id`),
    CONSTRAINT `fk_promocao_produtos_promocao`
        FOREIGN KEY (`promocao_id`) REFERENCES `promocoes`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_promocao_produtos_produto`
        FOREIGN KEY (`produto_id`) REFERENCES `produtos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 


select * From promocoes;