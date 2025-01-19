<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework\Indexing;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;
use Cicada\Elasticsearch\Framework\AbstractElasticsearchDefinition;

#[Package('core')]
class IndexMappingProvider
{
    /**
     * @internal
     *
     * @param array<mixed> $mapping
     */
    public function __construct(
        private readonly array $mapping,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function build(AbstractElasticsearchDefinition $definition, Context $context): array
    {
        $mapping = $definition->getMapping($context);

        return array_merge_recursive($mapping, $this->mapping);
    }
}
