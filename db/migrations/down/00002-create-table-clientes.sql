-- db/migrations/down/00002-rollback-table-clientes.sql
-- Migration: Rollback create table clientes
-- Description: Removes the clientes table
-- Author: Felipe Correa
-- Date: 2025-08-26
DROP TABLE IF EXISTS clientes;