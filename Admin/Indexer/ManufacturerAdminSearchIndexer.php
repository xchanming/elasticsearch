<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Admin\Indexer;

use Cicada\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerCollection;
use Cicada\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use Cicada\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Cicada\Core\Framework\Uuid\Uuid;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

#[Package('inventory')]
final class ManufacturerAdminSearchIndexer extends AbstractAdminIndexer
{
    /**
     * @internal
     *
     * @param EntityRepository<ProductManufacturerCollection> $repository
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly IteratorFactory $factory,
        private readonly EntityRepository $repository,
        private readonly int $indexingBatchSize
    ) {
    }

    public function getDecorated(): AbstractAdminIndexer
    {
        throw new DecorationPatternException(self::class);
    }

    public function getEntity(): string
    {
        return ProductManufacturerDefinition::ENTITY_NAME;
    }

    public function getName(): string
    {
        return 'manufacturer-listing';
    }

    public function getIterator(): IterableQuery
    {
        return $this->factory->createIterator($this->getEntity(), null, $this->indexingBatchSize);
    }

    public function globalData(array $result, Context $context): array
    {
        $ids = array_column($result['hits'], 'id');

        return [
            'total' => (int) $result['total'],
            'data' => $this->repository->search(new Criteria($ids), $context)->getEntities(),
        ];
    }

    public function fetch(array $ids): array
    {
        $data = $this->connection->fetchAllAssociative(
            '
            SELECT LOWER(HEX(product_manufacturer.id)) as id,
                   GROUP_CONCAT(DISTINCT product_manufacturer_translation.name SEPARATOR " ") as name
            FROM product_manufacturer
                INNER JOIN product_manufacturer_translation
                    ON product_manufacturer.id = product_manufacturer_translation.product_manufacturer_id
            WHERE product_manufacturer.id IN (:ids)
            GROUP BY product_manufacturer.id
        ',
            [
                'ids' => Uuid::fromHexToBytesList($ids),
            ],
            [
                'ids' => ArrayParameterType::BINARY,
            ]
        );

        $mapped = [];
        foreach ($data as $row) {
            $id = (string) $row['id'];
            $text = \implode(' ', array_filter($row));
            $mapped[$id] = ['id' => $id, 'text' => \strtolower($text)];
        }

        return $mapped;
    }
}
