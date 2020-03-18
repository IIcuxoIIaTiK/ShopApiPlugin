<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Command\Cart;

use Sylius\ShopApiPlugin\Command\CommandInterface;

class AssignCustomerToCart implements CommandInterface
{
    /** @var string */
    protected $orderToken;

    /** @var string */
    protected $username;

    public function __construct(string $orderToken, string $username)
    {
        $this->orderToken = $orderToken;
        $this->username = $username;
    }

    public function orderToken(): string
    {
        return $this->orderToken;
    }

    public function username(): string
    {
        return $this->username;
    }
}
