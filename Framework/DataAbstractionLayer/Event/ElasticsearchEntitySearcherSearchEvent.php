<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework\DataAbstractionLayer\Event;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Event\CicadaEvent;
use Cicada\Core\Framework\Log\Package;
use OpenSearchDSL\Search;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('core')]
class ElasticsearchEntitySearcherSearchEvent extends Event implements CicadaEvent
{
    public function __construct(
        private readonly Search $search,
        private readonly EntityDefinition $definition,
        private readonly Criteria $criteria,
        private readonly Context $context
    ) {
    }

    public function getSearch(): Search
    {
        return $this->search;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getDefinition(): EntityDefinition
    {
        return $this->definition;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }
}
