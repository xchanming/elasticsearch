<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework\Indexing\Event;

use Cicada\Core\Framework\Log\Package;
use Cicada\Elasticsearch\Framework\AbstractElasticsearchDefinition;

#[Package('core')]
class ElasticsearchIndexCreatedEvent
{
    public function __construct(
        private readonly string $indexName,
        private readonly AbstractElasticsearchDefinition $definition
    ) {
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }

    public function getDefinition(): AbstractElasticsearchDefinition
    {
        return $this->definition;
    }
}
