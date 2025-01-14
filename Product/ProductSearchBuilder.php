<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Product;

use Cicada\Core\Content\Product\ProductDefinition;
use Cicada\Core\Content\Product\SearchKeyword\ProductSearchBuilderInterface;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Routing\RoutingException;
use Cicada\Core\System\SalesChannel\SalesChannelContext;
use Cicada\Elasticsearch\Framework\ElasticsearchHelper;
use Symfony\Component\HttpFoundation\Request;

#[Package('core')]
class ProductSearchBuilder implements ProductSearchBuilderInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ProductSearchBuilderInterface $decorated,
        private readonly ElasticsearchHelper $helper,
        private readonly ProductDefinition $productDefinition,
        private readonly int $searchTermMaxLength = 300
    ) {
    }

    public function build(Request $request, Criteria $criteria, SalesChannelContext $context): void
    {
        if (!$this->helper->allowSearch($this->productDefinition, $context->getContext(), $criteria)) {
            $this->decorated->build($request, $criteria, $context);

            return;
        }

        $search = $request->get('search');

        if (\is_array($search)) {
            $term = implode(' ', $search);
        } else {
            $term = (string) $search;
        }

        $term = mb_substr(trim($term), 0, $this->searchTermMaxLength);

        if (empty($term)) {
            throw RoutingException::missingRequestParameter('search');
        }

        // reset queries and set term to criteria.
        $criteria->resetQueries();

        // elasticsearch will interpret this on demand
        $criteria->setTerm($term);
    }
}
