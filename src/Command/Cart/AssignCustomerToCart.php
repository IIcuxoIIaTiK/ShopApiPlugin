<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Command\Cart;

use Sylius\ShopApiPlugin\Command\CommandInterface;

class AssignCustomerToCart implements CommandInterface
{
    /** @var string */
    protected $orderToken;

    /** @var string|null */
    protected $email;

    /** @var string|null */
    protected $phoneNumber;

    public function __construct(string $orderToken, string $email = null, string $phoneNumber = null)
    {
        $this->orderToken = $orderToken;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
    }

    public function orderToken(): string
    {
        return $this->orderToken;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function phoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

}
