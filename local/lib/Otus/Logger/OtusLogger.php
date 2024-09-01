<?php

namespace Otus\Logger;

use Bitrix\Main\Diag\ExceptionHandlerFormatter;
use Bitrix\Main\Diag\FileExceptionHandlerLog;

class OtusLogger extends FileExceptionHandlerLog
{
    public function write($exception, $logType)
    {
        $text = ExceptionHandlerFormatter::format($exception);

        $context = [
            'type' => static::logTypeToString($logType),
        ];

        $logLevel = static::logTypeToLevel($logType);

        $message = "OTUS => {date} - Host: {host} - {type} - {$text}\n";

        $this->logger->log($logLevel, $message, $context);
    }
}