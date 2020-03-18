<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Request\Customer;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\ShopApiPlugin\Command\CommandInterface;
use Sylius\ShopApiPlugin\Command\Customer\SendResetPasswordToken;
use Sylius\ShopApiPlugin\Request\ChannelBasedRequestInterface;
use Symfony\Component\HttpFoundation\Request;

class SendResetPasswordTokenRequest implements ChannelBasedRequestInterface
{
    /** @var string */
    protected $username;

    /** @var string */
    protected $channelCode;

    protected function __construct(Request $request, string $channelCode)
    {
        $this->username = $request->request->get('username');
        $this->channelCode = $channelCode;
    }

    public static function fromHttpRequestAndChannel(Request $request, ChannelInterface $channel): ChannelBasedRequestInterface
    {
        return new self($request, $channel->getCode());
    }

    public function getCommand(): CommandInterface
    {
        return new SendResetPasswordToken($this->username, $this->channelCode);
    }
}
