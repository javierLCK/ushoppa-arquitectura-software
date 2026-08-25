# Ushoppa — Proyecto E-commerce

Documentos, diagramas y código del proyecto de comercio electrónico Ushoppa, desarrollado para NexoMarket como parte de la asignatura Arquitectura de Software (ISI602 - NRC 4723).

## Integrantes del equipo

- Javier Pizarro
- Daniel Bozo
- Johao Silva

## Estructura del repositorio

```
docs/               Documentos del proyecto (requerimientos, fichas de etapa)
diagramas/          Casos de uso, clases, ER, arquitectura en capas
database/           Script SQL de la base de datos
public/             Código fuente (PHP, HTML, CSS, JS)
public/includes/    Conexión a base de datos y funciones comunes
```

## Requisitos

- XAMPP (Apache, PHP, MySQL)
- Navegador web moderno

## Instalación local

1. Clonar este repositorio dentro de la carpeta `htdocs` de XAMPP.
2. Importar `database/ushoppa.sql` en phpMyAdmin.
3. Copiar `public/includes/config.example.php` a `public/includes/config.php` y completar las credenciales locales de la base de datos.
4. Iniciar Apache y MySQL desde el panel de XAMPP.
5. Acceder desde el navegador a `http://localhost/ushoppa-arquitectura-software/public`.

## Tecnologías

- Backend: PHP
- Base de datos: MySQL
- Frontend: HTML5, CSS3, JavaScript
- Entorno local: XAMPP

## Estado del proyecto

En desarrollo — etapa de diagnóstico y diseño de arquitectura.
