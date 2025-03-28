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

--28 de marzo 2025
-- Eliminar la clave foránea actual en la tabla cuentas
ALTER TABLE cuentas DROP FOREIGN KEY cuentas_idval_foreign;

-- Modificar el tipo de la columna idval
ALTER TABLE cuentas MODIFY COLUMN idval VARCHAR(50);
-- Volver a crear la clave foránea con actualización en cascada
ALTER TABLE cuentas 
ADD CONSTRAINT cuentas_idval_foreign 
FOREIGN KEY (idval) REFERENCES valores(idval) 
ON DELETE CASCADE 
ON UPDATE CASCADE;


-- Eliminar la clave foránea en valores
ALTER TABLE valores DROP FOREIGN KEY valores_idpro_foreign;

-- Volver a crear la clave foránea con actualización y eliminación en cascada
ALTER TABLE valores ADD CONSTRAINT valores_idpro_foreign 
FOREIGN KEY (idpro) REFERENCES proveedores(idpro) 
ON DELETE CASCADE ON UPDATE CASCADE;
