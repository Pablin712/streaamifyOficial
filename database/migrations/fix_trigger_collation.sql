DROP TRIGGER IF EXISTS trg_generar_idcue;

DELIMITER $$

CREATE TRIGGER trg_generar_idcue
BEFORE INSERT ON cuentas
FOR EACH ROW
BEGIN
    DECLARE servicio_prefix VARCHAR(20) COLLATE utf8mb4_unicode_ci;
    DECLARE tipo_prefix VARCHAR(10) COLLATE utf8mb4_unicode_ci;
    DECLARE siguiente_numero INT;
    DECLARE nuevo_idcue VARCHAR(50) COLLATE utf8mb4_unicode_ci;

    IF NEW.idcue IS NULL OR NEW.idcue = '' THEN
        SELECT idser INTO servicio_prefix
        FROM valores
        WHERE idval = NEW.idval;

        IF NEW.tipo_cuenta = 'individual' THEN
            SET tipo_prefix = 'IND.';
        ELSE
            SET tipo_prefix = '';
        END IF;

        SELECT COALESCE(MAX(
            CAST(
                SUBSTRING_INDEX(
                    REPLACE(idcue, CONCAT(tipo_prefix, servicio_prefix, '-'), ''),
                    '.',
                    1
                ) AS UNSIGNED
            )
        ), 0) + 1
        INTO siguiente_numero
        FROM cuentas
        WHERE idcue LIKE CONCAT(tipo_prefix, servicio_prefix, '-%')
          AND idcue NOT LIKE '%Atencion';

        SET nuevo_idcue = CONCAT(tipo_prefix, servicio_prefix, '-', siguiente_numero);
        SET NEW.idcue = nuevo_idcue;
    END IF;
END$$

DELIMITER ;
