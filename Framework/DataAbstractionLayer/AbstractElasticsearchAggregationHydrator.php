<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework\DataAbstractionLayer;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Log\Package;

#[Package('core')]
abstract class AbstractElasticsearchAggregationHydrator
{
    abstract public function getDecorated(): AbstractElasticsearchAggregationHydrator;

    abstract public function hydrate(EntityDefinition $definition, Criteria $criteria, Context $context, array $result): AggregationResultCollection;
}
