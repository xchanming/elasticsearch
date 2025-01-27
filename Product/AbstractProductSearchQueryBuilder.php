<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Product;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Log\Package;
use OpenSearchDSL\Query\Compound\BoolQuery;

#[Package('framework')]
abstract class AbstractProductSearchQueryBuilder
{
    abstract public function getDecorated(): AbstractProductSearchQueryBuilder;

    /**
     * @deprecated tag:v6.7.0 - reason:return-type-change - will return BuilderInterface in the future
     */
    abstract public function build(Criteria $criteria, Context $context): BoolQuery;
}
