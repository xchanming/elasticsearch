<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Product;

use Cicada\Core\Content\Product\Events\ProductIndexerEvent;
use Cicada\Core\Content\Product\Events\ProductStockAlteredEvent;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\Log\Package;
use Cicada\Elasticsearch\Framework\Indexing\ElasticsearchIndexer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('core')]
class ProductUpdater implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ElasticsearchIndexer $indexer,
        private readonly EntityDefinition $definition
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductIndexerEvent::class => 'update',
            ProductStockAlteredEvent::class => 'update',
        ];
    }

    public function update(ProductIndexerEvent|ProductStockAlteredEvent $event): void
    {
        $this->indexer->updateIds($this->definition, $event->getIds());
    }
}
