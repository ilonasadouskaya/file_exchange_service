<?php

declare(strict_types=1);

class ErrorHandler
{
    public static function noticeHandler ($errno, $errstr, $errfile, $errline): void
    {
        throw new Exception( '[' . $errno . ']' . $errstr . ' in ' . $errfile . ' at line ' . $errline);
    }
}