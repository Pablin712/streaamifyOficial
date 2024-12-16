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
    DECLARE numero_venta INT;
    
    -- Verificar el número máximo de ventas del día actual en la tabla ventas_diarias
    SELECT COALESCE(MAX(numero_venta), 0)
    INTO numero_venta
    FROM ventas_diarias
    WHERE fecha = CURRENT_DATE;

    -- Incrementar el número de venta del día actual
    SET numero_venta = numero_venta + 1;

    -- Si ya existe un registro para la fecha actual, actualizamos el número de venta
    IF EXISTS (SELECT 1 FROM ventas_diarias WHERE fecha = CURRENT_DATE) THEN
        UPDATE ventas_diarias
        SET numero_venta = numero_venta
        WHERE fecha = CURRENT_DATE;
    ELSE
        -- Si no existe, insertamos un nuevo registro
        INSERT INTO ventas_diarias (fecha, numero_venta)
        VALUES (CURRENT_DATE, numero_venta);
    END IF;

    -- Generar el número de venta en el formato deseado
    SET NEW.idven = CONCAT('FAC', LPAD(numero_venta, 3, '0'), '-', DATE_FORMAT(CURRENT_DATE, '%d%m%Y'));
END$$

DELIMITER ;



-- Crear el trigger asociado
-- Crear la función insertar_perfiles
DELIMITER $$

CREATE TRIGGER insertar_perfiles
AFTER INSERT ON cuentas
FOR EACH ROW
BEGIN
    -- Insertar perfiles para Netflix
    IF NEW.IDCUE LIKE 'NETFLIX%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(NEW.IDCUE, '.1'), NEW.IDCUE, 1, '1000'),
        (CONCAT(NEW.IDCUE, '.2'), NEW.IDCUE, 2, '5555'),
        (CONCAT(NEW.IDCUE, '.3'), NEW.IDCUE, 3, '8833'),
        (CONCAT(NEW.IDCUE, '.4'), NEW.IDCUE, 4, '6622'),
        (CONCAT(NEW.IDCUE, '.5'), NEW.IDCUE, 5, '9000');
    END IF;

    -- Insertar perfiles para Disney
    IF NEW.IDCUE LIKE 'DISNEY%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(NEW.IDCUE, '.1'), NEW.IDCUE, 1, '1000'),
        (CONCAT(NEW.IDCUE, '.2'), NEW.IDCUE, 2, '5555'),
        (CONCAT(NEW.IDCUE, '.3'), NEW.IDCUE, 3, '8833'),
        (CONCAT(NEW.IDCUE, '.4'), NEW.IDCUE, 4, '6622'),
        (CONCAT(NEW.IDCUE, '.5'), NEW.IDCUE, 5, '9000'),
        (CONCAT(NEW.IDCUE, '.6'), NEW.IDCUE, 6, '2012'),
        (CONCAT(NEW.IDCUE, '.7'), NEW.IDCUE, 7, '2000');
    END IF;

    -- Insertar perfiles para Prime Video
    IF NEW.IDCUE LIKE 'PRIME%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(NEW.IDCUE, '.1'), NEW.IDCUE, 1, '10000'),
        (CONCAT(NEW.IDCUE, '.2'), NEW.IDCUE, 2, '55555'),
        (CONCAT(NEW.IDCUE, '.3'), NEW.IDCUE, 3, '88333'),
        (CONCAT(NEW.IDCUE, '.4'), NEW.IDCUE, 4, '66222'),
        (CONCAT(NEW.IDCUE, '.5'), NEW.IDCUE, 5, '90000'),
        (CONCAT(NEW.IDCUE, '.6'), NEW.IDCUE, 6, '20122');
    END IF;

    -- Insertar perfiles para Max
    IF NEW.IDCUE LIKE 'MAX%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(NEW.IDCUE, '.1'), NEW.IDCUE, 1, '1000'),
        (CONCAT(NEW.IDCUE, '.2'), NEW.IDCUE, 2, '5555'),
        (CONCAT(NEW.IDCUE, '.3'), NEW.IDCUE, 3, '8833'),
        (CONCAT(NEW.IDCUE, '.4'), NEW.IDCUE, 4, '6622'),
        (CONCAT(NEW.IDCUE, '.5'), NEW.IDCUE, 5, '9000');
    END IF;

    -- Insertar perfiles para Paramount
    IF NEW.IDCUE LIKE 'PARAMOUNT%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(NEW.IDCUE, '.1'), NEW.IDCUE, 1, '1000'),
        (CONCAT(NEW.IDCUE, '.2'), NEW.IDCUE, 2, '5555'),
        (CONCAT(NEW.IDCUE, '.3'), NEW.IDCUE, 3, '8833'),
        (CONCAT(NEW.IDCUE, '.4'), NEW.IDCUE, 4, '6622'),
        (CONCAT(NEW.IDCUE, '.5'), NEW.IDCUE, 5, '9000'),
        (CONCAT(NEW.IDCUE, '.6'), NEW.IDCUE, 6, '2012');
    END IF;

    -- Insertar perfiles para Spotify
    IF NEW.IDCUE LIKE 'SPOTIFY%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(NEW.IDCUE, '.1'), NEW.IDCUE, 1, 'owner'),
        (CONCAT(NEW.IDCUE, '.2'), NEW.IDCUE, 2, 'invit'),
        (CONCAT(NEW.IDCUE, '.3'), NEW.IDCUE, 3, 'invit'),
        (CONCAT(NEW.IDCUE, '.4'), NEW.IDCUE, 4, 'invit'),
        (CONCAT(NEW.IDCUE, '.5'), NEW.IDCUE, 5, 'invit'),
        (CONCAT(NEW.IDCUE, '.6'), NEW.IDCUE, 6, 'invit');
    END IF;

    -- Insertar perfiles para MAGIS
    IF NEW.IDCUE LIKE 'MAGIS%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(NEW.IDCUE, '.1'), NEW.IDCUE, 1, ''),
        (CONCAT(NEW.IDCUE, '.2'), NEW.IDCUE, 2, ''),
        (CONCAT(NEW.IDCUE, '.3'), NEW.IDCUE, 3, '');
    END IF;

    -- Insertar perfiles para Crunchyroll
    IF NEW.IDCUE LIKE 'CRUNCHY%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(NEW.IDCUE, '.1'), NEW.IDCUE, 1, ''),
        (CONCAT(NEW.IDCUE, '.2'), NEW.IDCUE, 2, ''),
        (CONCAT(NEW.IDCUE, '.3'), NEW.IDCUE, 3, ''),
        (CONCAT(NEW.IDCUE, '.4'), NEW.IDCUE, 4, ''),
        (CONCAT(NEW.IDCUE, '.5'), NEW.IDCUE, 5, '');
    END IF;

    -- Insertar perfiles para IND
    IF NEW.IDCUE LIKE 'ind%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(NEW.IDCUE, '.1'), NEW.IDCUE, 1, 'aparte');
    END IF;
END$$

DELIMITER ;


-- Vista de Usuarios Activos
CREATE VIEW view_usuarios_activos AS
SELECT 
    v.IDCLI,
    cl.NOMBRECLI AS nombre_cliente,
    dv.idven,
    dv.iddet,
    p.IDCUE,  -- Relacionamos el perfil con la cuenta a través de IDCUE
    p.NUMEROPER AS perfil,  -- Número de perfil desde la tabla PERFILES
    dv.FECHAVENdet AS fecha_vencimiento
FROM 
    DETALLES_VENTA dv
    INNER JOIN VENTAS v ON dv.IDVEN = v.IDVEN  -- Conectamos DETALLES_VENTA con VENTAS
    INNER JOIN CLIENTES cl ON v.IDCLI = cl.IDCLI  -- Conectamos VENTAS con CLIENTES
    INNER JOIN PERFILES p ON dv.IDPER = p.IDPER  -- Conectamos DETALLES_VENTA con PERFILES
    INNER JOIN CUENTAS c ON p.IDCUE = c.IDCUE  -- Conectamos PERFILES con CUENTAS
WHERE 
    dv.ACTIVODET = TRUE;  -- Filtra solo los detalles de venta activos




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
    SELECT COALESCE(SUM(TOTALPAGO), 0) INTO total_pagado
    FROM VENTAS
    WHERE IDCLIENTE = cliente_id
      AND MONTH(FECHAVENTA) = mes
      AND YEAR(FECHAVENTA) = anio;

    RETURN total_pagado;
END$$

DELIMITER ;

-- Vista de Clientes Usuarios
CREATE VIEW view_clientes_usuarios AS
SELECT 
    u.IDCLI,
    u.nombre_cliente,
    COUNT(u.IDCLI) AS usuarios,
    calcular_total_pagado_mes(
        u.IDCLI, 
        MONTH(CURRENT_DATE),  -- Obtenemos el mes actual
        YEAR(CURRENT_DATE)    -- Obtenemos el año actual
    ) AS facturado
FROM view_usuarios_activos u
GROUP BY u.IDCLI, u.nombre_cliente;
