<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework\Indexing\Event;

use Cicada\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use Cicada\Core\Framework\Log\Package;
use Cicada\Elasticsearch\Framework\AbstractElasticsearchDefinition;

/**
 * @codeCoverageIgnore
 */
#[Package('framework')]
class ElasticsearchIndexIteratorEvent
{
    public function __construct(
        public readonly AbstractElasticsearchDefinition $elasticsearchDefinition,
        public IterableQuery $iterator,
    ) {
    }
}
