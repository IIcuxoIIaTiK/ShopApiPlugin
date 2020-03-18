<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Command\Customer;

use Sylius\ShopApiPlugin\Command\CommandInterface;

class GenerateResetPasswordToken implements CommandInterface
{
    /** @var string */
    protected $username;

    public function __construct(string $username)
    {
        $this->username = $username;
    }

    public function username(): string
    {
        return $this->username;
    }
}
