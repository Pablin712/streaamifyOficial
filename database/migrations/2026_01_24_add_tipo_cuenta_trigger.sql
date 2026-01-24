-- =====================================================
-- TRIGGER PARA GENERAR idcue AUTOMÁTICAMENTE
-- =====================================================

-- Eliminar trigger anterior si existe
DROP TRIGGER IF EXISTS trg_generar_idcue;

DELIMITER $$

CREATE TRIGGER trg_generar_idcue
BEFORE INSERT ON cuentas
FOR EACH ROW
BEGIN
    DECLARE servicio_prefix VARCHAR(20);
    DECLARE tipo_prefix VARCHAR(10);
    DECLARE siguiente_numero INT;
    DECLARE nuevo_idcue VARCHAR(50);

    -- Solo generar si idcue está vacío o NULL
    IF NEW.idcue IS NULL OR NEW.idcue = '' THEN
        -- Obtener el servicio desde el valor
        SELECT idser INTO servicio_prefix
        FROM valores
        WHERE idval = NEW.idval;

        -- Determinar el prefijo según el tipo de cuenta
        IF NEW.tipo_cuenta = 'individual' THEN
            SET tipo_prefix = 'IND.';
        ELSE
            SET tipo_prefix = '';
        END IF;

        -- Buscar el siguiente número disponible para este servicio y tipo
        SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(idcue, '-', -1) AS UNSIGNED)), 0) + 1
        INTO siguiente_numero
        FROM cuentas
        WHERE idcue LIKE CONCAT(tipo_prefix, servicio_prefix, '%')
          AND idcue NOT LIKE '%Atencion';

        -- Generar el nuevo idcue
        SET nuevo_idcue = CONCAT(tipo_prefix, servicio_prefix, '-', siguiente_numero);

        -- Asignar el nuevo idcue
        SET NEW.idcue = nuevo_idcue;
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- MODIFICAR TABLA CUENTAS PARA AGREGAR TIPO_CUENTA
-- =====================================================

-- Agregar columna temporal para tipo de cuenta (completa/individual)
ALTER TABLE cuentas ADD COLUMN IF NOT EXISTS tipo_cuenta ENUM('completa', 'individual') DEFAULT 'completa';

-- =====================================================
-- NOTA:
-- Este trigger genera automáticamente el idcue basándose en:
-- - El servicio (obtenido del idval)
-- - El tipo de cuenta (completa o individual)
-- - El siguiente número secuencial disponible
--
-- Ejemplos:
-- - Cuenta completa de Netflix: NETFLIX-1, NETFLIX-2, NETFLIX-3
-- - Cuenta individual de Netflix: IND.NETFLIX-1, IND.NETFLIX-2
-- - Cuenta completa de Spotify: SPOTIFY-1, SPOTIFY-2
-- - Cuenta individual de Spotify: IND.SPOTIFY-1, IND.SPOTIFY-2
-- =====================================================
