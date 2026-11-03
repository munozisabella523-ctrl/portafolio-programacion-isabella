-- ARCHIVO: actualizacion_inicio_limpio.sql
-- PARA QUÉ SIRVE: Actualiza las tres curiosidades iniciales que se muestran en ¿Sabías que? y Datos científicos.
-- Son instrucciones de base de datos, no una pantalla web.
-- Sustituye únicamente las tres curiosidades iniciales. Conserva sus identificadores.

-- Agrupa actualizaciones para confirmarlas juntas.
START TRANSACTION;

-- Fuente: https://www.esa.int/Science_Exploration/Human_and_Robotic_Exploration/Research/Tiny_animals_survive_exposure_to_space

-- Actualiza solo los registros que cumplen WHERE y conserva los demás.
UPDATE datos_cientificos
SET titulo='Animales que sobrevivieron al espacio',
    descripcion='En 2007, algunos tardígrados deshidratados sobrevivieron a la exposición al vacío del espacio. Al volver a la Tierra y recibir agua, recuperaron su actividad. Estos animales miden alrededor de un milímetro.', categoria='Biología'
WHERE titulo='Tres corazones, un pulpo'
    AND creado_por IS NULL;

-- Fuente: https://science.nasa.gov/venus/venus-facts/

-- Actualiza solo los registros que cumplen WHERE y conserva los demás.
UPDATE datos_cientificos
SET titulo='Venus cumple años antes de terminar un giro',
    descripcion='Venus tarda unos 243 días terrestres en girar sobre sí mismo, pero solo 225 en dar una vuelta al Sol. ¡Completa un año antes que una rotación!', categoria='Astronomía'
WHERE titulo='Un viaje de ocho minutos'
    AND creado_por IS NULL;

-- Fuente: https://science.nasa.gov/universe/stories/quick-reads/neutron-stars-are-weird/

-- Actualiza solo los registros que cumplen WHERE y conserva los demás.
UPDATE datos_cientificos
SET titulo='Una montaña en un terrón de azúcar',
    descripcion='La materia de una estrella de neutrones es tan densa que un volumen del tamaño de un terrón de azúcar tendría una masa de alrededor de mil millones de toneladas: comparable a una montaña.', categoria='Astronomía'
WHERE titulo='El agua se transforma'
    AND creado_por IS NULL;


-- Confirma las actualizaciones de la transacción.
COMMIT;