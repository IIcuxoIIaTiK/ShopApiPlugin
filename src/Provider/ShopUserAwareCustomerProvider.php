<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Provider;

use App\Domain\Customer\Repository\CustomerRepository;
use Doctrine\ORM\EntityManager;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\ShopApiPlugin\Exception\WrongUserException;

final class ShopUserAwareCustomerProvider implements CustomerProviderInterface
{
    /** @var CustomerRepository */
    private $customerRepository;

    /** @var FactoryInterface */
    private $customerFactory;

    /** @var LoggedInShopUserProviderInterface */
    private $loggedInShopUserProvider;

    /** @var EntityManager */
    protected $em;

    public function __construct(
        CustomerRepository $customerRepository,
        FactoryInterface $customerFactory,
        LoggedInShopUserProviderInterface $loggedInShopUserProvider,
        EntityManager $em
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->loggedInShopUserProvider = $loggedInShopUserProvider;
        $this->em = $em;
    }

    public function provide(string $username): CustomerInterface
    {
        if ($this->loggedInShopUserProvider->isUserLoggedIn()) {
            $loggedInUser = $this->loggedInShopUserProvider->provide();

            /** @var CustomerInterface $customer */
            $customer = $loggedInUser->getCustomer();

            if ($customer->getEmail() !== $username && $customer->getPhoneNumber() !== $username) {
                throw new WrongUserException('Cannot finish checkout for other user, if customer is logged in.');
            }

            return $customer;
        }

        /** @var CustomerInterface|null $customer */
        $customer = $this->customerRepository->findOneByUsername($username);

        if ($customer === null) {
            /** @var CustomerInterface $customer */
            $customer = $this->customerFactory->createNew();
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $customer->setEmail($username);
            } else {
                $customer->setPhoneNumber($username);
            }
            $this->em->persist($customer);
            $this->em->flush();

            return $customer;
        }

        if ($customer->getUser() !== null) {
            throw new WrongUserException('Customer already registered.');
        }

        return $customer;
    }
}
