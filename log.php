<?php

function wh_log($msg) {
    $log_filename = "./logs/log_".date("j.n.Y");
    if (!file_exists($log_filename))
    {
        // create directory/folder uploads.
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename.'/log_' . date('d-M-Y') . '.log';
    $log_msg = $_SERVER['HTTP_HOST']. " ".date("m/d/Y H:i:s ", time()) . ": " . $msg;
    file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
}
