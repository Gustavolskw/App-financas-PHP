<?php

namespace App\Application\UseCases;

use App\Domain\Interfaces\RepositoryInterface;
use Psr\Log\LoggerInterface;

abstract class UseCaseService
{
    private LoggerInterface $logger;
    private RepositoryInterface $pdoRepository;

    public function __construct(LoggerInterface $logger, RepositoryInterface $pdoRepository)
    {
        $this->logger = $logger;
        $this->pdoRepository = $pdoRepository;
    }
    public function getPdoRepository():RepositoryInterface
    {
        return $this->pdoRepository;
    }

    public function logger(): LoggerInterface
    {
        return $this->logger;
    }
}
