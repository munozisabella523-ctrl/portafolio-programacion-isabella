<?php
/*
 * ARCHIVO: administracion/historial_actividad.php
 * PARA QUÉ SIRVE: Consulta registros de actividad de usuarios, administradores y experimentos.
 * DÓNDE SE UTILIZA: Administración > Historial.
 * Los comentarios explican el código y no aparecen como contenido de la página.
 */
// Carga las funciones o la conexión compartida que necesita este archivo.
require_once __DIR__ . '/../includes/funciones.php';
// Recupera la cuenta que visita esta pantalla y sus permisos.
$viewer = exigir_administrador();
// Define el título de la pestaña y, donde corresponde, del encabezado de gestión.
$pageTitle = 'Historial de Actividad';
// Conserva el identificador de contexto utilizado por esta pantalla.
$panelType = 'admin';
// Prepara $db con el resultado de esta expresión para utilizarlo en las siguientes instrucciones.
$db = base_datos();
// Conserva una función de compatibilidad vacía; el esquema se actualiza con la herramienta de migración.
asegurar_esquema_experimentos();
// Recupera la lista de registros devueltos por la consulta.
$userHistory = base_datos()->query('SELECT h.*, u.nombre, u.correo FROM historial_usuarios h INNER JOIN usuarios u ON u.id = h.usuario_id ORDER BY h.fecha DESC LIMIT 80')->fetch_all(MYSQLI_ASSOC);
// Recupera la lista de registros devueltos por la consulta.
$adminHistory = base_datos()->query('SELECT h.*, a.nombre AS admin_nombre, u.nombre AS afectado FROM historial_admin h INNER JOIN usuarios a ON a.id = h.admin_id LEFT JOIN usuarios u ON u.id = h.usuario_afectado_id ORDER BY h.fecha DESC LIMIT 80')->fetch_all(MYSQLI_ASSOC);
// Consulta los registros. El resultado se utiliza en este bloque.
$experimentHistory = $db->query('SELECT h.*, u.nombre, e.titulo
    FROM historial_experimentos h
    INNER JOIN usuarios u ON u.id = h.usuario_id
    LEFT JOIN experimentos e ON e.id = h.experimento_id
    ORDER BY h.fecha DESC
    LIMIT 80')->fetch_all(MYSQLI_ASSOC);
// Incluye el encabezado y la navegación que se muestran encima del contenido.
require_once __DIR__ . '/../includes/encabezado_gestion.php';
?>
<!-- Agrupa un apartado de contenido. Ubicación: Administración > Historial. -->
<section class="panel-grid">
    <!-- Presenta una tarjeta o registro individual. Ubicación: Administración > Historial. -->
    <article class="info-card">
        <!-- Muestra el título de un apartado. Ubicación: Administración > Historial. -->
        <h2>Historial de Experimentos</h2>
        <div class="table-wrap">
            <!-- Organiza registros en filas y columnas. Ubicación: Administración > Historial. -->
            <table>
                <!-- Define los encabezados de la tabla. Ubicación: Administración > Historial. -->
                <thead><tr><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>Usuario</th><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>Accion</th><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>Experimento</th><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>ID</th><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>Fecha</th><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>IP</th></tr></thead>
                <!-- Contiene las filas de datos de la tabla. Ubicación: Administración > Historial. -->
                <tbody>
                <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach ($experimentHistory as $row): ?>
                    <tr>
                        <td><?= e($row['nombre']) ?></td>
                        <td><?= e($row['accion']) ?></td>
                        <td><?= e($row['titulo'] ?? 'Eliminado') ?></td>
                        <td><?= e((string) ($row['experimento_referencia_id'] ?? $row['experimento_id'] ?? 'No disponible')) ?></td>
                        <td><?= e($row['fecha']) ?></td>
                        <td><?= e($row['ip']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
    <!-- Presenta una tarjeta o registro individual. Ubicación: Administración > Historial. -->
    <article class="info-card">
        <!-- Muestra el título de un apartado. Ubicación: Administración > Historial. -->
        <h2>Historial de Actividad de Usuarios</h2>
        <div class="table-wrap">
            <!-- Organiza registros en filas y columnas. Ubicación: Administración > Historial. -->
            <table>
                <!-- Define los encabezados de la tabla. Ubicación: Administración > Historial. -->
                <thead><tr><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>Usuario</th><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>Accion</th><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>Fecha</th><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>IP</th><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>Navegador</th></tr></thead>
                <!-- Contiene las filas de datos de la tabla. Ubicación: Administración > Historial. -->
                <tbody>
                <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach ($userHistory as $row): ?>
                    <tr>
                        <td><?= e($row['nombre']) ?><br><span><?= e($row['correo']) ?></span></td>
                        <td><?= e($row['accion']) ?></td>
                        <td><?= e($row['fecha']) ?></td>
                        <td><?= e($row['ip']) ?></td>
                        <td><?= e($row['navegador']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
    <!-- Presenta una tarjeta o registro individual. Ubicación: Administración > Historial. -->
    <article class="info-card">
        <!-- Muestra el título de un apartado. Ubicación: Administración > Historial. -->
        <h2>Historial Administrativo</h2>
        <div class="table-wrap">
            <!-- Organiza registros en filas y columnas. Ubicación: Administración > Historial. -->
            <table>
                <!-- Define los encabezados de la tabla. Ubicación: Administración > Historial. -->
                <thead><tr><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>Administrador</th><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>Accion</th><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>Usuario afectado</th><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>Fecha</th><!-- Identifica una columna de la tabla. Ubicación: Administración > Historial. --><th>IP</th></tr></thead>
                <!-- Contiene las filas de datos de la tabla. Ubicación: Administración > Historial. -->
                <tbody>
                <?php /* Repite el componente siguiente para cada registro o posición de la lista. */ foreach ($adminHistory as $row): ?>
                    <tr>
                        <td><?= e($row['admin_nombre']) ?></td>
                        <td><?= e($row['accion']) ?></td>
                        <td><?= e($row['afectado'] ?? 'No aplica') ?></td>
                        <td><?= e($row['fecha']) ?></td>
                        <td><?= e($row['ip']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
<?php /* Inserta el footer con contactos y carga las interacciones. */ require_once __DIR__ . '/../includes/pie_pagina_gestion.php'; ?>
