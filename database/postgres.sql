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
    SET TOTALPAGO = (SELECT COALESCE(SUM(MONTO), 0) 
                     FROM DETALLES_VENTA
                     WHERE IDVENTA = NEW.IDVENTA)
    WHERE IDVENTA = NEW.IDVENTA;
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
BEGIN
    -- Reinicia la secuencia cada día
    IF CURRENT_DATE != (SELECT TO_DATE(last_value::text, 'YYYYMMDD') FROM ventas_diarias_seq) THEN
        PERFORM setval('ventas_diarias_seq', 1, false);  -- Reinicia la secuencia a 1
    END IF;

    -- Genera el número de venta con tres cifras
    numero_venta := LPAD(NEXTVAL('ventas_diarias_seq')::TEXT, 3, '0');

    -- Asigna el IDVENTA en el formato deseado
    NEW.IDVENTA := 'FAC' || numero_venta || TO_CHAR(CURRENT_DATE, 'DDMMYYYY');
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_generar_idventa BEFORE
INSERT
    ON VENTAS FOR EACH ROW WHEN (NEW.IDVENTA IS NULL)
EXECUTE FUNCTION generar_idventa ();

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
CREATE OR REPLACE VIEW usuarios_activos AS
SELECT
    v.IDCLIENTE,
    cl.NOMBRE AS nombre_cliente,
    dv.IDCUENTA,
    DV.PERFIL,
    c.FECHAVENC AS fecha_vencimiento_cuenta
FROM
    DETALLES_VENTA dv
    INNER JOIN VENTAS v ON dv.IDVENTA = v.IDVENTA
    INNER JOIN CLIENTES cl ON v.IDCLIENTE = cl.IDCLIENTE
    INNER JOIN CUENTAS c ON dv.IDCUENTA = c.IDCUENTA
WHERE
    dv.ACTIVO = TRUE;

CREATE OR REPLACE VIEW CLIENTES_USUARIOS AS
SELECT
    U.IDCLIENTE,
    U.nombre_cliente,
    COUNT(U.IDCLIENTE) AS USUARIOS,
    calcular_total_pagado_mes (
        U.IDCLIENTE,
        CAST(
            EXTRACT(
                MONTH
                FROM CURRENT_DATE
            ) AS INTEGER
        ),
        CAST(
            EXTRACT(
                YEAR
                FROM CURRENT_DATE
            ) AS INTEGER
        )
    ) AS FACTURADO
FROM USUARIOS_ACTIVOS U
GROUP BY
    U.IDCLIENTE,
    U.NOMBRE_CLIENTE;
