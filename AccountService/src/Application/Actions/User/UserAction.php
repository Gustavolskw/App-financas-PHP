<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Application\Actions\Action;
use App\Domain\User\UserRepository;
use Illuminate\Validation\Factory as ValidationFactory;
use Psr\Log\LoggerInterface;

abstract class UserAction extends Action
{
    protected UserRepository $userRepository;

    public function __construct( UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
}
