<?php

use Bitrix\Main\Diag\FileLogger;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$logFile = $_SERVER['DOCUMENT_ROOT'] . '/logs/otus.log';

try {
    throw new \Exception('Exception test message');
} catch (Exception $e) {
    $logger = new FileLogger($logFile);
    $logger->debug(
        "{date} - {host}\n{exception}\n{delimiter}\n",
        [
            'exception' => $e,
        ]
    );
}

