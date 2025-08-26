-- db/migrations/down/00002-rollback-table-clientes.sql
-- Migration: Rollback create table clientes
-- Description: Removes the clientes table
-- Author: Felipe Correa
-- Date: 2024-12-25
DROP TABLE IF EXISTS clientes;