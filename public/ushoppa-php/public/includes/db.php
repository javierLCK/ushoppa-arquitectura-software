<?php
/**
 * db.php
 * -----------------------------------------------------------------------
 * Abre la conexión PDO a MySQL usando las credenciales de config.php
 * (que cada desarrollador crea localmente a partir de config.example.php
 * y NO se sube al repositorio; ver .gitignore).
 *
 * Expone la variable global $pdo, usada en todas las páginas para hacer
 * consultas preparadas: $pdo->prepare(...)->execute(...).
 */
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            // Las excepciones permiten detectar errores de BD con try/catch
            // (se usa en actions/checkout.php al crear el pedido).
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Fetch por defecto como array asociativo (['columna' => valor]).
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // Nota: en producción no debería mostrarse el mensaje de excepción tal
    // cual (puede filtrar detalles del servidor); para este proyecto
    // académico se deja explícito para facilitar el diagnóstico en clase.
    die('Error de conexión a la base de datos: ' . $e->getMessage());
}
