-- =========================================================
-- Base de datos NUEVA (instalación desde cero)
-- Si ya tiene la base creada, use migracion.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS parqueadero
    CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE parqueadero;

CREATE TABLE IF NOT EXISTS vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(10) NOT NULL,
    tipo VARCHAR(20) NOT NULL,
    propietario VARCHAR(100) NOT NULL,
    hora_entrada DATETIME NOT NULL,
    hora_salida DATETIME NULL,
    tarifa_tipo VARCHAR(10) NOT NULL DEFAULT 'minuto',   -- 'minuto' o 'plena'
    tarifa_valor DECIMAL(10,2) NOT NULL DEFAULT 0,
    valor_pagado DECIMAL(10,2) NOT NULL DEFAULT 0,
    cerrado TINYINT(1) NOT NULL DEFAULT 0,                -- 1 = ya incluido en un cierre de caja
    INDEX idx_placa (placa),
    INDEX idx_salida (hora_salida),
    INDEX idx_entrada (hora_entrada)
);
