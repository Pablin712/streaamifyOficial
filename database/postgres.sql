--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
-- FUNCIONES
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh

--DEFINIR IDVENTA
--NOTA: SE UNE ANTES DEL TRIGGER GENERARIDVENTA
CREATE SEQUENCE ventas_diarias_seq
  START 1
  MINVALUE 1
  MAXVALUE 999
  CYCLE;

--OBTIENE EL COSTO QUE SE HA ASUMIDO EN EL MES DE UNA CUENTA ESPECIFICA DEL NEGOCIO
CREATE OR REPLACE FUNCTION obtener_costo_mes_actual(id_cuenta_param VARCHAR)
RETURNS DECIMAL(8,2) AS $$
BEGIN
    RETURN COALESCE(
        (SELECT SUM(MONTO)
         FROM COSTOS
         WHERE IDCUENTA = id_cuenta_param
           AND EXTRACT(MONTH FROM FECHACOSTO) = EXTRACT(MONTH FROM CURRENT_DATE)
           AND EXTRACT(YEAR FROM FECHACOSTO) = EXTRACT(YEAR FROM CURRENT_DATE)), 0);
END;
$$ LANGUAGE plpgsql;


--CONTAR USUARIOS ACTIVOS EN UN PERFIL DE UNA CUENTA
CREATE OR REPLACE FUNCTION contar_usuarios_perfil(id_cuenta_param VARCHAR, perfil_numero INTEGER)
RETURNS INTEGER AS $$
BEGIN
    RETURN COALESCE(
        (SELECT COUNT(*)
         FROM DETALLES_VENTA
         WHERE IDCUENTA = id_cuenta_param
           AND PERFIL = perfil_numero
		   AND ACTIVO=TRUE), 0);
END;
$$ LANGUAGE plpgsql;

--CONTAR USUARIOS ACTIVOS TOTALES EN LA CUENTA
CREATE OR REPLACE FUNCTION contar_usuarios_activos(id_cuenta_param VARCHAR)
RETURNS INTEGER AS $$
BEGIN
    RETURN COALESCE(
        (SELECT COUNT(*)
         FROM DETALLES_VENTA
         WHERE IDCUENTA = id_cuenta_param
           AND ACTIVO = TRUE), 0);
END;
$$ LANGUAGE plpgsql;


--CALCULAR EL TOTAL PAGADO DE UN CLIENTE EN UN MES ESPECIFICO
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




--FUNCIONES ESPECIALES, ESPECIFICAS PARA LAS ESTADISTICAS
--A CONTINUACIÓN
--FUNCIONES DE ESTADÍSTICA
CREATE OR REPLACE FUNCTION NUM_CUENTAS_SERVICIO(IDSERVICIO VARCHAR(10))
RETURNS INT AS $$
DECLARE TOTAL INT;
BEGIN
    IF IDSERVICIO='OTRO' THEN
        SELECT COUNT(CU.IDCUENTA) INTO TOTAL FROM CUENTAS CU
        JOIN VALORES VA ON CON.IDVALOR=CU.IDVALOR
        WHERE VA.IDSERVICIO
        NOT IN (
                'NETFLIX', 'MAX', 'PRIME', 'DISNEYP','DISNEYS', 'CRUNCHY', 'SPOTIFY', 'MAGIS', 'PARAMOUNT'
            );
    ELSE
        SELECT COUNT(CU.IDCUENTA) INTO TOTAL FROM CUENTAS CU
        JOIN VALORES VA ON VA.IDVALOR=CU.IDVALOR
        WHERE VA.IDSERVICIO=NUM_CUENTAS_SERVICIO.IDSERVICIO;
    END IF;
    RETURN TOTAL;
END;
$$ LANGUAGE PLPGSQL;

CREATE OR REPLACE FUNCTION SUMA_INGRESOS_DE_SERVICIO_MES(IDSERVICIO VARCHAR(10), MES INT)
RETURNS DECIMAL(8,2) AS $$
DECLARE TOTAL DECIMAL(8,2);
BEGIN
    IF IDSERVICIO = 'OTRO' THEN
        SELECT COALESCE(SUM(DV.MONTO), 0) INTO TOTAL
        FROM DETALLES_VENTA DV
        JOIN VENTAS V ON V.IDVENTA = DV.IDVENTA
        JOIN CUENTAS C ON C.IDCUENTA = DV.IDCUENTA
        JOIN VALORES VA ON VA.IDVALOR = C.IDVALOR
        WHERE EXTRACT(MONTH FROM V.FECHAVENTA) = MES
        AND VA.IDSERVICIO NOT IN (
            'NETFLIX', 'MAX', 'PRIME', 'DISNEYP','DISNEYS', 'CRUNCHY', 'SPOTIFY', 'MAGIS', 'PARAMOUNT'
        );
    ELSE
        SELECT COALESCE(SUM(DV.MONTO),0) INTO TOTAL
        FROM DETALLES_VENTA DV
        JOIN VENTAS V ON V.IDVENTA=DV.IDVENTA
        JOIN CUENTAS C ON C.IDCUENTA = DV.IDCUENTA
        JOIN VALORES VA ON VA.IDVALOR = C.IDVALOR
        WHERE 
            EXTRACT(MONTH FROM V.FECHAVENTA)=MES AND
            SUMA_INGRESOS_DE_SERVICIO_MES.IDSERVICIO=VA.IDSERVICIO;
    END IF;
    RETURN TOTAL;
END;
$$ LANGUAGE PLPGSQL;


--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
-- DATOS PARA INSERTAR EN LAS TABLAS
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh


INSERT INTO SERVICIOS (IDSERVICIO, NOMBRE, COMPLETO, PRECIO, COMBO, REVENTA, REVCOMP) VALUES
    ('NETFLIX', 'NETFLIX PREMIUM', 11, 3.25, 2.75, 2.5, 9),
    ('DISNEYP', 'DISNEY PREMIUM', 11, 3.5, 2.75, 2.5, 9),
    ('DISNEYS', 'DISNEY STANDARD', 8, 2.5, 2, 2, 4.5),
    ('PRIME', 'PRIME VIDEO', 6, 2.25, 1.5, 1.25, 3.5),
    ('MAX', 'MAX STANDARD', 6, 2.5, 2, 2, 4),
    ('MAGIS', 'MAGIS TV', 8, 3.25, 2.75, 2.5, 5),
    ('CRUNCHY', 'CRUNCHYROLL', 4, 1.5, 1, 1, 3.5),
    ('PARAMOUNT', 'PARAMOUNT PLUS', 4, 1.75, 1.25, 1.1, 3.5),
    ('SPOTIFY', 'SPOTIFY PREMIUM', 11, 3, 2.75, 2.5, 11),
    ('VIX', 'VIX', 7.5, 2.75, 2.25, 2.25, 5),
    ('OTRO', 'OTRAS QUE SE VENDEN', 0, 0, 0, 0, 0);

-- Inserción en la tabla PROVEEDORES
INSERT INTO PROVEEDORES (NOMBRE, TELEFONO) VALUES
    ('JUAN DOMÍNGUEZ', '0992905379'),
    ('JOSÉ MORA MOORMIX', '0990880300'),
    ('EC VIRTUAL STORE', '0960523682');

-- Inserción en la tabla VALORES
INSERT INTO VALORES (IDVALOR, IDSERVICIO, IDPROVEEDOR, COSTO, PANTMIN, PANTMAX, MESES) VALUES
    ('NETFLIX-JUAN', 'NETFLIX', 1, 8, 4, 7, 1),
    ('DISNEYP-JUAN', 'DISNEYP', 1, 6, 4, 7, 1),
    ('DISNEYS-JUAN', 'DISNEYS', 1, 6, 4, 7, 1),
    ('MAX-JUAN', 'MAX', 1, 2.5, 3, 5, 1),
    ('PARAMOUNT-JUAN', 'PARAMOUNT', 1, 2.5, 3, 7, 1),
    ('CRUNCHY-JUAN', 'CRUNCHY', 1, 2.5, 4, 8, 1);

-- Inserción en la tabla EMPLEADOS
INSERT INTO EMPLEADOS (NOMBRE, TELEFONO) VALUES
    ('MATEO JIMÉNEZ', '0961702129'),
    ('RONALDO JIMÉNEZ', '0961412826');

-- Inserción en la tabla TIPO_GASTO
INSERT INTO TIPO_GASTO (DETALLE) VALUES
    ('PUBLICIDAD (META ADS)'),
    ('PAGO DE EMPLEADOS'),
    ('SERVICIO EN LA NUBE'),
    ('OTRO');

-- Inserción en la tabla GASTOS
INSERT INTO GASTOS (IDTIPO, FECHAGASTO, MONTO, DESCRIPCION) VALUES
    (1, '2024-09-10', 40, 'FACEBOOK ADS'),
    (2, '2024-10-30', 120, 'PAGO A MATEO'),
    (2, '2024-10-30', 20, 'PAGO A RONALDO'),
    (3, '2024-09-10', 40, 'PAGO DE AWS');




--datos solo de prueba
-- Clientes de PABLIN
INSERT INTO Clientes (Nombre, Telefono)
VALUES 
    ('FRANCISCO PEREZ', NULL),
    ('ALEX IBARRA', NULL),
    ('SULE CACUANGO', NULL),
    ('FABIAN JIMENEZ', NULL),
    ('CARLOS CRESPO', NULL),
    ('ARIEL ANTACURI', NULL),
    ('PAMELA ESPINOSA', NULL),
    ('RICKY ZAMBRANO', NULL),
    ('CAMILA PALACIOS', NULL),
    ('ISRAEL VILLACIS', NULL),
    ('TATIANA POMASQUI', NULL),
    ('ABRIL COELLO', NULL),
    ('DANIEL CALZALUISA', NULL),
    ('JEFFERSON YEPEZ', NULL),
    ('PEDRO BENALCAZAR', NULL),
    ('BYRON JUSTICIA', NULL),
    ('ESTEFANIA BENTANCURT', NULL),
    ('YHON RIOS', NULL),
    ('DIEGO JIMENEZ', NULL),
    ('LINDA BALAREZO', NULL),
    ('SANTIAGO PINEDA', NULL),
    ('DAPHNE AGUIRRE', NULL),
    ('GENSY GIRON', NULL),
    ('RICARDO RAMIREZ', NULL),
    ('JOHANA CEVALLOS', NULL),
    ('KARINARA YASELGA', NULL),
    ('BENJI MARRIOT', NULL),
    ('ALEXANDRA MARTINEZ', NULL),
    ('DIANA MOREIRA', NULL),
    ('JOHAN CHELE', NULL),
    ('FERNANDO AGUILAR', NULL),
    ('EDWIN CAIZA', NULL),
    ('LEIDY VELIZ', NULL),
    ('GABRIEL ARTEAGA', NULL),
    ('PAUCAR CHILA', NULL),
    ('ANDREA RIOFRIO', NULL),
    ('MAMA DE MAIA', NULL),
    ('PABLO JIMENEZ', NULL),
    ('NATHALY BOLANOS', NULL),
    ('PAULO ESCOBAR', NULL),
    ('JOSUE GUZMAN', NULL),
    ('ALEJO MALLAMA', NULL),
    ('FRANCISCO ZUNIGA', NULL),
    ('KATY CARRY', NULL),
    ('RAFAELA CHAMORRO', NULL),
    ('LORENA CABRERA', NULL),
    ('MIRIAM AYALA', NULL),
    ('SANDRA ORELLANA', NULL),
    ('MILENE PALACIOS', NULL),
    ('MAIA MALES', NULL),
    ('DENISSE ACOSTA', NULL),
    ('MIA ARCENTALES', NULL),
    ('JEAN PEREZ', NULL),
    ('NAY MERO', NULL),
    ('ALEX OYAGATA', NULL),
    ('ALEX IBARRA', NULL),
    ('ALAN RIVADENEIRA', NULL),
    ('JEAN PEREZ', NULL),
    ('SHIRLEY ROSERO', NULL);

-- Clientes de MATEO
INSERT INTO Clientes (Nombre, Telefono)
VALUES 
    ('ARIEL FLORES', NULL),
    ('ALEXANDER YACHAY', NULL),
    ('DEIVID PAREDES', NULL),
    ('JHON MORAN', NULL),
    ('MIGUEL AGUDELO', NULL),
    ('LLUMIQUINGA', NULL),
    ('KIMBERLY SALAS', NULL),
    ('CRISTHIAN COELLO', NULL),
    ('GABRIEL CHAMORRO', NULL),
    ('SHIRLEY ROSERO', NULL),
    ('PAOLA MONTENEGRO', NULL),
    ('CARLOS MORETA', NULL),
    ('ASTRIANY GUALAN', NULL),
    ('GABY LEMA', NULL);


--INSERTS PARA CUENTAS Y PERFILES PERO SOLO DE PRUEBA, DATOS NO ACTUALIZADOS Y ERRONEOS
INSERT INTO CUENTAS (IDCUENTA, IDVALOR, USUARIO, FECHAVENC, CONTRASENA, CAIDA) 
VALUES 
    ('DISNEY-1', 'DISNEYP-JUAN', 'combosjose01@scarlitamail.com', '2024-08-15', 'legopoli7P$$', FALSE),
    ('DISNEY-2', 'DISNEYP-JUAN', 'combosjose60@scarlitamail.com', '2024-08-09', 'cuenta123', FALSE),
    ('NETFLIX-1', 'NETFLIX-JUAN', 't-williams23.gb@onehitpe.xyz', '2024-08-03', 'relax0', FALSE),
    ('NETFLIX-2', 'NETFLIX-JUAN', 'cartexmanxd.us@pronyx.xyz', '2024-08-17', 'ele750', FALSE),
    ('NETFLIX-3', 'NETFLIX-JUAN', 'hnadh.us@nextaon.com', '2024-08-03', '836634578', FALSE),
    ('NETFLIX-5', 'NETFLIX-JUAN', 'j.yacoub@jcarlos.vip', '2024-08-25', 'combo123', FALSE),
    ('NETFLIX-6', 'NETFLIX-JUAN', 'dairon.br@pronyx.xyz', '2024-08-04', 'DOLOR123', FALSE),
    ('NETFLIX-8', 'NETFLIX-JUAN', 'katie-whiteman@jcarlos.vip', '2024-08-09', 'R102530', FALSE),
    ('NETFLIX-9', 'NETFLIX-JUAN', 'yaren.ca@yampe.xyz', '2024-08-01', 'ander30', FALSE),
    ('CRUNCHYROLL-1', 'CRUNCHY-JUAN', 'cruncry042@lissistr.com', '2024-08-07', 'JC12345', FALSE),
    ('MAX-1', 'MAX-JUAN', 'jcmax21@scarlitamail.com', '2024-08-13', 'RE44123120', FALSE),
    ('MAX-2', 'MAX-JUAN', 'cc3@scarlitamail.com', '2024-08-28', 'jc123456', FALSE),
    ('MAGIS-1', 'NETFLIX-JUAN', '16mundo91', '2024-08-16', 'lego777', FALSE),
    ('MAGIS-2', 'NETFLIX-JUAN', '2mick', '2024-08-03', 'lego777', FALSE),
    ('MAGIS-3', 'NETFLIX-JUAN', '23magax', '2024-08-23', 'lego777', FALSE),
    ('SPOTIFY-1', 'NETFLIX-JUAN', 'pablitoutn@outlook.es', '2024-08-23', 'HUeWj:Mg2-T.wE8', TRUE),
    ('SPOTIFY-2', 'NETFLIX-JUAN', 'tel: 0999947287', '2024-08-26', 'teléfono ma', FALSE),
    ('SPOTIFY-3', 'NETFLIX-JUAN', 'pdjimeneze@utn.edu.ec', '2024-08-13', 'legopoli7$', FALSE),
    ('SPOTI-3M', 'NETFLIX-JUAN', 'sebastianelizalde1812@gmail.com', '2024-08-13', 'legopoli7P$', FALSE),
    ('SPOTIFY-4', 'NETFLIX-JUAN', 'papimateosebas@gmail.com', '2024-08-06', 'Mateosebas18', FALSE),
    ('SPOTIFY-5', 'NETFLIX-JUAN', 'pablinosoftw@gmail.com', '2024-08-08', 'legopoli7P$', FALSE),
    ('indspoti-F8', 'PARAMOUNT-JUAN', 'spspty86xg@lissistr.com', '2024-08-28', 'nova45687@', FALSE),
    ('indspoti-F9', 'PARAMOUNT-JUAN', 'spot779@exwa.org', '2024-09-09', 'premium5050', FALSE),
    ('indspoti-F0', 'PARAMOUNT-JUAN', 'pablinj712@gmail.com', '2024-10-07', 'legopoli7P$', FALSE),
    ('indspoti-F1', 'PARAMOUNT-JUAN', 'sofiavillota80@gmail.com', '2024-10-03', '(SKHrj7Gj2QGtTE', FALSE),
    ('indspoti-F2', 'PARAMOUNT-JUAN', 'spoti674@tapi.re', '2024-10-02', 'pablin7710', FALSE),
    ('indspoti-F4', 'PARAMOUNT-JUAN', 'spoti98@pwpwa.com', '2024-10-19', 'diciembre', FALSE),
    ('indspoti-F5', 'PARAMOUNT-JUAN', 'spoti82@pwpwa.com', '1900-01-01', 'legopoli7P$', FALSE),
    ('indspoti-F6', 'PARAMOUNT-JUAN', 'miaarcentalesh@gmail.com', '1900-01-01', '2006151JkJm', FALSE),
    ('indspoti-F7', 'PARAMOUNT-JUAN', 'cahroman1990@gmail.com', '2024-08-30', 'legopoli7P$', FALSE),
    ('indspoti-F10', 'PARAMOUNT-JUAN', 'josueh374@gmail.com', '2024-09-25', 'combo2024', FALSE),
    ('indspoti-F11', 'PARAMOUNT-JUAN', 'paulethvillavicencio2008@gmail.com', '2024-09-27', 'messigoat10', FALSE);


--VENTAS
INSERT INTO VENTAS(IDEMPLEADO, IDCLIENTE) VALUES
(1, 1),
(2, 2),
(1, 3),
(1, 4),
(2, 5);

INSERT INTO DETALLES_VENTAS(IDVENTA,IDCUENTA,PERFIL,FECHAVENC,MONTO,ACTIVO)
VALUES
('FAC006-07112024','DISNEY-1',1,'07-12-2024',3.25,TRUE),
('FAC006-07112024','NETFLIX-1',1,'07-12-2024',3,TRUE),
('FAC006-07112024','MAX-1',1,'07-12-2024',2,TRUE),
('FAC007-07112024','DISNEY-1',2,'07-12-2024',3.5,TRUE),
('FAC007-07112024','NETFLIX-1',2,'07-12-2024',3.25,TRUE),
('FAC007-07112024','MAX-1',2,'07-12-2024',2,TRUE),
('FAC008-07112024','DISNEY-1',3,'07-12-2024',3,TRUE),
('FAC008-07112024','NETFLIX-1',3,'07-12-2024',3,TRUE),
('FAC009-07112024','MAGIS-1',1,'07-12-2024',3,TRUE);

INSERT INTO COSTOS(IDCUENTA,MONTO,DESCRIPCION) VALUES
('NETFLIX-1',7.25,'COMPRADO SIN PROBLEMAS'),
('NETFLIX-2',7.25,'COMPRADO SIN PROBLEMAS'),
('NETFLIX-3',7.25,'COMPRADO SIN PROBLEMAS'),
('DISNEY-1',6,'COMPRADO SIN PROBLEMAS'),
('DISNEY-2',6,'COMPRADO SIN PROBLEMAS'),
('MAX-1',2.5,'COMPRADO SIN PROBLEMAS'),
('MAX-2',2.5,'COMPRADO SIN PROBLEMAS');



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
CREATE TRIGGER trg_actualizar_total_venta
AFTER INSERT OR UPDATE OR DELETE ON DETALLES_VENTA
FOR EACH ROW
EXECUTE FUNCTION actualizar_total_venta();


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

CREATE TRIGGER trg_generar_idventa
BEFORE INSERT ON VENTAS
FOR EACH ROW
WHEN (NEW.IDVENTA IS NULL)
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
CREATE OR REPLACE TRIGGER TG_insertar_perfiles
AFTER INSERT ON Cuentas
FOR EACH ROW
EXECUTE FUNCTION insertar_perfiles();






--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
-- VISTAS
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh
--hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh



--VISTA CUENTAS PERFILES GENERAL
CREATE OR REPLACE VIEW vista_cuentas_perfiles AS
SELECT 
    c.IDCUENTA,
    c.USUARIO,
    obtener_costo_mes_actual(c.IDCUENTA) AS costo_mes_actual,
    c.FECHAVENC AS fecha_vencimiento,
    -- Llamadas a la función para contar usuarios por perfil
    contar_usuarios_perfil(c.IDCUENTA, 1) AS P1,
    contar_usuarios_perfil(c.IDCUENTA, 2) AS P2,
    contar_usuarios_perfil(c.IDCUENTA, 3) AS P3,
    contar_usuarios_perfil(c.IDCUENTA, 4) AS P4,
    contar_usuarios_perfil(c.IDCUENTA, 5) AS P5,
    contar_usuarios_perfil(c.IDCUENTA, 6) AS P6,
    contar_usuarios_perfil(c.IDCUENTA, 7) AS P7,
    -- Total de usuarios activos
    contar_usuarios_activos(c.IDCUENTA) AS total_usuarios_activos
FROM 
    CUENTAS c;


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
	calcular_total_pagado_mes(
        U.IDCLIENTE, 
        CAST(EXTRACT(MONTH FROM CURRENT_DATE) AS INTEGER), 
        CAST(EXTRACT(YEAR FROM CURRENT_DATE) AS INTEGER)
    ) AS FACTURADO
FROM USUARIOS_ACTIVOS U
GROUP BY U.IDCLIENTE, U.NOMBRE_CLIENTE;

create or replace view view_valores as
select v.idval,v.idser,p.nombrepro,v.costoval,v.pantmaxval,v.mesesval
from valores v
join proveedores p on p.idpro=v.idpro;



CREATE TABLE contabilidad (
    idcon SERIAL PRIMARY KEY,            -- idcon como clave primaria, autoincremental
    mes INTEGER DEFAULT EXTRACT(MONTH FROM CURRENT_DATE),  -- mes por defecto es el mes actual
    año INTEGER DEFAULT EXTRACT(YEAR FROM CURRENT_DATE),  -- año por defecto es el año actual
    detalle TEXT,                         -- detalle de la transacción
    num_cuentas INTEGER,                  -- número de cuentas involucradas
    num_usuarios INTEGER,                 -- número de usuarios involucrados
    ingresos DECIMAL(15, 2),              -- ingresos (con 2 decimales para centavos)
    costos DECIMAL(15, 2),                -- costos (con 2 decimales para centavos)
    ganancias DECIMAL(15, 2),             -- ganancias (con 2 decimales)
    renta DECIMAL(5, 2)                  -- renta (con 2 decimales)
);

-- Crear un trigger para calcular las ganancias automáticamente
CREATE OR REPLACE FUNCTION calcular_ganancias() 
RETURNS TRIGGER AS $$
BEGIN
    NEW.ganancias := NEW.ingresos - NEW.costos;  -- calcula las ganancias
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Crear un trigger que se activa antes de insertar un registro
CREATE TRIGGER before_insert_contabilidad
BEFORE INSERT ON contabilidad
FOR EACH ROW
EXECUTE FUNCTION calcular_ganancias();
