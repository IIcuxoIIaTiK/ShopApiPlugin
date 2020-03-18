<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Validator\Customer;

use Sylius\Component\User\Repository\UserRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class ShopUserExistsValidator extends ConstraintValidator
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function validate($username, Constraint $constraint)
    {
        if (null === $username || null === $this->userRepository->findOneBy(['username' => $username])) {
            $this->context->addViolation($constraint->message);
        }
    }
}
