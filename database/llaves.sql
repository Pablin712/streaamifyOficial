ALTER TABLE mantenimientos DROP FOREIGN KEY mantenimientos_idcue_foreign;
ALTER TABLE costos DROP FOREIGN KEY costos_idcue_foreign;
ALTER TABLE perfiles DROP FOREIGN KEY perfiles_idcue_foreign;
ALTER TABLE detalles_venta DROP FOREIGN KEY detalles_venta_idper_foreign;

ALTER TABLE mantenimientos MODIFY COLUMN idcue VARCHAR(50);
ALTER TABLE costos MODIFY COLUMN idcue VARCHAR(50);
ALTER TABLE perfiles MODIFY COLUMN idcue VARCHAR(50);
ALTER TABLE detalles_venta MODIFY COLUMN idper VARCHAR(50);

-- Volver a crear las llaves foráneas
ALTER TABLE mantenimientos ADD CONSTRAINT mantenimientos_idcue_foreign FOREIGN KEY (idcue) REFERENCES cuentas(idcue) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE costos ADD CONSTRAINT costos_idcue_foreign FOREIGN KEY (idcue) REFERENCES cuentas(idcue) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE perfiles ADD CONSTRAINT perfiles_idcue_foreign FOREIGN KEY (idcue) REFERENCES cuentas(idcue) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE detalles_venta ADD CONSTRAINT detalles_venta_idper_foreign FOREIGN KEY (idper) REFERENCES perfiles(idper) ON DELETE CASCADE ON UPDATE CASCADE;