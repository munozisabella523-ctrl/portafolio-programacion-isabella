-- ARCHIVO: migracion_mundo_ciencia_v2.sql
-- PARA QUÉ SIRVE: Actualiza la estructura existente para roles, moderación, evaluaciones y robótica. Se ejecuta mediante tools/migrar.php.
-- Son instrucciones de base de datos, no una pantalla web.
-- MariaDB 10.4+ (XAMPP). Seleccionar la base y respaldarla antes. Reejecutable, sin eliminar datos.
-- Configura la codificación o prepara datos para actualizar la estructura.
SET NAMES utf8mb4;


-- Adapta columnas o relaciones a la versión actual. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
ALTER TABLE usuarios MODIFY rol VARCHAR(30) NOT NULL DEFAULT 'estudiante';


-- Actualiza solo los registros que cumplen WHERE y conserva los demás.
UPDATE usuarios
SET rol='administrador'
WHERE rol='admin';


-- Actualiza solo los registros que cumplen WHERE y conserva los demás.
UPDATE usuarios
SET rol='estudiante'
WHERE rol='usuario';


-- Adapta columnas o relaciones a la versión actual. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
ALTER TABLE usuarios MODIFY rol ENUM('estudiante',
                                    'profesor', 'administrador', 'super_administrador') NOT NULL DEFAULT 'estudiante';


-- Adapta columnas o relaciones a la versión actual. Cuentas, datos personales, roles y estado. Se usan en acceso, perfil y gestión de usuarios.
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL;


-- Adapta columnas o relaciones a la versión actual. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
ALTER TABLE experimentos MODIFY estado VARCHAR(30) NOT NULL DEFAULT 'pendiente';


-- Actualiza solo los registros que cumplen WHERE y conserva los demás.
UPDATE experimentos
SET estado='aprobado'
WHERE estado='publicado';


-- Adapta columnas o relaciones a la versión actual. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
ALTER TABLE experimentos MODIFY estado ENUM('borrador',
                                           'pendiente', 'aprobado', 'rechazado', 'cambios_solicitados',
                                           'oculto') NOT NULL DEFAULT 'pendiente';


-- Adapta columnas o relaciones a la versión actual. Proyectos científicos y su estado de publicación. Se muestran en Experimentos y Mis proyectos.
ALTER TABLE experimentos ADD COLUMN IF NOT EXISTS destacado TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS moderado_por INT NULL,
                                                                                                                                     ADD COLUMN IF NOT EXISTS fecha_moderacion DATETIME NULL,
                                                                                                                                                                                        ADD COLUMN IF NOT EXISTS observacion_moderacion TEXT NULL;


-- Configura la codificación o prepara datos para actualizar la estructura.
SET @mc_fk =
    (SELECT IF(COUNT(*)=0,

                'ALTER TABLE experimentos ADD CONSTRAINT fk_experimentos_moderador FOREIGN KEY(moderado_por) REFERENCES usuarios(id) ON DELETE SET NULL', 'SELECT 1')
     FROM information_schema.TABLE_CONSTRAINTS
     WHERE CONSTRAINT_SCHEMA=DATABASE()
         AND TABLE_NAME='experimentos'
         AND CONSTRAINT_NAME='fk_experimentos_moderador');

-- Prepara una instrucción de mantenimiento según la estructura existente.
PREPARE mc_stmt
FROM @mc_fk;

-- Ejecuta la instrucción de mantenimiento preparada.
EXECUTE mc_stmt;

-- Libera la instrucción preparada después de utilizarla.
DEALLOCATE PREPARE mc_stmt;


-- Crea un índice para acelerar consultas de publicaciones por estado y fecha.
CREATE INDEX IF NOT EXISTS idx_experimentos_publicos ON experimentos(estado,
                                                            destacado, fecha_creacion);


-- Crea experimento_imagenes: Fotografías y orden de cada experimento. Alimenta portadas y galerías.
CREATE TABLE IF NOT EXISTS experimento_imagenes
    (
    -- Identificador único del registro.
    id INT AUTO_INCREMENT PRIMARY KEY, 
    -- Vincula el registro con la cuenta o contenido indicado por experimento_id.
    experimento_id INT NOT NULL, 
    -- Referencia al archivo almacenado que se entrega mediante archivo.php.
    archivo VARCHAR(255) NOT NULL, 
    -- Posición de la imagen en la galería.
    orden INT NOT NULL DEFAULT 0,
                                                                                                                               
    -- Fecha de creación del registro.
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                                                                                                                                                        
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    UNIQUE KEY uq_exp_archivo(experimento_id,

                                                                                                                                                                                       archivo),
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(experimento_id) REFERENCES experimentos(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Añade o traslada los datos indicados; IGNORE evita repetir registros restringidos por una clave única.
INSERT
IGNORE INTO experimento_imagenes(experimento_id, archivo)
SELECT id,archivo
FROM experimentos
WHERE archivo REGEXP '\\.(jpg|jpeg|png|webp|gif)$';


-- Crea calificaciones: Notas y comentarios del profesor vinculados a proyecto y estudiante. Se ven en Evaluaciones y Mi perfil.
CREATE TABLE IF NOT EXISTS calificaciones
    (
    -- Identificador único del registro.
    id INT AUTO_INCREMENT PRIMARY KEY, 
    -- Vincula el registro con la cuenta o contenido indicado por experimento_id.
    experimento_id INT NOT NULL, 
    -- Vincula el registro con la cuenta o contenido indicado por profesor_id.
    profesor_id INT NOT NULL, 
    -- Vincula el registro con la cuenta o contenido indicado por estudiante_id.
    estudiante_id INT NOT NULL,
                                                                                                                 
    -- Valor numérico mostrado en las evaluaciones.
    nota DECIMAL(3,

                                                                                                                          2) NOT NULL CHECK(nota BETWEEN 1 AND 5),
                                                                                                                                      
    -- Texto de evaluación o participación.
    comentario TEXT NOT NULL,
                                                                                                                                                      
    -- Fecha de creación del registro.
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                                                                                                                                                                               
    -- Fecha del último cambio.
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                                                                                                                                                                                                                                                         
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    UNIQUE KEY uq_calificacion(experimento_id,

                                                                                                                                                                                                                                                                                        profesor_id),
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(experimento_id) REFERENCES experimentos(id) ON DELETE CASCADE,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(profesor_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(estudiante_id) REFERENCES usuarios(id) ON DELETE RESTRICT) ENGINE=InnoDB DEFAULT
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Crea moderaciones: Decisiones y observaciones de revisión de experimentos. Se utilizan en la moderación.
CREATE TABLE IF NOT EXISTS moderaciones
    (
    -- Identificador único del registro.
    id INT AUTO_INCREMENT PRIMARY KEY, 
    -- Vincula el registro con la cuenta o contenido indicado por experimento_id.
    experimento_id INT NOT NULL, 
    -- Vincula el registro con la cuenta o contenido indicado por administrador_id.
    administrador_id INT NOT NULL, 
    -- Operación guardada en el historial.
    accion VARCHAR(30) NOT NULL,
                                                                                                                       
    -- Explicación de una decisión de revisión.
    observacion TEXT NOT NULL,
                                                                                                                                        
    -- Momento de la operación.
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(experimento_id) REFERENCES experimentos(id) ON DELETE CASCADE,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(administrador_id) REFERENCES usuarios(id) ON DELETE RESTRICT) ENGINE=InnoDB DEFAULT
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Crea proyectos_robotica: Proyectos del club, sus responsables, portada y estado de revisión.
CREATE TABLE IF NOT EXISTS proyectos_robotica
    (
    -- Identificador único del registro.
    id INT AUTO_INCREMENT PRIMARY KEY, 
    -- Título de la tarjeta o del detalle.
    titulo VARCHAR(160) NOT NULL, 
    -- Explicación mostrada al abrir el contenido.
    descripcion TEXT NOT NULL, 
    -- Vincula el registro con la cuenta o contenido indicado por creador_id.
    creador_id INT NOT NULL,
                                                                                                                
    -- Estado de visibilidad, acceso o revisión según esta tabla.
    estado ENUM('idea',

                                                                                                                           'prototipo',
                                                                                                                           'programacion',
                                                                                                                           'pruebas',
                                                                                                                           'terminado') NOT NULL DEFAULT 'idea',
                                                                                                                                                         
    -- Estado de revisión del proyecto del club.
    moderacion ENUM('pendiente',

                                                                                                                                                                        'aprobado',
                                                                                                                                                                        'rechazado',
                                                                                                                                                                        'oculto') NOT NULL DEFAULT 'pendiente',
                                                                                                                                                                                                   
    -- Imagen principal del proyecto de robótica.
    portada VARCHAR(255) NULL,
                                                                                                                                                                                                                        
    -- Dato equipo del registro; su tipo y restricciones se indican a continuación.
    equipo VARCHAR(120) NOT NULL,
                                                                                                                                                                                                                                            
    -- Fecha de creación del registro.
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                                                                                                                                                                                                                                                                     
    -- Fecha del último cambio.
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(creador_id) REFERENCES usuarios(id) ON DELETE RESTRICT, 
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    INDEX(moderacion,
                                                                             fecha_creacion)) ENGINE=InnoDB DEFAULT
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Crea miembros_robotica: Relaciones entre cuentas y equipos del club. Se muestran como integrantes.
CREATE TABLE IF NOT EXISTS miembros_robotica
    (
    -- Vincula el registro con la cuenta o contenido indicado por proyecto_id.
    proyecto_id INT NOT NULL, 
    -- Vincula el registro con la cuenta o contenido indicado por usuario_id.
    usuario_id INT NOT NULL, 
    -- Clave primaria: identifica de forma única cada registro de esta tabla.
    PRIMARY KEY(proyecto_id,usuario_id),
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(proyecto_id) REFERENCES proyectos_robotica(id) ON DELETE CASCADE,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Crea avances_robotica: Entradas de progreso de los proyectos de robótica. Se muestran en su detalle.
CREATE TABLE IF NOT EXISTS avances_robotica
    (
    -- Identificador único del registro.
    id INT AUTO_INCREMENT PRIMARY KEY, 
    -- Vincula el registro con la cuenta o contenido indicado por proyecto_id.
    proyecto_id INT NOT NULL, 
    -- Vincula el registro con la cuenta o contenido indicado por usuario_id.
    usuario_id INT NOT NULL, 
    -- Título de la tarjeta o del detalle.
    titulo VARCHAR(160) NOT NULL,
                                                                                                               
    -- Explicación mostrada al abrir el contenido.
    descripcion TEXT NOT NULL,
                                                                                                                                
    -- Dato fecha_avance del registro; su tipo y restricciones se indican a continuación.
    fecha_avance DATE NOT NULL,
                                                                                                                                                  
    -- Estado de visibilidad, acceso o revisión según esta tabla.
    estado ENUM('pendiente',

                                                                                                                                                             'visible',
                                                                                                                                                             'oculto') NOT NULL DEFAULT 'pendiente',
                                                                                                                                                                                        
    -- Fecha de creación del registro.
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(proyecto_id) REFERENCES proyectos_robotica(id) ON DELETE CASCADE,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT) ENGINE=InnoDB DEFAULT
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Crea avances_robotica_imagenes: Fotografías asociadas a cada avance del club.
CREATE TABLE IF NOT EXISTS avances_robotica_imagenes
    (
    -- Identificador único del registro.
    id INT AUTO_INCREMENT PRIMARY KEY, 
    -- Vincula el registro con la cuenta o contenido indicado por avance_id.
    avance_id INT NOT NULL, 
    -- Referencia al archivo almacenado que se entrega mediante archivo.php.
    archivo VARCHAR(255) NOT NULL, 
    -- Posición de la imagen en la galería.
    orden INT NOT NULL DEFAULT 0,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(avance_id) REFERENCES avances_robotica(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Crea comentarios: Mensajes asociados a experimentos o avances, con autor y estado de visibilidad.
CREATE TABLE IF NOT EXISTS comentarios
    (
    -- Identificador único del registro.
    id INT AUTO_INCREMENT PRIMARY KEY, 
    -- Vincula el registro con la cuenta o contenido indicado por usuario_id.
    usuario_id INT NOT NULL, 
    -- Vincula el registro con la cuenta o contenido indicado por experimento_id.
    experimento_id INT NULL, 
    -- Vincula el registro con la cuenta o contenido indicado por avance_robotica_id.
    avance_robotica_id INT NULL,
                                                                                                                 
    -- Texto de evaluación o participación.
    comentario TEXT NOT NULL,
                                                                                                                                 
    -- Estado de visibilidad, acceso o revisión según esta tabla.
    estado ENUM('visible',

                                                                                                                                            'pendiente',
                                                                                                                                            'oculto') NOT NULL DEFAULT 'visible',
                                                                                                                                                                       
    -- Fecha de creación del registro.
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                                                                                                                                                                                                
    -- Fecha del último cambio.
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                                                                                                                                                                                                                                                                          
    -- Dato CHECK del registro; su tipo y restricciones se indican a continuación.
    CHECK((experimento_id IS NULL) <> (avance_robotica_id IS NULL)),
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(experimento_id) REFERENCES experimentos(id) ON DELETE CASCADE,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(avance_robotica_id) REFERENCES avances_robotica(id) ON DELETE CASCADE,
                                                                               
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    INDEX(estado,
                                                                                   fecha_creacion)) ENGINE=InnoDB DEFAULT
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Crea notificaciones: Avisos destinados a una cuenta. Se consultan desde Notificaciones.
CREATE TABLE IF NOT EXISTS notificaciones
    (
    -- Identificador único del registro.
    id INT AUTO_INCREMENT PRIMARY KEY, 
    -- Vincula el registro con la cuenta o contenido indicado por usuario_id.
    usuario_id INT NOT NULL, 
    -- Clase de aviso o contenido.
    tipo VARCHAR(40) NOT NULL, 
    -- Título de la tarjeta o del detalle.
    titulo VARCHAR(160) NOT NULL,
                                                                                                                
    -- Contenido del aviso de Notificaciones.
    mensaje TEXT NOT NULL,
                                                                                                                             
    -- Destino de un aviso.
    url VARCHAR(255) NOT NULL,
                                                                                                                                              
    -- Indica si el aviso está marcado como leído.
    leida TINYINT(1) NOT NULL DEFAULT 0,
                                                                                                                                                                                
    -- Fecha de creación del registro.
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE, 
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    INDEX(usuario_id,
                                                                            leida, fecha_creacion)) ENGINE=InnoDB DEFAULT
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Crea datos_cientificos: Curiosidades, categoría, imagen y visibilidad. Alimentan ¿Sabías que? y Datos científicos.
CREATE TABLE IF NOT EXISTS datos_cientificos
    (
    -- Identificador único del registro.
    id INT AUTO_INCREMENT PRIMARY KEY, 
    -- Título de la tarjeta o del detalle.
    titulo VARCHAR(160) NOT NULL UNIQUE, 
    -- Explicación mostrada al abrir el contenido.
    descripcion TEXT NOT NULL, 
    -- Área temática de las etiquetas y proyectos relacionados.
    categoria VARCHAR(80) NOT NULL,
                                                                                                                              
    -- Imagen de una curiosidad.
    imagen VARCHAR(255) NULL,
                                                                                                                                                  
    -- Indica si el contenido está habilitado.
    activo TINYINT(1) NOT NULL DEFAULT 1,
                                                                                                                                                                                     
    -- Vincula el registro con la cuenta o contenido indicado por creado_por.
    creado_por INT NULL,
                                                                                                                                                                                                    
    -- Fecha de creación del registro.
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(creado_por) REFERENCES usuarios(id) ON DELETE
     SET NULL) ENGINE=InnoDB DEFAULT
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Añade o traslada los datos indicados; IGNORE evita repetir registros restringidos por una clave única.
INSERT
IGNORE INTO datos_cientificos(titulo, descripcion, categoria)
VALUES ('Animales que sobrevivieron al espacio','En 2007, algunos tardígrados deshidratados sobrevivieron a la exposición al vacío del espacio. Al volver a la Tierra y recibir agua, recuperaron su actividad. Estos animales miden alrededor de un milímetro.','Biología'),
       ('Venus cumple años antes de terminar un giro','Venus tarda unos 243 días terrestres en girar sobre sí mismo, pero solo 225 en dar una vuelta al Sol. ¡Completa un año antes que una rotación!','Astronomía'),
       ('Una montaña en un terrón de azúcar','La materia de una estrella de neutrones es tan densa que un volumen del tamaño de un terrón de azúcar tendría una masa de alrededor de mil millones de toneladas: comparable a una montaña.','Astronomía');


-- Crea favoritos: Proyectos guardados por cada usuario. Alimentan Mis favoritos.
CREATE TABLE IF NOT EXISTS favoritos
    (
    -- Vincula el registro con la cuenta o contenido indicado por usuario_id.
    usuario_id INT NOT NULL, 
    -- Vincula el registro con la cuenta o contenido indicado por experimento_id.
    experimento_id INT NOT NULL, 
    -- Clave primaria: identifica de forma única cada registro de esta tabla.
    PRIMARY KEY(usuario_id,experimento_id),
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(experimento_id) REFERENCES experimentos(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Crea reconocimientos: Distinciones asociadas a experimentos. Se muestran en el proyecto y en la feria.
CREATE TABLE IF NOT EXISTS reconocimientos
    (
    -- Identificador único del registro.
    id INT AUTO_INCREMENT PRIMARY KEY, 
    -- Vincula el registro con la cuenta o contenido indicado por experimento_id.
    experimento_id INT NOT NULL, 
    -- Título de la tarjeta o del detalle.
    titulo VARCHAR(120) NOT NULL, 
    -- Explicación mostrada al abrir el contenido.
    descripcion TEXT NOT NULL,
                                                                                                                    
    -- Dato icono del registro; su tipo y restricciones se indican a continuación.
    icono VARCHAR(20) NOT NULL DEFAULT '★',
                                                                                                                                                       
    -- Momento de la operación.
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
     
    -- Restricción: protege la identificación, evita duplicados o relaciona registros entre tablas.
    FOREIGN KEY(experimento_id) REFERENCES experimentos(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Crea configuracion: Opciones generales guardadas desde Administración > Sistema.
CREATE TABLE IF NOT EXISTS configuracion (
    -- Identificador de una opción o del control interno de acceso.
    clave VARCHAR(60) PRIMARY KEY, 
    -- Contenido de una opción de configuración.
    valor TEXT NOT NULL) ENGINE=InnoDB DEFAULT
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Añade o traslada los datos indicados; IGNORE evita repetir registros restringidos por una clave única.
INSERT
IGNORE INTO configuracion
VALUES('mensaje_comunidad','Un espacio donde estudiantes y profesores convierten ideas en proyectos que inspiran.');


-- Crea intentos_login: Control temporal de intentos de acceso. Funciona internamente en Iniciar sesión.
CREATE TABLE IF NOT EXISTS intentos_login (
    -- Identificador de una opción o del control interno de acceso.
    clave CHAR(64) PRIMARY KEY, 
    -- Cantidad de intentos de acceso contabilizados.
    intentos INT NOT NULL DEFAULT 0,
                                                                                                     
    -- Inicio del período de control de intentos.
    desde DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Crea versiones_esquema: Versiones de la estructura instaladas. Registro técnico, sin pantalla pública.
CREATE TABLE IF NOT EXISTS versiones_esquema (
    -- Versión de estructura instalada.
    version VARCHAR(60) PRIMARY KEY, 
    -- Momento de la operación.
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP);


-- Añade o traslada los datos indicados; IGNORE evita repetir registros restringidos por una clave única.
INSERT
IGNORE INTO versiones_esquema(version)
VALUES('mundo_ciencia_v2');