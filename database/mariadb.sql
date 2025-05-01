-- Eliminar triggers para la tabla detalles_venta
DROP TRIGGER IF EXISTS trg_actualizar_total_venta_insert;
DROP TRIGGER IF EXISTS trg_actualizar_total_venta_update;
DROP TRIGGER IF EXISTS trg_actualizar_total_venta_delete;

-- Eliminar el trigger para la tabla ventas
DROP TRIGGER IF EXISTS trg_generar_idventa;

-- Eliminar el trigger para la tabla cuentas
DROP TRIGGER IF EXISTS insertar_perfiles;
DROP FUNCTION IF EXISTS calcular_total_pagado_mes;
DROP VIEW IF EXISTS view_usuarios_activos;
DROP VIEW IF EXISTS view_clientes_usuarios;

CREATE TABLE secuencia_factura (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


DELIMITER $$

CREATE TRIGGER trg_generar_idventa
BEFORE INSERT ON ventas
FOR EACH ROW
BEGIN
    DECLARE secuencia BIGINT;
    DECLARE establecimiento VARCHAR(3) DEFAULT '001';  -- Número de establecimiento
    DECLARE facturero VARCHAR(3) DEFAULT '001';        -- Número de facturero

    -- Insertar un nuevo registro en la tabla secuencia_factura y obtener el ID generado
    INSERT INTO secuencia_factura () VALUES ();
    SET secuencia = LAST_INSERT_ID();

    -- Generar el ID de venta con el formato: establecimiento-facturero-secuencia de 9 dígitos
    SET NEW.idven = CONCAT(establecimiento, '-', facturero, '-', LPAD(secuencia, 9, '0'));

END$$

DELIMITER ;


DELIMITER $$

CREATE TRIGGER trg_actualizar_total_venta_insert
AFTER INSERT ON detalles_venta
FOR EACH ROW
BEGIN
    -- Calcula el total de la venta actual
    UPDATE ventas
    SET totalpagoven = (
        SELECT COALESCE(SUM(montodet), 0)
        FROM detalles_venta
        WHERE idven = NEW.idven
    )
    WHERE idven = NEW.idven;
END $$

DELIMITER ;
DELIMITER $$

CREATE TRIGGER trg_actualizar_total_venta_update
AFTER UPDATE ON detalles_venta
FOR EACH ROW
BEGIN
    -- Calcula el total de la venta actual
    UPDATE ventas
    SET totalpagoven = (
        SELECT COALESCE(SUM(montodet), 0)
        FROM detalles_venta
        WHERE idven = NEW.idven
    )
    WHERE idven = NEW.idven;
END $$

DELIMITER ;
DELIMITER $$

CREATE TRIGGER trg_actualizar_total_venta_delete
AFTER DELETE ON detalles_venta
FOR EACH ROW
BEGIN
    -- Calcula el total de la venta actual
    UPDATE ventas
    SET totalpagoven = (
        SELECT COALESCE(SUM(montodet), 0)
        FROM detalles_venta
        WHERE idven = OLD.idven
    )
    WHERE idven = OLD.idven;
END $$

DELIMITER ;



DELIMITER $$

CREATE TRIGGER trg_generar_idventa
BEFORE INSERT ON ventas
FOR EACH ROW
BEGIN
    DECLARE num_venta_dia INT;

    -- Verificar el número máximo de ventas del día actual en la tabla ventas_diarias
    SELECT COALESCE(MAX(numero_venta), 0)
    INTO num_venta_dia
    FROM ventas_diarias
    WHERE fecha = CURRENT_DATE;

    -- Incrementar el número de venta del día actual
    SET num_venta_dia = num_venta_dia + 1;

    -- Actualizar o insertar en ventas_diarias
    IF EXISTS (SELECT 1 FROM ventas_diarias WHERE fecha = CURRENT_DATE) THEN
        -- Actualizar el número de venta si ya existe la fecha
        UPDATE ventas_diarias
        SET numero_venta = num_venta_dia
        WHERE fecha = CURRENT_DATE;
    ELSE
        -- Insertar nuevo registro si no existe la fecha
        INSERT INTO ventas_diarias (fecha, numero_venta)
        VALUES (CURRENT_DATE, num_venta_dia);
    END IF;

    -- Generar el ID de venta en el formato deseado: FAC + número + fecha
    SET NEW.idven = CONCAT('FAC', LPAD(num_venta_dia, 3, '0'), '-', DATE_FORMAT(CURRENT_DATE, '%d%m%Y'));
END$$

DELIMITER ;



-- Crear el trigger asociado
-- Crear la función insertar_perfiles
DROP TRIGGER IF EXISTS insertar_perfiles;
DELIMITER $$

CREATE TRIGGER insertar_perfiles
AFTER INSERT ON cuentas
FOR EACH ROW
BEGIN
    -- Insertar perfiles para Netflix
    IF NEW.idcue LIKE 'NETFLIX%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
        (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, '1000'),
        (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, '5555'),
        (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, '8833'),
        (CONCAT(NEW.idcue, '.4'), NEW.idcue, 4, '6622'),
        (CONCAT(NEW.idcue, '.5'), NEW.idcue, 5, '9000');
    -- Insertar perfiles para Disney
    ELSEIF NEW.idcue LIKE 'DISNEY%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
        (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, '1000'),
        (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, '5555'),
        (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, '8833'),
        (CONCAT(NEW.idcue, '.4'), NEW.idcue, 4, '6622'),
        (CONCAT(NEW.idcue, '.5'), NEW.idcue, 5, '9000'),
        (CONCAT(NEW.idcue, '.6'), NEW.idcue, 6, '2012'),
        (CONCAT(NEW.idcue, '.7'), NEW.idcue, 7, '2000');
    -- Insertar perfiles para Prime Video
    ELSEIF NEW.idcue LIKE 'PRIME%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
        (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, '10000'),
        (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, '55555'),
        (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, '88333'),
        (CONCAT(NEW.idcue, '.4'), NEW.idcue, 4, '66222'),
        (CONCAT(NEW.idcue, '.5'), NEW.idcue, 5, '90000'),
        (CONCAT(NEW.idcue, '.6'), NEW.idcue, 6, '20122');
    -- Insertar perfiles para Max
    ELSEIF NEW.idcue LIKE 'MAX%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
        (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, '1000'),
        (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, '5555'),
        (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, '8833'),
        (CONCAT(NEW.idcue, '.4'), NEW.idcue, 4, '6622'),
        (CONCAT(NEW.idcue, '.5'), NEW.idcue, 5, '9000');
    -- Insertar perfiles para Paramount
    ELSEIF NEW.idcue LIKE 'PARAMOUNT%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
        (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, '1000'),
        (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, '5555'),
        (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, '8833'),
        (CONCAT(NEW.idcue, '.4'), NEW.idcue, 4, '6622'),
        (CONCAT(NEW.idcue, '.5'), NEW.idcue, 5, '9000'),
        (CONCAT(NEW.idcue, '.6'), NEW.idcue, 6, '2012');
    -- Insertar perfiles para Spotify
    ELSEIF NEW.idcue LIKE 'SPOTIFY%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
        (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, 'owner'),
        (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, 'invit'),
        (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, 'invit'),
        (CONCAT(NEW.idcue, '.4'), NEW.idcue, 4, 'invit'),
        (CONCAT(NEW.idcue, '.5'), NEW.idcue, 5, 'invit'),
        (CONCAT(NEW.idcue, '.6'), NEW.idcue, 6, 'invit');
    -- Insertar perfiles para MAGIS
    ELSEIF NEW.idcue LIKE 'MAGIS%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
        (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, ''),
        (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, ''),
        (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, '');
    -- Insertar perfiles para Crunchyroll
    ELSEIF NEW.idcue LIKE 'CRUNCHY%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
        (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, ''),
        (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, ''),
        (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, ''),
        (CONCAT(NEW.idcue, '.4'), NEW.idcue, 4, ''),
        (CONCAT(NEW.idcue, '.5'), NEW.idcue, 5, '');
    -- Insertar perfiles para IND
    ELSEIF NEW.idcue LIKE 'IND%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
        (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, 'aparte');
    -- Insertar perfiles para IND
    ELSEIF NEW.idcue LIKE 'COM%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
        (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, 'Cmplta');
    ELSE
        -- Insertar 3 perfiles predeterminados si no corresponde a ningún tipo conocido
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
        (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, '1111'),
        (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, '2222'),
        (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, '3333');
    END IF;
END$$

DELIMITER ;


-- Vista de Usuarios Activos
CREATE VIEW view_usuarios_activos AS
SELECT 
    v.idcli,
    cl.nombrecli AS nombre_cliente,
    dv.idven,
    dv.iddet,
    p.idcue,  -- Relacionamos el perfil con la cuenta a través de idcue
    p.numeroper AS perfil,  -- Número de perfil desde la tabla perfiles
    dv.fechavendet AS fecha_vencimiento
FROM 
    detalles_venta dv
    INNER JOIN ventas v ON dv.idven = v.idven  -- Conectamos detalles_venta con ventas
    INNER JOIN clientes cl ON v.idcli = cl.idcli  -- Conectamos ventas con clientes
    INNER JOIN perfiles p ON dv.idper = p.idper  -- Conectamos detalles_venta con perfiles
    INNER JOIN cuentas c ON p.idcue = c.idcue  -- Conectamos perfiles con cuentas
WHERE 
    dv.activodet = TRUE;  -- Filtra solo los detalles de venta activos

DELIMITER $$

CREATE FUNCTION calcular_total_pagado_mes(
    cliente_id INT,
    mes INT,
    anio INT
) 
RETURNS DECIMAL(10, 2)
DETERMINISTIC
BEGIN
    DECLARE total_pagado DECIMAL(10, 2);

    -- Calcular el total pagado por el cliente en el mes y año especificados
    SELECT COALESCE(SUM(totalpago), 0) INTO total_pagado
    FROM ventas
    WHERE idcliente = cliente_id
      AND MONTH(fechaventa) = mes
      AND YEAR(fechaventa) = anio;

    RETURN total_pagado;
END$$

DELIMITER ;

-- Vista de clientes usuarios
CREATE VIEW view_clientes_usuarios AS
SELECT 
    u.idcli,
    u.nombre_cliente,
    COUNT(u.idcli) AS usuarios,
    calcular_total_pagado_mes(
        u.idcli, 
        MONTH(CURRENT_DATE),  -- Obtenemos el mes actual
        YEAR(CURRENT_DATE)    -- Obtenemos el año actual
    ) AS facturado
FROM view_usuarios_activos u
GROUP BY u.idcli, u.nombre_cliente;



-- Modificaciones forma 1 no para mysql mariadb
ALTER TABLE contabilidad 
MODIFY COLUMN idcon INT NOT NULL AUTO_INCREMENT PRIMARY KEY;

ALTER TABLE clientes 
MODIFY COLUMN idcli INT NOT NULL AUTO_INCREMENT PRIMARY KEY;

ALTER TABLE costos 
MODIFY COLUMN idcos INT NOT NULL AUTO_INCREMENT PRIMARY KEY;

ALTER TABLE detalles_venta 
MODIFY COLUMN iddet INT NOT NULL AUTO_INCREMENT PRIMARY KEY;

ALTER TABLE empleados 
MODIFY COLUMN idemp INT NOT NULL AUTO_INCREMENT PRIMARY KEY;

ALTER TABLE mantenimientos 
MODIFY COLUMN idman INT NOT NULL AUTO_INCREMENT PRIMARY KEY;

ALTER TABLE gastos 
MODIFY COLUMN idgas INT NOT NULL AUTO_INCREMENT PRIMARY KEY;

ALTER TABLE proveedores 
MODIFY COLUMN idpro INT NOT NULL AUTO_INCREMENT PRIMARY KEY;

ALTER TABLE tipo_gasto 
MODIFY COLUMN idtip INT NOT NULL AUTO_INCREMENT PRIMARY KEY;

-- Modificacion en la forma 2
ALTER TABLE clientes 
CHANGE idcli idcli INT NOT NULL AUTO_INCREMENT,
ADD PRIMARY KEY (idcli);


-- Roles y permisos
-- Insertar permisos en la tabla permissions
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('roles.index', 'web', NOW(), NOW()),
('roles.store', 'web', NOW(), NOW()),
('roles.update', 'web', NOW(), NOW()),
('roles.destroy', 'web', NOW(), NOW());

INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('historial.clear', 'web', NOW(), NOW());
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('tareas.index', 'web', NOW(), NOW());
-- Obtener el ID del rol de administrador
SET @admin_role_id = (SELECT id FROM roles WHERE name = 'Admin');

-- Asignar permisos al rol de administrador en la tabla role_has_permissions
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT p.id, @admin_role_id FROM permissions p WHERE p.name IN 
('roles.index', 'roles.store', 'roles.update', 'roles.destroy');


-- Modificaciones 28 de marzo 2025
ALTER TABLE valores ADD COLUMN activoval TINYINT(1) DEFAULT 1;
ALTER TABLE proveedores ADD COLUMN activopro TINYINT(1) DEFAULT 1;
-- Modificaciones 29 de marzo 2025
ALTER TABLE valores ADD COLUMN bot TEXT DEFAULT NULL;

-- Modificaciones 7 de abril 2025
-- Asegurar que la columna exista
ALTER TABLE clientes ADD COLUMN IF NOT EXISTS codigo_referidor VARCHAR(50);

-- Generar el código en mayúsculas
UPDATE clientes
SET codigo_referidor = UPPER(CONCAT(SUBSTRING_INDEX(nombrecli, ' ', 1), '-', LPAD(idcli, 3, '0')))
WHERE codigo_referidor IS NULL;

--trigger
-- Trigger para INSERT
DELIMITER //
CREATE TRIGGER trg_insert_codigo_referidor
BEFORE INSERT ON clientes
FOR EACH ROW
BEGIN
    SET NEW.codigo_referidor = UPPER(CONCAT(SUBSTRING_INDEX(NEW.nombrecli, ' ', 1), '-', LPAD(NEW.idcli, 3, '0')));
END;
//
DELIMITER ;

-- Trigger para UPDATE del nombre
DELIMITER //
CREATE TRIGGER trg_update_codigo_referidor
BEFORE UPDATE ON clientes
FOR EACH ROW
BEGIN
    IF NEW.nombrecli != OLD.nombrecli THEN
        SET NEW.codigo_referidor = UPPER(CONCAT(SUBSTRING_INDEX(NEW.nombrecli, ' ', 1), '-', LPAD(NEW.idcli, 3, '0')));
    END IF;
END;
//
DELIMITER ;

-- referido por
ALTER TABLE clientes 
ADD COLUMN referido_por BIGINT(20) NULL;

-- Agregar la foreign key (opcional)
ALTER TABLE clientes
ADD CONSTRAINT fk_referido_por FOREIGN KEY (referido_por)
REFERENCES clientes(idcli)
ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE clientes ADD COLUMN ya_compro BOOLEAN DEFAULT FALSE;

UPDATE clientes
SET ya_compro = TRUE
WHERE idcli IN (
    SELECT DISTINCT idcli
    FROM ventas
);

-- Modificar la columna pinper a la tabla perfiles
ALTER TABLE perfiles
MODIFY COLUMN pinper VARCHAR(255) DEFAULT 'ninguno';

-- Eliminar las columnas 'fecha' y 'updated_at' de la tabla 'historial'
ALTER TABLE historial DROP COLUMN IF EXISTS fecha;
ALTER TABLE historial DROP COLUMN IF EXISTS updated_at;
-- Renombrar la columna 'realizado_por' a 'empleado_id'
ALTER TABLE historial CHANGE realizado_por empleado_id BIGINT;

-- Agregar la clave foránea para 'empleado_id' que referencia la tabla 'empleados'
ALTER TABLE historial ADD CONSTRAINT fk_empleado_id FOREIGN KEY (empleado_id) REFERENCES empleados(idemp) ON DELETE SET NULL;