<?php
require_once __DIR__ . '/../includes/functions.php';
$id = (int)($_POST['id'] ?? 0);
if ($id > 0) cart_remove($id);
echo json_encode(['ok' => true, 'count' => cart_count()]);
