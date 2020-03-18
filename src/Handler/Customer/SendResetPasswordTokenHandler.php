<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Handler\Customer;

use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Sylius\ShopApiPlugin\Command\Customer\SendResetPasswordToken;
use Sylius\ShopApiPlugin\Mailer\Emails;
use Webmozart\Assert\Assert;

final class SendResetPasswordTokenHandler
{

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var SenderInterface */
    private $sender;

    public function __construct(UserRepositoryInterface $userRepository, SenderInterface $sender)
    {
        $this->userRepository = $userRepository;
        $this->sender         = $sender;
    }

    public function __invoke(SendResetPasswordToken $resendResetPasswordToken): void
    {
        $username = $resendResetPasswordToken->username();

        /** @var ShopUserInterface $user */
        $user = $this->userRepository->findOneBy(['username' => $username]);

        Assert::notNull($user, sprintf('User with %s username has not been found.', $username));
        Assert::notNull($user->getPasswordResetToken(),
            sprintf('User with %s username has not verification token defined.', $username)
        );

        //если username -> почта
        if($user->getEmail() == $username){
            $this->sender->send(Emails::EMAIL_RESET_PASSWORD_TOKEN,
                [$username],
                ['user' => $user, 'channelCode' => $resendResetPasswordToken->channelCode()]
            );
        }
        //если username -> номер телефона
        if($user->getCustomer()->getPhoneNumber() == $username){
            //TODO: вот тут севис отправки смсок
            dd($user->getEmailVerificationToken());
        }
    }
}
