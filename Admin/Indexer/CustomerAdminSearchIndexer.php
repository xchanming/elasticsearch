<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Admin\Indexer;

use Cicada\Core\Checkout\Customer\CustomerCollection;
use Cicada\Core\Checkout\Customer\CustomerDefinition;
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
final class CustomerAdminSearchIndexer extends AbstractAdminIndexer
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerCollection> $repository
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
        return CustomerDefinition::ENTITY_NAME;
    }

    public function getName(): string
    {
        return 'customer-listing';
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
            SELECT LOWER(HEX(customer.id)) as id,
                   GROUP_CONCAT(DISTINCT tag.name SEPARATOR " ") as tags,
                   GROUP_CONCAT(DISTINCT country_translation.name SEPARATOR " ") as country,
                   GROUP_CONCAT(DISTINCT customer_address.name SEPARATOR " ") as address_name,
                   GROUP_CONCAT(DISTINCT customer_address.company SEPARATOR " ") as address_company,
                   GROUP_CONCAT(DISTINCT customer_address.city SEPARATOR " ") as city,
                   GROUP_CONCAT(DISTINCT customer_address.street SEPARATOR " ") as street,
                   GROUP_CONCAT(DISTINCT customer_address.zipcode SEPARATOR " ") as zipcode,
                   GROUP_CONCAT(DISTINCT customer_address.phone_number SEPARATOR " ") as phone_number,
                   GROUP_CONCAT(DISTINCT customer_address.additional_address_line1 SEPARATOR " ") as additional_address_line1,
                   GROUP_CONCAT(DISTINCT customer_address.additional_address_line2 SEPARATOR " ") as additional_address_line2,
                   customer.name,
                   customer.email,
                   customer.company,
                   customer.customer_number
            FROM customer
                LEFT JOIN customer_address
                    ON customer_address.customer_id = customer.id
                LEFT JOIN country
                    ON customer_address.country_id = country.id
                LEFT JOIN country_translation
                    ON country.id = country_translation.country_id
                LEFT JOIN customer_tag
                    ON customer.id = customer_tag.customer_id
                LEFT JOIN tag
                    ON customer_tag.tag_id = tag.id
            WHERE customer.id IN (:ids)
            GROUP BY customer.id
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
