-- =========================================================
-- MIGRACIÓN para quien YA tiene la tabla "vehiculos" creada.
-- Su código original guardaba tarifa_tipo y tarifa_valor, pero el .sql
-- original no tenía esas columnas. Ejecute esto UNA sola vez en phpMyAdmin.
-- (Si alguna columna ya existe, MySQL avisará; ignore ese error y siga con la siguiente.)
-- =========================================================

USE parqueadero;

ALTER TABLE vehiculos ADD COLUMN tarifa_tipo VARCHAR(10) NOT NULL DEFAULT 'minuto' AFTER hora_salida;
ALTER TABLE vehiculos ADD COLUMN tarifa_valor DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER tarifa_tipo;
ALTER TABLE vehiculos ADD COLUMN cerrado TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE vehiculos ADD INDEX idx_placa (placa);
ALTER TABLE vehiculos ADD INDEX idx_salida (hora_salida);
ALTER TABLE vehiculos ADD INDEX idx_entrada (hora_entrada);
