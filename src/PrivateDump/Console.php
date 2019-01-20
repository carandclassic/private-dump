<?php

namespace PrivateDump;

class Console
{
    /**
     * Show error message and exit the app
     *
     * @param string $message
     * @param int $exitCode
     */
    public function error($message, $exitCode) {
        echo sprintf('[error] %s%s', $message, PHP_EOL);
        exit($exitCode);
    }
}