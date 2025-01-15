<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Admin;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\MessageQueue\AsyncMessageInterface;

/**
 * @internal
 */
#[Package('services-settings')]
final class AdminSearchIndexingMessage implements AsyncMessageInterface
{
    /**
     * @param array<string, string> $indices
     * @param array<string> $ids
     */
    public function __construct(
        private readonly string $entity,
        private readonly string $indexer,
        private readonly array $indices,
        private readonly array $ids
    ) {
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getIndexer(): string
    {
        return $this->indexer;
    }

    /**
     * @return array<string, string>
     */
    public function getIndices(): array
    {
        return $this->indices;
    }

    /**
     * @return array<string>
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}
