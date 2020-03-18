<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Command\Customer;

use Sylius\ShopApiPlugin\Command\CommandInterface;

class SendVerificationToken implements CommandInterface
{
    /** @var string */
    protected $username;

    /** @var string */
    protected $channelCode;

    public function __construct(string $username, string $channelCode)
    {
        $this->username = $username;
        $this->channelCode = $channelCode;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function channelCode(): string
    {
        return $this->channelCode;
    }
}
