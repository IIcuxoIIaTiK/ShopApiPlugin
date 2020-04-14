<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Provider;

use Sylius\Component\Core\Model\CustomerInterface;
use App\Domain\Customer\Repository\CustomerRepository;
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

    public function __construct(
        CustomerRepository $customerRepository,
        FactoryInterface $customerFactory,
        LoggedInShopUserProviderInterface $loggedInShopUserProvider
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->loggedInShopUserProvider = $loggedInShopUserProvider;
    }

    public function provide(string $searchField): CustomerInterface
    {
        if ($this->loggedInShopUserProvider->isUserLoggedIn()) {
            $loggedInUser = $this->loggedInShopUserProvider->provide();

            /** @var CustomerInterface $customer */
            $customer = $loggedInUser->getCustomer();

            if ($customer->getEmail() !== $searchField && $customer->getPhoneNumber() !== $searchField) {
                throw new WrongUserException('Cannot finish checkout for other user, if customer is logged in.');
            }

            return $customer;
        }

        /** @var CustomerInterface|null $customer */
        $customer = $this->customerRepository->findOneByEmailOrPhoneNumber($searchField);

        if ($customer === null) {
            /** @var CustomerInterface $customer */
            $customer = $this->customerFactory->createNew();
            if(filter_var($searchField, FILTER_VALIDATE_EMAIL)){
                $customer->setEmail($searchField);
            }
            else {
                $customer->setPhoneNumber($searchField);
            }

            $this->customerRepository->add($customer);

            return $customer;
        }

        if ($customer->getUser() !== null) {
            throw new WrongUserException('Customer already registered.');
        }

        return $customer;
    }
}
