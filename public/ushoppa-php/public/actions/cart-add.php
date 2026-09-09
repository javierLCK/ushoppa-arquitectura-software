<?php
require_once __DIR__ . '/../includes/functions.php';
$id  = (int)($_POST['id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));
if ($id > 0) cart_add($id, $qty);
echo json_encode(['ok' => true, 'count' => cart_count()]);
