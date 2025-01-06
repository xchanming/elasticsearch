<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework\DataAbstractionLayer\Event;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Event\CicadaEvent;
use Cicada\Core\Framework\Log\Package;
use OpenSearchDSL\Search;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @codeCoverageIgnore
 */
#[Package('core')]
class ElasticsearchEntityAggregatorSearchedEvent extends Event implements CicadaEvent
{
    public function __construct(
        public readonly AggregationResultCollection $result,
        public readonly Search $search,
        public readonly EntityDefinition $definition,
        public readonly Criteria $criteria,
        private readonly Context $context
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
