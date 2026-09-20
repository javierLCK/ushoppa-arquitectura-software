<?php
$BASE_URL = '';
require_once __DIR__ . '/includes/functions.php';

$pages = [
    'sobre' => [
        'title' => 'Sobre NexoMarket',
        'body' => "NexoMarket es la empresa detrás de Ushoppa, una plataforma marketplace desarrollada como proyecto académico para la asignatura Arquitectura de Software (ISI602).\n\nNuestra misión es conectar compradores y vendedores en un mismo espacio digital, ofreciendo una experiencia de compra simple, moderna y segura, tanto para quienes buscan productos como para quienes quieren emprender vendiendo en línea.\n\nEl proyecto integra un catálogo de productos, gestión de pedidos, paneles diferenciados para clientes, vendedores y administradores, y un flujo de pago simulado con fines educativos.",
    ],
    'terminos' => [
        'title' => 'Términos y condiciones',
        'body' => "Al usar Ushoppa aceptas los siguientes términos generales:\n\n1. Ushoppa es una plataforma que conecta compradores y vendedores independientes; NexoMarket no es fabricante ni propietario de los productos publicados.\n\n2. Cada vendedor es responsable de la veracidad de la información de sus productos, del stock disponible y del cumplimiento de los pedidos.\n\n3. Los precios y promociones pueden cambiar sin previo aviso.\n\n4. El pago dentro de esta plataforma es simulado con fines académicos: no se procesa dinero real ni se almacenan datos de tarjetas reales.\n\n5. NexoMarket se reserva el derecho de suspender cuentas que incumplan estas condiciones.",
    ],
    'privacidad' => [
        'title' => 'Política de privacidad',
        'body' => "En NexoMarket nos tomamos en serio la protección de tus datos:\n\n- Los datos de registro (nombre, correo) se usan únicamente para identificar tu cuenta dentro de la plataforma.\n- Las contraseñas se almacenan de forma cifrada (hash), nunca en texto plano.\n- No se comparte información de usuarios con terceros.\n- Como este es un proyecto académico, no se procesan pagos reales ni se almacenan datos financieros verdaderos.\n\nSi tienes dudas sobre el uso de tus datos, puedes escribirnos desde la sección de Contacto.",
    ],
    'como-comprar' => [
        'title' => 'Cómo comprar en Ushoppa',
        'body' => "Comprar en Ushoppa es simple:\n\n1. Explora el catálogo o usa el buscador para encontrar lo que necesitas.\n2. Agrega los productos que quieras al carrito con el botón \"+ Carrito\".\n3. Revisa tu carrito desde el ícono superior y ajusta cantidades si lo necesitas.\n4. Haz clic en \"Finalizar Compra\" para ver el resumen del pedido.\n5. Inicia sesión (o crea una cuenta) si aún no lo has hecho.\n6. Completa el pago simulado eligiendo tu región y comuna de entrega.\n7. Revisa el estado de tu pedido en cualquier momento desde \"Mis Pedidos\".",
    ],
    'metodos-pago' => [
        'title' => 'Métodos de pago',
        'body' => "Ushoppa utiliza un sistema de pago simulado con fines académicos, pensado para demostrar un flujo de checkout completo sin procesar dinero real.\n\nTarjetas aceptadas en el simulador:\n- Visa, Mastercard y American Express (según el primer dígito ingresado).\n- Usa 4242 4242 4242 4242 para simular un pago aprobado.\n- Usa 4000 0000 0000 0002 para simular un pago rechazado.\n\nNingún dato de tarjeta ingresado en este simulador se almacena ni se transmite a una pasarela de pago real.",
    ],
    'envios' => [
        'title' => 'Envíos y devoluciones',
        'body' => "Envíos:\nUshoppa realiza el despacho a cualquier región de Chile. El envío es gratuito en compras sobre \$50; bajo ese monto se cobra un costo fijo de envío. El tiempo estimado de entrega es de 24 a 48 horas hábiles desde la confirmación del pago.\n\nDevoluciones:\nSi un producto llega en mal estado o no corresponde a lo solicitado, puedes contactar al vendedor a través del panel de tu pedido en \"Mis Pedidos\" o escribir a soporte desde la sección de Contacto.",
    ],
    'ayuda' => [
        'title' => 'Centro de ayuda',
        'body' => "Preguntas frecuentes:\n\n¿Cómo sé si mi pago fue exitoso?\nAl confirmar el pago verás una pantalla de \"Pedido Confirmado\" con tu número de pedido. También puedes revisarlo en \"Mis Pedidos\".\n\n¿Puedo cambiar la dirección de un pedido ya confirmado?\nNo directamente desde la plataforma; contacta al vendedor a través de la sección de Contacto.\n\n¿Cómo me convierto en vendedor?\nCrea una cuenta y selecciona el tipo \"Vendedor\" durante el registro, o revisa la sección \"Vende en Ushoppa\".\n\n¿Olvidé mi contraseña?\nPor ahora la recuperación de contraseña no está disponible en esta versión académica del proyecto; contacta a soporte.",
    ],
    'comisiones' => [
        'title' => 'Comisiones para vendedores',
        'role' => 'vendedor',
        'body' => "Vender en Ushoppa no tiene costo de suscripción mensual. El modelo de comisión es simulado con fines académicos y se basa en un porcentaje aplicado únicamente sobre las ventas efectivamente concretadas.\n\nEsto significa que un vendedor sin ventas no paga nada, y solo se descuenta comisión cuando un pedido se confirma exitosamente.",
    ],
    'soporte-vendedor' => [
        'title' => 'Soporte para vendedores',
        'role' => 'vendedor',
        'body' => "Si eres vendedor y tienes dudas sobre cómo publicar productos, editar tu inventario o interpretar las estadísticas de tu panel, puedes:\n\n1. Revisar el Centro de ayuda general.\n2. Escribirnos desde la sección de Contacto detallando tu consulta.\n3. Ingresar a tu Panel de vendedor para gestionar directamente tus publicaciones (Mi Tienda → Agregar/Editar Producto).",
    ],
];

$slug = $_GET['page'] ?? '';
$page = $pages[$slug] ?? null;

// Páginas de la sección "Vendedores" (comisiones, soporte-vendedor) son
// vistas exclusivas para cuentas con rol=vendedor. require_role() ya
// redirige a login.php si no hay sesión, o a index.php si el rol no
// coincide (misma protección que usa vendor/dashboard.php).
if ($page && isset($page['role'])) {
    require_role($page['role']);
}

$pageTitle = $page['title'] ?? 'Ushoppa';

require __DIR__ . '/includes/header.php';
?>
<div class="page-wrap" style="max-width:820px;">
  <?php if (!$page): ?>
    <div class="empty-state">
      <div class="emoji">🔎</div>
      <h2 class="serif" style="font-size:24px;margin-bottom:12px;color:#f0f0f0;">Página no encontrada</h2>
      <a href="index.php" class="btn-gold" style="padding:12px 32px;">Volver al inicio →</a>
    </div>
  <?php else: ?>
    <a href="index.php" class="btn-ghost" style="display:inline-flex;align-items:center;gap:6px;margin-bottom:28px;font-size:13px;">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg> Volver al inicio
    </a>
    <h1 class="section-title serif" style="font-size:34px;margin-bottom:24px;"><?= e($page['title']) ?></h1>
    <div class="card" style="padding:32px;font-size:15px;color:#aaa;line-height:1.8;white-space:pre-line;">
      <?= e($page['body']) ?>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
