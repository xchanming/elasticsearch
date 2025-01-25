<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Product;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * @internal
 *
 * @phpstan-type SearchConfig array{and_logic: string, field: string, tokenize: int, ranking: float}
 */
#[Package('framework')]
class SearchConfigLoader
{
    private const NOT_SUPPORTED_FIELDS = [
        'manufacturer.customFields',
        'categories.customFields',
    ];

    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<SearchConfig>
     */
    public function load(Context $context): array
    {
        foreach ($context->getLanguageIdChain() as $languageId) {
            /** @var array<SearchConfig> $config */
            $config = $this->connection->fetchAllAssociative(
                'SELECT
product_search_config.and_logic,
product_search_config_field.field,
product_search_config_field.tokenize,
product_search_config_field.ranking

FROM product_search_config
INNER JOIN product_search_config_field ON(product_search_config_field.product_search_config_id = product_search_config.id)
WHERE product_search_config.language_id = :languageId AND product_search_config_field.searchable = 1 AND product_search_config_field.field NOT IN(:excludedFields)',
                [
                    'languageId' => Uuid::fromHexToBytes($languageId),
                    'excludedFields' => self::NOT_SUPPORTED_FIELDS,
                ],
                [
                    'excludedFields' => ArrayParameterType::STRING,
                ]
            );

            if (!empty($config)) {
                return $config;
            }
        }

        throw ElasticsearchProductException::configNotFound();
    }
}
