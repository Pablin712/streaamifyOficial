DELIMITER $$

CREATE TRIGGER trg_actualizar_total_venta
AFTER INSERT OR UPDATE OR DELETE ON detalles_venta
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
END$$

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
DELIMITER $$
CREATE PROCEDURE insertar_perfiles(IN cuenta VARCHAR(255))
BEGIN
    -- Insertar perfiles para Netflix
    IF cuenta LIKE 'NETFLIX%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(cuenta, '.1'), cuenta, 1, '1000'),
        (CONCAT(cuenta, '.2'), cuenta, 2, '5555'),
        (CONCAT(cuenta, '.3'), cuenta, 3, '8833'),
        (CONCAT(cuenta, '.4'), cuenta, 4, '6622'),
        (CONCAT(cuenta, '.5'), cuenta, 5, '9000');
    END IF;
    -- Insertar perfiles para Disney
    IF cuenta LIKE 'DISNEY%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(cuenta, '.1'), cuenta, 1, '1000'),
        (CONCAT(cuenta, '.2'), cuenta, 2, '5555'),
        (CONCAT(cuenta, '.3'), cuenta, 3, '8833'),
        (CONCAT(cuenta, '.4'), cuenta, 4, '6622'),
        (CONCAT(cuenta, '.5'), cuenta, 5, '9000'),
        (CONCAT(cuenta, '.6'), cuenta, 6, '2012'),
        (CONCAT(cuenta, '.7'), cuenta, 7, '2000');
    END IF;
    -- Insertar perfiles para Prime Video
    IF cuenta LIKE 'PRIME%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(cuenta, '.1'), cuenta, 1, '10000'),
        (CONCAT(cuenta, '.2'), cuenta, 2, '55555'),
        (CONCAT(cuenta, '.3'), cuenta, 3, '88333'),
        (CONCAT(cuenta, '.4'), cuenta, 4, '66222'),
        (CONCAT(cuenta, '.5'), cuenta, 5, '90000'),
        (CONCAT(cuenta, '.6'), cuenta, 6, '20122');
    END IF;
    -- Insertar perfiles para Max
    IF cuenta LIKE 'MAX%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(cuenta, '.1'), cuenta, 1, '1000'),
        (CONCAT(cuenta, '.2'), cuenta, 2, '5555'),
        (CONCAT(cuenta, '.3'), cuenta, 3, '8833'),
        (CONCAT(cuenta, '.4'), cuenta, 4, '6622'),
        (CONCAT(cuenta, '.5'), cuenta, 5, '9000');
    END IF;
    -- Insertar perfiles para Paramount
    IF cuenta LIKE 'PARAMOUNT%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(cuenta, '.1'), cuenta, 1, '1000'),
        (CONCAT(cuenta, '.2'), cuenta, 2, '5555'),
        (CONCAT(cuenta, '.3'), cuenta, 3, '8833'),
        (CONCAT(cuenta, '.4'), cuenta, 4, '6622'),
        (CONCAT(cuenta, '.5'), cuenta, 5, '9000'),
        (CONCAT(cuenta, '.6'), cuenta, 6, '2012');
    END IF;
    -- Insertar perfiles para Spotify
    IF cuenta LIKE 'SPOTIFY%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(cuenta, '.1'), cuenta, 1, 'owner'),
        (CONCAT(cuenta, '.2'), cuenta, 2, 'invit'),
        (CONCAT(cuenta, '.3'), cuenta, 3, 'invit'),
        (CONCAT(cuenta, '.4'), cuenta, 4, 'invit'),
        (CONCAT(cuenta, '.5'), cuenta, 5, 'invit'),
        (CONCAT(cuenta, '.6'), cuenta, 6, 'invit');
    END IF;
    -- Insertar perfiles para Magis TV
    IF cuenta LIKE 'MAGIS%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(cuenta, '.1'), cuenta, 1, ''),
        (CONCAT(cuenta, '.2'), cuenta, 2, ''),
        (CONCAT(cuenta, '.3'), cuenta, 3, '');
    END IF;
    -- Insertar perfiles para Crunchyroll
    IF cuenta LIKE 'CRUNCHY%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(cuenta, '.1'), cuenta, 1, ''),
        (CONCAT(cuenta, '.2'), cuenta, 2, ''),
        (CONCAT(cuenta, '.3'), cuenta, 3, ''),
        (CONCAT(cuenta, '.4'), cuenta, 4, ''),
        (CONCAT(cuenta, '.5'), cuenta, 5, '');
    END IF;
    -- Insertar perfiles para cuentas personalizadas
    IF cuenta LIKE 'ind%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (CONCAT(cuenta, '.1'), cuenta, 1, 'aparte');
    END IF;
END;

CREATE TRIGGER TG_insertar_perfiles
AFTER INSERT ON cuentas
FOR EACH ROW
BEGIN
    CALL insertar_perfiles(NEW.IDCUE);
END;
DELIMITER ;


--VISTAS

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
