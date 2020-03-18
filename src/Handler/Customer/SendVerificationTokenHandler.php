<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Handler\Customer;

use Http\Discovery\Exception\NotFoundException;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Sylius\ShopApiPlugin\Command\Customer\SendVerificationToken;
use Sylius\ShopApiPlugin\Mailer\Emails;
use Webmozart\Assert\Assert;

final class SendVerificationTokenHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var SenderInterface */
    private $sender;

    public function __construct(UserRepositoryInterface $userRepository, SenderInterface $sender)
    {
        $this->userRepository = $userRepository;
        $this->sender = $sender;
    }

    public function __invoke(SendVerificationToken $resendVerificationToken): void
    {
        $username = $resendVerificationToken->username();

        /** @var ShopUserInterface $user */
        $user = $this->userRepository->findOneBy(['username' => $username]);
        Assert::notNull($user, sprintf('User with %s username has not been found.', $username));
        Assert::notNull($user->getEmailVerificationToken(), sprintf('User with %s username has not verification token defined.', $username));
        //если username -> почта
        if($user->getEmail() == $username){
            $this->sender->send(
                Emails::EMAIL_VERIFICATION_TOKEN,
                [$username],
                ['user' => $user, 'channelCode' => $resendVerificationToken->channelCode(), 'frontUrl' => getenv('FRONT_URL')]
            );
        }
        //если username -> номер телефона
        if($user->getCustomer()->getPhoneNumber() == $username){
            //TODO: вот тут севис отправки смсок
            dd($user->getEmailVerificationToken());
        }
    }
}
