<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Sort;

use Cicada\Core\Framework\Log\Package;
use OpenSearchDSL\BuilderInterface;
use OpenSearchDSL\Sort\FieldSort;

#[Package('core')]
class CountSort extends FieldSort
{
    /**
     * @param array<mixed> $params
     */
    public function __construct(
        string $field,
        ?string $order = null,
        ?BuilderInterface $nestedFilter = null,
        array $params = []
    ) {
        $path = explode('.', $field);
        array_pop($path);

        $params = array_merge(
            $params,
            [
                'mode' => 'sum',
                'nested' => ['path' => implode('.', $path)],
                'missing' => 0,
            ]
        );

        $path[] = '_count';

        parent::__construct(implode('.', $path), $order, $nestedFilter, $params);
    }
}
