<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use Cicada\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Log\Package;
use OpenSearchDSL\Query\Compound\BoolQuery;

#[Package('core')]
abstract class AbstractElasticsearchDefinition
{
    final public const KEYWORD_FIELD = [
        'type' => 'keyword',
        'ignore_above' => 10000,
        'normalizer' => 'sw_lowercase_normalizer',
    ];

    final public const BOOLEAN_FIELD = ['type' => 'boolean'];

    final public const FLOAT_FIELD = ['type' => 'double'];

    final public const INT_FIELD = ['type' => 'long'];

    final public const SEARCH_FIELD = [
        'fields' => [
            'search' => ['type' => 'text', 'analyzer' => 'sw_whitespace_analyzer'],
            'ngram' => ['type' => 'text', 'analyzer' => 'sw_ngram_analyzer'],
        ],
    ];

    abstract public function getEntityDefinition(): EntityDefinition;

    /**
     * @return array{_source?: array{includes: string[]}, properties: array<mixed>}
     */
    abstract public function getMapping(Context $context): array;

    /**
     * Can be used to define custom queries to define the data to be indexed.
     */
    public function getIterator(): ?IterableQuery
    {
        return null;
    }

    /**
     * @param array<string> $ids
     *
     * @return array<string, array<string, mixed>>
     */
    public function fetch(array $ids, Context $context): array
    {
        return [];
    }

    /**
     * @deprecated tag:v6.7.0 - reason:return-type-change - will return BuilderInterface in the future
     */
    abstract public function buildTermQuery(Context $context, Criteria $criteria): BoolQuery;

    /**
     * @return array<string, mixed>
     */
    protected static function getTextFieldConfig(): array
    {
        return self::KEYWORD_FIELD + self::SEARCH_FIELD;
    }
}
