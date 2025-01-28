<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Admin\Indexer;

use Cicada\Core\Content\LandingPage\LandingPageCollection;
use Cicada\Core\Content\LandingPage\LandingPageDefinition;
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
final class LandingPageAdminSearchIndexer extends AbstractAdminIndexer
{
    /**
     * @internal
     *
     * @param EntityRepository<LandingPageCollection> $repository
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
        return LandingPageDefinition::ENTITY_NAME;
    }

    public function getName(): string
    {
        return 'landing-page-listing';
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
            SELECT LOWER(HEX(landing_page.id)) as id,
                   GROUP_CONCAT(DISTINCT landing_page_translation.name SEPARATOR " ") as name,
                   GROUP_CONCAT(DISTINCT tag.name SEPARATOR " ") as tags
            FROM landing_page
                INNER JOIN landing_page_translation
                    ON landing_page.id = landing_page_translation.landing_page_id
                LEFT JOIN landing_page_tag
                    ON landing_page.id = landing_page_tag.landing_page_id
                LEFT JOIN tag
                    ON landing_page_tag.tag_id = tag.id
            WHERE landing_page.id IN (:ids)
            GROUP BY landing_page.id
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
            $text = \implode(' ', array_filter(array_unique(array_values($row))));
            $mapped[$id] = ['id' => $id, 'text' => \strtolower($text)];
        }

        return $mapped;
    }
}
