-- ============================================================
-- Sistema de Contas a Pagar - FinControl
-- Script de criação do banco de dados
-- ============================================================

CREATE DATABASE IF NOT EXISTS u199367788_SjJHpEoZL_fincontrol
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE u199367788_SjJHpEoZL_fincontrol;

-- ============================================================
-- TABELA: tbPessoaTipo (dominio)
-- ============================================================
CREATE TABLE IF NOT EXISTS tbPessoaTipo (
  pessoa_tipo_id INT(10) NOT NULL AUTO_INCREMENT,
  descricao      VARCHAR(200) NOT NULL,
  PRIMARY KEY (pessoa_tipo_id),
  UNIQUE KEY uq_pessoa_tipo_descricao (descricao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABELA: tbPessoas (cadastro)
-- ============================================================
CREATE TABLE IF NOT EXISTS tbPessoas (
  pessoa_id      INT(11)      NOT NULL AUTO_INCREMENT,
  nome           VARCHAR(200) NOT NULL,
  cpf            VARCHAR(14)  NOT NULL,
  nascimento     DATE         NOT NULL,
  telefone       VARCHAR(20)  NOT NULL,
  pessoa_tipo_id INT(10)      NOT NULL,
  atualizado_por INT(10)      DEFAULT NULL,
  atualizado_em  DATE         DEFAULT NULL,
  PRIMARY KEY (pessoa_id),
  UNIQUE KEY uq_pessoa_cpf (cpf),
  CONSTRAINT fk_pessoa_tipo FOREIGN KEY (pessoa_tipo_id)
    REFERENCES tbPessoaTipo (pessoa_tipo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABELA: tbUsuarios (seguranca)
-- ============================================================
CREATE TABLE IF NOT EXISTS tbUsuarios (
  usuario_id     INT(10)      NOT NULL AUTO_INCREMENT,
  nome           VARCHAR(200) NOT NULL,
  login          VARCHAR(50)  NOT NULL,
  senha          VARCHAR(255) NOT NULL,
  atualizado_em  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  atualizado_por INT(10)      DEFAULT NULL,
  PRIMARY KEY (usuario_id),
  UNIQUE KEY uq_usuario_login (login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABELA: tbTipoTitulo (financeiro)
-- ============================================================
CREATE TABLE IF NOT EXISTS tbTipoTitulo (
  tipo_titulo_id INT(11)      NOT NULL AUTO_INCREMENT,
  descricao      VARCHAR(100) NOT NULL,
  PRIMARY KEY (tipo_titulo_id),
  UNIQUE KEY uq_tipo_titulo_descricao (descricao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABELA: tbContasPagar (financeiro)
-- ============================================================
CREATE TABLE IF NOT EXISTS tbContasPagar (
  conta_pagar_id  INT(11)      NOT NULL AUTO_INCREMENT,
  valor           DECIMAL(15,2) NOT NULL,
  data_vencimento DATE         NOT NULL,
  data_pagamento  DATE         DEFAULT NULL,
  tipo_titulo_id  INT(11)      NOT NULL,
  atualizado_por  INT(10)      DEFAULT NULL,
  atualizado_em   DATE         DEFAULT NULL,
  emprestimo_id   INT(10)      DEFAULT NULL,
  funcionario_id  INT(11)      DEFAULT NULL,
  cliente_id      INT(11)      DEFAULT NULL,
  viagem_id       INT(10)      DEFAULT NULL,
  fornecedor_id   INT(11)      DEFAULT NULL,
  descricao       VARCHAR(500) DEFAULT NULL,
  status          ENUM('pendente','aprovado','pago','cancelado','vencido') NOT NULL DEFAULT 'pendente',
  PRIMARY KEY (conta_pagar_id),
  CONSTRAINT fk_cp_tipo_titulo  FOREIGN KEY (tipo_titulo_id)  REFERENCES tbTipoTitulo (tipo_titulo_id),
  CONSTRAINT fk_cp_fornecedor   FOREIGN KEY (fornecedor_id)   REFERENCES tbPessoas    (pessoa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DADOS INICIAIS
-- ============================================================

-- Tipos de pessoa
INSERT INTO tbPessoaTipo (descricao) VALUES
  ('Fornecedor'),
  ('Cliente'),
  ('Funcionário'),
  ('Pessoa Física');

-- Tipos de título
INSERT INTO tbTipoTitulo (descricao) VALUES
  ('Nota Fiscal'),
  ('Boleto Bancário'),
  ('Duplicata'),
  ('Cheque'),
  ('Fatura de Cartão'),
  ('Contrato'),
  ('Recibo');

-- Usuário administrador padrão (senha: admin123)
INSERT INTO tbUsuarios (nome, login, senha) VALUES
  ('Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
