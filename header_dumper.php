<?php

if (isset($_SERVER['HTTP_X_HCS_PAYLOAD'])) {
    $data = unserialize(base64_decode($_SERVER['HTTP_X_HCS_PAYLOAD']));
    file_put_contents('dump.log', print_r($data, true));
}//end :: if

?>
