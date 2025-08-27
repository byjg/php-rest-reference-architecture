-- Migration: Add status field to clientes table
-- Description: Adds a status field to track client state
-- Author: Felipe Correa
-- Date: 2025-08-26

-- Add status field with proper constraints and comments
ALTER TABLE clientes
    ADD COLUMN status ENUM('ativo', 'inativo', 'pendente', 'bloqueado')
    DEFAULT 'ativo'
    COMMENT 'Status do cliente no sistema';

-- Add index for better performance on status queries
CREATE INDEX idx_clientes_status ON clientes(status);
