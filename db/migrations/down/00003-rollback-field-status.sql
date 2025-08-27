-- Migration: Rollback add status field to clientes table
-- Description: Removes the status field from clientes table
-- Author: Felipe Correa
-- Date: 2025-08-26

-- Remove index first (safe rollback)
DROP INDEX IF EXISTS idx_clientes_status ON clientes;

-- Remove status column
ALTER TABLE clientes
DROP COLUMN IF EXISTS status;