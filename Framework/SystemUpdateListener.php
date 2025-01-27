<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework;

use Cicada\Core\Framework\Adapter\Storage\AbstractKeyValueStorage;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Update\Event\UpdatePostFinishEvent;
use Cicada\Elasticsearch\Framework\Indexing\ElasticsearchIndexer;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[Package('framework')]
#[AsEventListener]
class SystemUpdateListener
{
    public const CONFIG_KEY = 'elasticsearch.indexing.entities';

    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractKeyValueStorage $storage,
        private readonly ElasticsearchIndexer $indexer,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(UpdatePostFinishEvent $event): void
    {
        $entitiesToReindex = $this->storage->get(self::CONFIG_KEY, []);

        if (empty($entitiesToReindex)) {
            return;
        }

        $offset = null;
        while ($message = $this->indexer->iterate($offset)) {
            $offset = $message->getOffset();

            $this->messageBus->dispatch($message);
        }

        $this->storage->remove(self::CONFIG_KEY);
    }
}
