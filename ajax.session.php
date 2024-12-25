<?php

include_once dirname(__FILE__).'/wc_session.php';

$json = false;
if (isset($_SESSION['is_logged'])) {
    $json = $_SESSION['is_logged'];
}

echo json_encode($json);
return;
