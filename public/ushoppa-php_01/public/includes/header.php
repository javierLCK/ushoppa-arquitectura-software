<?php
/**
 * includes/header.php
 * -----------------------------------------------------------------------
 * Cabecera común: define BASE_URL, imprime <head>, la barra superior de
 * promociones, el navbar (logo, links, buscador, carrito, sesión) y abre
 * la etiqueta <main>. Cada página lo incluye tras preparar sus datos:
 *
 *   $BASE_URL = '';         // '' en /public, '../' dentro de /vendor o /admin
 *   $pageTitle = 'Título';  // opcional, título de la pestaña
 *   require __DIR__ . '/includes/header.php';
 *
 * El <main> abierto aquí se cierra en includes/footer.php.
 */
require_once __DIR__ . '/functions.php';
$user = current_user();
$currentScreen = basename($_SERVER['SCRIPT_NAME']); // nombre del archivo actual, ej: "catalog.php"
$pageTitle = $pageTitle ?? 'Ushoppa';
$BASE_URL = $BASE_URL ?? ''; // '' en raíz de /public, '../' dentro de /vendor o /admin
define('BASE_URL', $BASE_URL); // constante disponible también en footer.php y cart-partial.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> · Ushoppa</title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="topbar">NexoMarket · Tu marketplace de confianza — Envío GRATIS en pedidos +$50</div>
<nav class="navbar">
  <div class="nav-inner">
    <a href="<?= BASE_URL ?>index.php" class="logo">U<span class="gold">shoppa</span></a>
    <div class="nav-links">
      <a href="<?= BASE_URL ?>index.php" class="<?= $currentScreen==='index.php'?'active':'' ?>">Inicio</a>
      <a href="<?= BASE_URL ?>catalog.php" class="<?= $currentScreen==='catalog.php'?'active':'' ?>">Catálogo</a>
      <?php /* Links de panel solo visibles según el rol logueado */ ?>
      <?php if ($user && $user['rol']==='vendedor'): ?>
        <a href="<?= BASE_URL ?>vendor/dashboard.php" class="role-link <?= str_starts_with($_SERVER['REQUEST_URI'],'/vendor')||str_contains($_SERVER['REQUEST_URI'],'vendor/')?'active':'' ?>">Mi Tienda</a>
      <?php endif; ?>
      <?php if ($user && $user['rol']==='admin'): ?>
        <a href="<?= BASE_URL ?>admin/dashboard.php" class="role-link <?= str_contains($_SERVER['REQUEST_URI'],'admin/')?'active':'' ?>">Admin</a>
      <?php endif; ?>
    </div>
    <div class="search-wrap">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input id="searchInput" type="text" placeholder="Buscar productos, marcas, vendedores..." value="<?= e($_GET['q'] ?? '') ?>">
    </div>
    <div class="nav-actions">
      <?php /* El botón de carrito solo tiene sentido para clientes o visitantes sin sesión */ ?>
      <?php if (!$user || $user['rol']==='cliente'): ?>
        <button class="cart-btn" data-open-cart>
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
          Carrito <span class="cart-count"><?= cart_count() ?></span>
        </button>
      <?php endif; ?>
      <?php if ($user): ?>
        <?php if ($user['rol']==='cliente'): ?>
          <a href="<?= BASE_URL ?>order-history.php" class="btn-ghost" style="padding:7px 12px;font-size:12px;">Mis Pedidos</a>
        <?php endif; ?>
        <div class="user-chip">
          <div class="avatar"><?= e(mb_substr($user['nombre'],0,1)) ?></div>
          <span style="font-size:13px;"><?= e(explode(' ', $user['nombre'])[0]) ?></span>
          <span class="role-tag"><?= e($user['rol']) ?></span>
        </div>
        <a href="<?= BASE_URL ?>logout.php" class="btn-ghost" style="padding:7px 12px;font-size:12px;color:#888;">Salir</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>login.php" class="btn-ghost" style="padding:7px 14px;font-size:13px;">Iniciar Sesión</a>
        <a href="<?= BASE_URL ?>register.php" class="btn-gold" style="padding:7px 14px;font-size:13px;">Registrarse</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<main>
