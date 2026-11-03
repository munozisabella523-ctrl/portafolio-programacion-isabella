# Mundo Ciencia

Código fuente de la aplicación académica Mundo Ciencia, recibida en el archivo ZIP del proyecto.

## Requisitos

- Servidor web con PHP y extensión MySQLi.
- Servidor MySQL con una base de datos creada a partir del esquema SQL incluido en esta carpeta.
- Configuración de conexión mediante las variables de entorno `MC_DB_HOST`, `MC_DB_USER`, `MC_DB_PASS`, `MC_DB_NAME` y `MC_DB_PORT`.

El punto de entrada de la aplicación es `inicio.php`. GitHub Pages no ejecuta PHP ni MySQL; este código se puede consultar aquí, pero para probarlo en línea se debe usar un hosting compatible y configurar su base de datos.

El volcado ciencia (2).sql contiene registros de la base de datos y se conserva solo en la copia local; no se publica. Los archivos de uploads/ también quedan fuera del repositorio porque son contenido generado por usuarios.
