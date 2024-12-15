--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
-- TRIGGERS
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
CREATE OR REPLACE FUNCTION actualizar_total_venta()
RETURNS TRIGGER AS $$
BEGIN
    -- Calcula el total de la venta actual
    UPDATE VENTAS
    SET TOTALPAGOVEN = (SELECT COALESCE(SUM(MONTODET), 0) 
                     FROM DETALLES_VENTA
                     WHERE IDVEN = NEW.IDVEN)
    WHERE IDVEN = NEW.IDVEN;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_actualizar_total_venta AFTER
INSERT
    OR
UPDATE
OR DELETE ON DETALLES_VENTA FOR EACH ROW
EXECUTE FUNCTION actualizar_total_venta ();

--TRIGGER QUE SE UNE DESPUES DE LA SECUENCIA Y FUNCION DE GENERARIDVENTA()
CREATE OR REPLACE FUNCTION generar_idventa()
RETURNS TRIGGER AS $$
DECLARE
    numero_venta TEXT;
    max_numero_venta INTEGER;
BEGIN
    -- Verificar el número máximo de ventas del día actual en la tabla ventas_diarias
    SELECT COALESCE(MAX(vd.numero_venta), 0)
    INTO max_numero_venta
    FROM ventas_diarias vd
    WHERE vd.fecha = CURRENT_DATE;

    -- Incrementar el número de venta del día actual
    max_numero_venta := max_numero_venta + 1;

    -- Si ya existe un registro para la fecha actual, actualizamos el número de venta
    IF EXISTS (SELECT 1 FROM ventas_diarias WHERE fecha = CURRENT_DATE) THEN
        UPDATE ventas_diarias
        SET numero_venta = max_numero_venta
        WHERE fecha = CURRENT_DATE;
    ELSE
        -- Si no existe, insertamos un nuevo registro
        INSERT INTO ventas_diarias (fecha, numero_venta)
        VALUES (CURRENT_DATE, max_numero_venta);
    END IF;

    -- Generar el número de venta en el formato deseado
    numero_venta := LPAD(max_numero_venta::TEXT, 3, '0');
    NEW.IDVEN := 'FAC' || numero_venta || TO_CHAR(CURRENT_DATE, 'DDMMYYYY');

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;



-- Crear el trigger asociado
CREATE or replace TRIGGER trg_generar_idventa
BEFORE INSERT
ON ventas
FOR EACH ROW
WHEN (NEW.IDVEN IS NULL)
EXECUTE FUNCTION generar_idventa();

-- Crear la función INSERTAR PERFILES
CREATE OR REPLACE FUNCTION insertar_perfiles()
RETURNS TRIGGER AS $$
BEGIN
    -- Insertar perfiles para Netflix
    IF NEW.IDCUENTA LIKE 'NETFLIX%' THEN
        INSERT INTO Perfiles (IDPERFIL, IDCUENTA, NUMERO, PIN) VALUES
        (NEW.IDCUENTA || '.1', NEW.IDCUENTA, 1, '1000'),
        (NEW.IDCUENTA || '.2', NEW.IDCUENTA, 2, '5555'),
        (NEW.IDCUENTA || '.3', NEW.IDCUENTA, 3, '8833'),
        (NEW.IDCUENTA || '.4', NEW.IDCUENTA, 4, '6622'),
        (NEW.IDCUENTA || '.5', NEW.IDCUENTA, 5, '9000');
    END IF;
    -- Insertar perfiles para Disney
    IF NEW.IDCUENTA LIKE 'DISNEY%' THEN
        INSERT INTO Perfiles (IDPERFIL, IDCUENTA, NUMERO, PIN) VALUES
        (NEW.IDCUENTA || '.1', NEW.IDCUENTA, 1, '1000'),
        (NEW.IDCUENTA || '.2', NEW.IDCUENTA, 2, '5555'),
        (NEW.IDCUENTA || '.3', NEW.IDCUENTA, 3, '8833'),
        (NEW.IDCUENTA || '.4', NEW.IDCUENTA, 4, '6622'),
        (NEW.IDCUENTA || '.5', NEW.IDCUENTA, 5, '9000'),
        (NEW.IDCUENTA || '.6', NEW.IDCUENTA, 6, '2012'),
        (NEW.IDCUENTA || '.7', NEW.IDCUENTA, 7, '2000');
    END IF;
    -- Insertar perfiles para Prime Video
    IF NEW.IDCUENTA LIKE 'PRIME%' THEN
        INSERT INTO Perfiles (IDPERFIL, IDCUENTA, NUMERO, PIN) VALUES
        (NEW.IDCUENTA || '.1', NEW.IDCUENTA, 1, '10000'),
        (NEW.IDCUENTA || '.2', NEW.IDCUENTA, 2, '55555'),
        (NEW.IDCUENTA || '.3', NEW.IDCUENTA, 3, '88333'),
        (NEW.IDCUENTA || '.4', NEW.IDCUENTA, 4, '66222'),
        (NEW.IDCUENTA || '.5', NEW.IDCUENTA, 5, '90000'),
        (NEW.IDCUENTA || '.6', NEW.IDCUENTA, 6, '20122');
    END IF;
    -- Insertar perfiles para Max
    IF NEW.IDCUENTA LIKE 'MAX%' THEN
        INSERT INTO Perfiles (IDPERFIL, IDCUENTA, NUMERO, PIN) VALUES
        (NEW.IDCUENTA || '.1', NEW.IDCUENTA, 1, '1000'),
        (NEW.IDCUENTA || '.2', NEW.IDCUENTA, 2, '5555'),
        (NEW.IDCUENTA || '.3', NEW.IDCUENTA, 3, '8833'),
        (NEW.IDCUENTA || '.4', NEW.IDCUENTA, 4, '6622'),
        (NEW.IDCUENTA || '.5', NEW.IDCUENTA, 5, '9000');
    END IF;
    -- Insertar perfiles para Paramount
    IF NEW.IDCUENTA LIKE 'PARAMOUNT%' THEN
        INSERT INTO Perfiles (IDPERFIL, IDCUENTA, NUMERO, PIN) VALUES
        (NEW.IDCUENTA || '.1', NEW.IDCUENTA, 1, '1000'),
        (NEW.IDCUENTA || '.2', NEW.IDCUENTA, 2, '5555'),
        (NEW.IDCUENTA || '.3', NEW.IDCUENTA, 3, '8833'),
        (NEW.IDCUENTA || '.4', NEW.IDCUENTA, 4, '6622'),
        (NEW.IDCUENTA || '.5', NEW.IDCUENTA, 5, '9000'),
        (NEW.IDCUENTA || '.6', NEW.IDCUENTA, 6, '2012');
    END IF;
    -- Insertar perfiles para Spotify
    IF NEW.IDCUENTA LIKE 'SPOTIFY%' THEN
        INSERT INTO Perfiles (IDPERFIL, IDCUENTA, NUMERO, PIN) VALUES
        (NEW.IDCUENTA || '.1', NEW.IDCUENTA, 1, 'owner'),
        (NEW.IDCUENTA || '.2', NEW.IDCUENTA, 2, 'invit'),
        (NEW.IDCUENTA || '.3', NEW.IDCUENTA, 3, 'invit'),
        (NEW.IDCUENTA || '.4', NEW.IDCUENTA, 4, 'invit'),
        (NEW.IDCUENTA || '.5', NEW.IDCUENTA, 5, 'invit'),
        (NEW.IDCUENTA || '.6', NEW.IDCUENTA, 6, 'invit');
    END IF;
    IF NEW.IDCUENTA LIKE 'MAGIS%' THEN
        INSERT INTO Perfiles (IDPERFIL, IDCUENTA, NUMERO, PIN) VALUES
        (NEW.IDCUENTA || '.1', NEW.IDCUENTA, 1, ''),
        (NEW.IDCUENTA || '.2', NEW.IDCUENTA, 2, ''),
        (NEW.IDCUENTA || '.3', NEW.IDCUENTA, 3, '');
    END IF;
    IF NEW.IDCUENTA LIKE 'CRUNCHY%' THEN
        INSERT INTO Perfiles (IDPERFIL, IDCUENTA, NUMERO, PIN) VALUES
        (NEW.IDCUENTA || '.1', NEW.IDCUENTA, 1, ''),
        (NEW.IDCUENTA || '.2', NEW.IDCUENTA, 2, ''),
        (NEW.IDCUENTA || '.3', NEW.IDCUENTA, 3, ''),
        (NEW.IDCUENTA || '.4', NEW.IDCUENTA, 4, ''),
        (NEW.IDCUENTA || '.5', NEW.IDCUENTA, 5, '');
    END IF;
    IF NEW.IDCUENTA LIKE 'ind%' THEN
        INSERT INTO Perfiles (IDPERFIL, IDCUENTA, NUMERO, PIN) VALUES
        (NEW.IDCUENTA || '.1', NEW.IDCUENTA, 1, 'aparte');
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
-- Crear el trigger
CREATE
OR
REPLACE
    TRIGGER TG_insertar_perfiles AFTER
INSERT
    ON Cuentas FOR EACH ROW
EXECUTE FUNCTION insertar_perfiles ();

--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
-- VISTAS
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh

--VISTA USUARIOS ACTIVOS
CREATE OR REPLACE VIEW view_usuarios_activos AS
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


        -- VISTA CLIENTES USUARIOS
CREATE OR REPLACE VIEW view_clientes_usuarios AS
SELECT 
    u.IDCLI,
    u.nombre_cliente,
    COUNT(u.IDCLI) AS usuarios,
    calcular_total_pagado_mes(
        u.IDCLI, 
        CAST(EXTRACT(MONTH FROM CURRENT_DATE) AS INTEGER), 
        CAST(EXTRACT(YEAR FROM CURRENT_DATE) AS INTEGER)
    ) AS facturado
FROM view_usuarios_activos u
GROUP BY u.IDCLI, u.nombre_cliente;