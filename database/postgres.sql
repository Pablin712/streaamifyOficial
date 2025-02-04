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

-- Crear una secuencia para la parte numérica de 9 dígitos (inicia en 1 y se incrementa automáticamente)
CREATE SEQUENCE secuencia_factura START 1;

-- Trigger modificado para generar el IDVEN según el formato del SRI
CREATE OR REPLACE FUNCTION generar_idventa()
RETURNS TRIGGER AS $$
DECLARE
    establecimiento TEXT := '001';   -- Número de establecimiento (ej: 001)
    facturero TEXT := '001';         -- Número de facturero (ej: 001)
    secuencia BIGINT;                -- Número secuencial de 9 dígitos
BEGIN
    -- Obtener el siguiente valor de la secuencia
    secuencia := nextval('secuencia_factura');
    
    -- Formatear el IDVEN: establecimiento + facturero + secuencia de 9 dígitos
    NEW.IDVEN := 
        establecimiento || '-' || 
        facturero || '-' || 
        LPAD(secuencia::TEXT, 9, '0'); -- Rellena con ceros a la izquierda
    
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
    IF NEW.IDCUE LIKE 'NETFLIX%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (NEW.IDCUE || '.1', NEW.IDCUE, 1, '1000'),
        (NEW.IDCUE || '.2', NEW.IDCUE, 2, '5555'),
        (NEW.IDCUE || '.3', NEW.IDCUE, 3, '8833'),
        (NEW.IDCUE || '.4', NEW.IDCUE, 4, '6622'),
        (NEW.IDCUE || '.5', NEW.IDCUE, 5, '9000');
    -- Insertar perfiles para Disney
    ELSEIF NEW.IDCUE LIKE 'DISNEY%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (NEW.IDCUE || '.1', NEW.IDCUE, 1, '1000'),
        (NEW.IDCUE || '.2', NEW.IDCUE, 2, '5555'),
        (NEW.IDCUE || '.3', NEW.IDCUE, 3, '8833'),
        (NEW.IDCUE || '.4', NEW.IDCUE, 4, '6622'),
        (NEW.IDCUE || '.5', NEW.IDCUE, 5, '9000'),
        (NEW.IDCUE || '.6', NEW.IDCUE, 6, '2012'),
        (NEW.IDCUE || '.7', NEW.IDCUE, 7, '2000');
    -- Insertar perfiles para Prime Video
    ELSEIF NEW.IDCUE LIKE 'PRIME%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (NEW.IDCUE || '.1', NEW.IDCUE, 1, '10000'),
        (NEW.IDCUE || '.2', NEW.IDCUE, 2, '55555'),
        (NEW.IDCUE || '.3', NEW.IDCUE, 3, '88333'),
        (NEW.IDCUE || '.4', NEW.IDCUE, 4, '66222'),
        (NEW.IDCUE || '.5', NEW.IDCUE, 5, '90000'),
        (NEW.IDCUE || '.6', NEW.IDCUE, 6, '20122');
    -- Insertar perfiles para Max
    ELSEIF NEW.IDCUE LIKE 'MAX%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (NEW.IDCUE || '.1', NEW.IDCUE, 1, '1000'),
        (NEW.IDCUE || '.2', NEW.IDCUE, 2, '5555'),
        (NEW.IDCUE || '.3', NEW.IDCUE, 3, '8833'),
        (NEW.IDCUE || '.4', NEW.IDCUE, 4, '6622'),
        (NEW.IDCUE || '.5', NEW.IDCUE, 5, '9000');
    -- Insertar perfiles para Paramount
    ELSEIF NEW.IDCUE LIKE 'PARAMOUNT%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (NEW.IDCUE || '.1', NEW.IDCUE, 1, '1000'),
        (NEW.IDCUE || '.2', NEW.IDCUE, 2, '5555'),
        (NEW.IDCUE || '.3', NEW.IDCUE, 3, '8833'),
        (NEW.IDCUE || '.4', NEW.IDCUE, 4, '6622'),
        (NEW.IDCUE || '.5', NEW.IDCUE, 5, '9000'),
        (NEW.IDCUE || '.6', NEW.IDCUE, 6, '2012');
    -- Insertar perfiles para Spotify
    ELSEIF NEW.IDCUE LIKE 'SPOTIFY%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (NEW.IDCUE || '.1', NEW.IDCUE, 1, 'owner'),
        (NEW.IDCUE || '.2', NEW.IDCUE, 2, 'invit'),
        (NEW.IDCUE || '.3', NEW.IDCUE, 3, 'invit'),
        (NEW.IDCUE || '.4', NEW.IDCUE, 4, 'invit'),
        (NEW.IDCUE || '.5', NEW.IDCUE, 5, 'invit'),
        (NEW.IDCUE || '.6', NEW.IDCUE, 6, 'invit');
    ELSEIF NEW.IDCUE LIKE 'MAGIS%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (NEW.IDCUE || '.1', NEW.IDCUE, 1, ''),
        (NEW.IDCUE || '.2', NEW.IDCUE, 2, ''),
        (NEW.IDCUE || '.3', NEW.IDCUE, 3, '');
    ELSEIF NEW.IDCUE LIKE 'CRUNCHY%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (NEW.IDCUE || '.1', NEW.IDCUE, 1, ''),
        (NEW.IDCUE || '.2', NEW.IDCUE, 2, ''),
        (NEW.IDCUE || '.3', NEW.IDCUE, 3, ''),
        (NEW.IDCUE || '.4', NEW.IDCUE, 4, ''),
        (NEW.IDCUE || '.5', NEW.IDCUE, 5, '');
    ELSEIF NEW.IDCUE LIKE 'IND%' THEN
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (NEW.IDCUE || '.1', NEW.IDCUE, 1, 'aparte');
    ELSE
        INSERT INTO Perfiles (IDPER, IDCUE, NUMEROPER, PINPER) VALUES
        (NEW.IDCUE || '.1', NEW.IDCUE, 1, 'vac'),
        (NEW.IDCUE || '.2', NEW.IDCUE, 2, 'vac'),
        (NEW.IDCUE || '.3', NEW.IDCUE, 3, 'vac');
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
-- Crear el trigger
CREATE OR REPLACE TRIGGER TG_insertar_perfiles
AFTER INSERT ON cuentas
FOR EACH ROW
EXECUTE FUNCTION insertar_perfiles();

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


CREATE OR REPLACE FUNCTION calcular_total_pagado_mes(
    cliente_id INTEGER,
    mes INTEGER,
    anio INTEGER
) RETURNS DECIMAL(10, 2) AS $$
DECLARE
    total_pagado DECIMAL(10, 2);
BEGIN
    -- Calcular el total pagado por el cliente en el mes y año especificados
    SELECT COALESCE(SUM(TOTALPAGO), 0) INTO total_pagado
    FROM VENTAS
    WHERE IDCLIENTE = cliente_id
    AND EXTRACT(MONTH FROM FECHAVENTA) = mes
    AND EXTRACT(YEAR FROM FECHAVENTA) = anio;
    RETURN total_pagado;
END;
$$ LANGUAGE plpgsql;

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