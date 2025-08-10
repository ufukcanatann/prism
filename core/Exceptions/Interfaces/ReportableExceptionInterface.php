<?php

namespace Core\Exceptions\Interfaces;

use Psr\Log\LoggerInterface;
use Throwable;

interface ReportableExceptionInterface extends Throwable
{
    /**
     * Report the exception
     */
    public function report(LoggerInterface $logger): void;
}
