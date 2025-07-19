CREATE TRIGGER `insertar_perfiles` AFTER INSERT ON `cuentas`
 FOR EACH ROW BEGIN
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
END

CREATE TRIGGER `trg_actualizar_total_venta_delete` AFTER DELETE ON `detalles_venta`
 FOR EACH ROW BEGIN
    -- Calcula el total de la venta actual
    UPDATE ventas
    SET totalpagoven = (
        SELECT COALESCE(SUM(montodet), 0)
        FROM detalles_venta
        WHERE idven = OLD.idven
    )
    WHERE idven = OLD.idven;
END

CREATE TRIGGER `trg_actualizar_total_venta_insert` AFTER INSERT ON `detalles_venta`
 FOR EACH ROW BEGIN
    -- Calcula el total de la venta actual
    UPDATE ventas
    SET totalpagoven = (
        SELECT COALESCE(SUM(montodet), 0)
        FROM detalles_venta
        WHERE idven = NEW.idven
    )
    WHERE idven = NEW.idven;
END

CREATE TRIGGER `trg_actualizar_total_venta_update` AFTER UPDATE ON `detalles_venta`
 FOR EACH ROW BEGIN
    -- Calcula el total de la venta actual
    UPDATE ventas
    SET totalpagoven = (
        SELECT COALESCE(SUM(montodet), 0)
        FROM detalles_venta
        WHERE idven = NEW.idven
    )
    WHERE idven = NEW.idven;
END

CREATE TRIGGER `trg_generar_idventa` BEFORE INSERT ON `ventas`
 FOR EACH ROW BEGIN
    DECLARE secuencia BIGINT;
    DECLARE establecimiento VARCHAR(3) DEFAULT '001';  -- Número de establecimiento
    DECLARE facturero VARCHAR(3) DEFAULT '001';        -- Número de facturero

    -- Insertar un nuevo registro en la tabla secuencia_factura y obtener el ID generado
    INSERT INTO secuencia_factura () VALUES ();
    SET secuencia = LAST_INSERT_ID();

    -- Generar el ID de venta con el formato: establecimiento-facturero-secuencia de 9 dígitos
    SET NEW.idven = CONCAT(establecimiento, '-', facturero, '-', LPAD(secuencia, 9, '0'));

END

CREATE TRIGGER `trg_insert_codigo_referidor` BEFORE INSERT ON `clientes`
 FOR EACH ROW BEGIN
    SET NEW.codigo_referidor = UPPER(CONCAT(SUBSTRING_INDEX(NEW.nombrecli, ' ', 1), '-', LPAD(NEW.idcli, 3, '0')));
END

CREATE TRIGGER `trg_update_codigo_referidor` BEFORE UPDATE ON `clientes`
 FOR EACH ROW BEGIN
    IF NEW.nombrecli != OLD.nombrecli THEN
        SET NEW.codigo_referidor = UPPER(CONCAT(SUBSTRING_INDEX(NEW.nombrecli, ' ', 1), '-', LPAD(NEW.idcli, 3, '0')));
    END IF;
END

CREATE TRIGGER `trigger_generar_idval` BEFORE INSERT ON `valores`
 FOR EACH ROW BEGIN
                    DECLARE tipo_valor VARCHAR(10);
                    DECLARE proveedor_nombre VARCHAR(100);

                    SET tipo_valor = LEFT(NEW.tipoval, 3);

                    SELECT SUBSTRING_INDEX(nombrepro, ' ', 1) INTO proveedor_nombre
                    FROM proveedores WHERE idpro = NEW.idpro LIMIT 1;

                    SET NEW.idval = CONCAT(NEW.idser, '-', proveedor_nombre, '-', tipo_valor, '-', NEW.mesesval, 'm');
                END
