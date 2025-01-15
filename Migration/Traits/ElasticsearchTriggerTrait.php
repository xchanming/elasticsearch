<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Migration\Traits;

use Cicada\Elasticsearch\Framework\SystemUpdateListener;
use Doctrine\DBAL\Connection;

trait ElasticsearchTriggerTrait
{
    /**
     * This method triggers Elasticsearch indexing after Cicada Update
     */
    public function triggerElasticsearchIndexing(Connection $connection): void
    {
        $connection->executeStatement(
            '
            REPLACE INTO app_config (`key`, `value`) VALUES
            (?, ?)
            ',
            [SystemUpdateListener::CONFIG_KEY, json_encode(['*'], \JSON_THROW_ON_ERROR)]
        );
    }
}
