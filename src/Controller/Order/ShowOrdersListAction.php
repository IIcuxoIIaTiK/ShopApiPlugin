<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Controller\Order;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\ShopApiPlugin\Model\PaginatorDetails;
use Sylius\ShopApiPlugin\Provider\LoggedInShopUserProviderInterface;
use Sylius\ShopApiPlugin\ViewRepository\Order\PlacedOrderViewRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;

final class ShowOrdersListAction
{
    /** @var ViewHandlerInterface */
    private $viewHandler;

    /** @var LoggedInShopUserProviderInterface */
    private $loggedInUserProvider;

    /** @var PlacedOrderViewRepositoryInterface */
    private $placedOrderQuery;

    public function __construct(
        ViewHandlerInterface $viewHandler,
        LoggedInShopUserProviderInterface $loggedInUserProvider,
        PlacedOrderViewRepositoryInterface $placedOrderQuery
    ) {
        $this->viewHandler = $viewHandler;
        $this->loggedInUserProvider = $loggedInUserProvider;
        $this->placedOrderQuery = $placedOrderQuery;
    }

    public function __invoke(Request $request): Response
    {
        try {
            /** @var ShopUserInterface $user */
            $user = $this->loggedInUserProvider->provide();
        } catch (TokenNotFoundException $exception) {
            return $this->viewHandler->handle(View::create(null, Response::HTTP_UNAUTHORIZED));
        }

        return $this->viewHandler->handle(
            View::create($this->placedOrderQuery->getAllCompletedByCustomerEmail($user->getCustomer()->getEmail(), new PaginatorDetails($request->attributes->get('_route'), $request->query->all())), Response::HTTP_OK)
        );
    }
}
