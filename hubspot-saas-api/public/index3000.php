<?php

if (!isset($_GET['code'])) {
    echo 'Nenhum code recebido.';
    exit;
}

$code = $_GET['code'];

header('Location: http://localhost:8000/api/hubspot/callback?code=' . urlencode($code));
exit;
