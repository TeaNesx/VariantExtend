<?php declare(strict_types=1);

namespace VariantExtend\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;

class VariantExtendSubscriber implements EventSubscriberInterface
{
    private $productRepository;

    public function __construct(EntityRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
        ];
    }

    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $product = $event->getPage()->getProduct();

        if($product->getParentId()) {
            $productId = $product->getParentId();
    
            $criteria = new Criteria([$productId]);
            $criteria->addAssociation('children.media');
            $criteria->addAssociation('children.cover');
            $criteria->addAssociation('children.cover.media');
    
    
            $productWithVariants = $this->productRepository->search($criteria, $event->getContext())->get($productId);

            $event->getPage()->addExtension('customVariants', $productWithVariants->getChildren());
        }
    }
}
