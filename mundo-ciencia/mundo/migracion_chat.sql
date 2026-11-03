-- Módulo Chat: conversaciones (propiedad) y mensajes (contenido ordenado).
CREATE TABLE IF NOT EXISTS chat_conversaciones (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 usuario_id INT NULL,
 visitante_hash CHAR(64) NULL,
 rol_origen VARCHAR(24) NOT NULL,
 titulo VARCHAR(120) NOT NULL DEFAULT 'Nueva conversación',
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_chat_usuario (usuario_id, fecha_actualizacion),
 INDEX idx_chat_visitante (visitante_hash, fecha_actualizacion),
 CONSTRAINT fk_chat_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Chat: propietario, título y fechas de cada conversación';
CREATE TABLE IF NOT EXISTS chat_mensajes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 conversacion_id BIGINT UNSIGNED NOT NULL,
 rol ENUM('user','assistant','error') NOT NULL,
 contenido TEXT NOT NULL,
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_chat_mensajes (conversacion_id,id),
 CONSTRAINT fk_chat_conversacion FOREIGN KEY (conversacion_id) REFERENCES chat_conversaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Chat: preguntas, respuestas y errores, en orden cronológico';
