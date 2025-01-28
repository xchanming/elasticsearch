<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Admin\Indexer;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use Cicada\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Plugin\Exception\DecorationPatternException;
use Cicada\Core\Framework\Uuid\Uuid;
use Cicada\Core\System\SalesChannel\SalesChannelCollection;
use Cicada\Core\System\SalesChannel\SalesChannelDefinition;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

#[Package('inventory')]
final class SalesChannelAdminSearchIndexer extends AbstractAdminIndexer
{
    /**
     * @internal
     *
     * @param EntityRepository<SalesChannelCollection> $repository
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
        return SalesChannelDefinition::ENTITY_NAME;
    }

    public function getName(): string
    {
        return 'sales-channel-listing';
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
            SELECT LOWER(HEX(sales_channel.id)) as id,
                   GROUP_CONCAT(DISTINCT sales_channel_translation.name SEPARATOR " ") as name
            FROM sales_channel
                INNER JOIN sales_channel_translation
                    ON sales_channel.id = sales_channel_translation.sales_channel_id
            WHERE sales_channel.id IN (:ids)
            GROUP BY sales_channel.id
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
