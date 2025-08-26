-- db/migrations/up/00002-create-table-clientes.sql
-- Migration: Create table clientes
-- Description: Creates a table for client management
-- Author: Felipe Correa
-- Date: 2024-12-25
CREATE TABLE clientes
(
    id            INT AUTO_INCREMENT NOT NULL PRIMARY KEY COMMENT 'Identificador único do cliente',
    nome          VARCHAR(100) NOT NULL COMMENT 'Nome completo do cliente',
    email         VARCHAR(255) NOT NULL UNIQUE COMMENT 'Email único do cliente',
    telefone      VARCHAR(20) NULL COMMENT 'Telefone de contato',
    cpf           VARCHAR(14) NULL UNIQUE COMMENT 'CPF único do cliente',
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de cadastro do cliente',

    -- Add indexes for better performance
    INDEX         idx_clientes_nome (nome),
    INDEX         idx_clientes_email (email),
    INDEX         idx_clientes_cpf (cpf),
    INDEX         idx_clientes_data_cadastro (data_cadastro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de clientes do sistema';