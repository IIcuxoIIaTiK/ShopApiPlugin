<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Handler\Customer;

use Http\Discovery\Exception\NotFoundException;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Sylius\Component\User\Security\Generator\GeneratorInterface;
use Sylius\ShopApiPlugin\Command\Customer\GenerateResetPasswordToken;
use Webmozart\Assert\Assert;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GenerateResetPasswordTokenHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var GeneratorInterface */
    private $tokenGenerator;

    public function __construct(UserRepositoryInterface $userRepository, GeneratorInterface $tokenGenerator)
    {
        $this->userRepository = $userRepository;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function __invoke(GenerateResetPasswordToken $generateResetPasswordToken): void
    {
        $username = $generateResetPasswordToken->username();

        /** @var ShopUserInterface $user */
        $user = $this->userRepository->findOneBy(['username' => $username]);

//        throw new NotFoundHttpException(sprintf('User with %s email has not been found.', $email));

        Assert::notNull($user, sprintf('User with %s username has not been found.', $username));

        if ($username == $user->getEmail()){
            $user->setPasswordResetToken($this->tokenGenerator->generate());
        }
        if ($username == $user->getCustomer()->getPhoneNumber()){
            $code = str_pad(strval(mt_rand(1, 999999)), 6, '0', STR_PAD_LEFT);
            $user->setPasswordResetToken($code);
        }

        $user->setPasswordRequestedAt(new \DateTime());
    }
}
