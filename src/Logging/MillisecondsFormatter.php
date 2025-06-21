<?php

namespace Azuriom\Plugin\ApiLimiter\Logging;

use Monolog\Logger;

class MillisecondsFormatter
{
    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            if (method_exists($handler, 'getFormatter')) {
                $formatter = $handler->getFormatter();
                if (method_exists($formatter, 'setDateFormat')) {
                    $formatter->setDateFormat('Y-m-d H:i:s.v');
                }
            }
        }
    }
} 