<?php

namespace Export;

use Psr\Log\LoggerInterface;

class Logger
{
    private $logger;

    /**
     * Logger constructor.
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function doSomething()
    {
        if ($this->logger) {
            $this->logger->info('Doing work');
        }

        // do something useful
    }
}