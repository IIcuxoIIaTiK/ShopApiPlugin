<?php

namespace Sylius\ShopApiPlugin\ViewRepository\Product;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\ShopApiPlugin\Factory\Product\ProductViewFactoryInterface;
use Sylius\ShopApiPlugin\Provider\SupportedLocaleProviderInterface;
use Sylius\ShopApiPlugin\View\Product\ProductListView;
use Webmozart\Assert\Assert;
use Sylius\ShopApiPlugin\Model\PaginatorDetails;
use Sylius\ShopApiPlugin\View\Product\PageView;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Sylius\ShopApiPlugin\Factory\Product\PageViewFactory;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository;
use Pagerfanta\Adapter\ArrayAdapter;

class ProductDiscountedVIewRepository
{

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductViewFactoryInterface */
    private $productViewFactory;

    /** @var SupportedLocaleProviderInterface */
    private $supportedLocaleProvider;

    /** @var PageViewFactory */
    private $pageViewFactory;

    /** @var TaxonRepository */
    private $taxonRepository;

    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        ProductRepositoryInterface $productRepository,
        ProductViewFactoryInterface $productViewFactory,
        SupportedLocaleProviderInterface $supportedLocaleProvider,
        PageViewFactory $pageViewFactory,
        TaxonRepository $taxonRepository
    ) {
        $this->channelRepository       = $channelRepository;
        $this->productRepository       = $productRepository;
        $this->productViewFactory      = $productViewFactory;
        $this->supportedLocaleProvider = $supportedLocaleProvider;
        $this->pageViewFactory         = $pageViewFactory;
        $this->taxonRepository         = $taxonRepository;
    }

    public function getDiscountedProducts(
        string $channelCode,
        ?string $localeCode,
        $paginatorDetails,
        string $taxonCode = null
    ): PageView {
        $channel    = $this->getChannel($channelCode);
        $localeCode = $this->supportedLocaleProvider->provide($localeCode, $channel);
        $taxon      = null;
        if ($taxonCode) {
            /** @var TaxonInterface $taxon */
            $taxon = $this->taxonRepository->findOneBy(['code' => $taxonCode]);
        }
        $discountedProducts = $this->productRepository->findDiscountedByChannel($channel, $localeCode, $taxon);
        Assert::notNull($discountedProducts, sprintf('Discounted Products not found in %s locale.', $localeCode));

        $productListView = new ProductListView();

        $pagerfanta = new Pagerfanta(new ArrayAdapter($discountedProducts));

        $pagerfanta->setMaxPerPage($paginatorDetails->limit());
        $pagerfanta->setCurrentPage($paginatorDetails->page());

        $pageView =
            $this->pageViewFactory->create($pagerfanta, $paginatorDetails->route(), $paginatorDetails->parameters());

        foreach ($pagerfanta->getCurrentPageResults() as $currentPageResult) {
            $pageView->items[] = $this->productViewFactory->create($currentPageResult, $channel, $localeCode);
        }

        return $pageView;
    }

    private function getChannel(string $channelCode): ChannelInterface
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelRepository->findOneByCode($channelCode);

        Assert::notNull($channel, sprintf('Channel with code %s has not been found.', $channelCode));

        return $channel;
    }
}
