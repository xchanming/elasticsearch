<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Test;

use Cicada\Core\DevOps\Environment\EnvironmentHelper;
use Cicada\Core\Framework\Log\Package;
use Cicada\Elasticsearch\Admin\AdminElasticsearchHelper;
use Cicada\Elasticsearch\Framework\Command\ElasticsearchAdminIndexingCommand;
use Cicada\Elasticsearch\Framework\ElasticsearchHelper;
use Doctrine\DBAL\Connection;
use OpenSearch\Client;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[Package('services-settings')]
trait AdminElasticsearchTestBehaviour
{
    #[Before]
    public function enableElasticsearch(): void
    {
        $this->getDiContainer()
            ->get(ElasticsearchHelper::class)
            ->setEnabled(true);
    }

    #[After]
    public function disableElasticsearch(): void
    {
        $this->getDiContainer()
            ->get(ElasticsearchHelper::class)
            ->setEnabled(false);
    }

    #[Before]
    public function enableAdminElasticsearch(): void
    {
        $this->getDiContainer()
            ->get(AdminElasticsearchHelper::class)
            ->setEnabled(true);
    }

    #[After]
    public function disableAdminElasticsearch(): void
    {
        $this->getDiContainer()
            ->get(AdminElasticsearchHelper::class)
            ->setEnabled(false);
    }

    /**
     * @param array<string, mixed> $input
     */
    public function indexElasticSearch(array $input = []): void
    {
        $this->getDiContainer()
            ->get(ElasticsearchAdminIndexingCommand::class)
            ->run(new ArrayInput([...$input, '--no-queue' => true]), new NullOutput());

        $this->runWorker();

        $this->refreshIndex();
    }

    public function refreshIndex(): void
    {
        $this->getDiContainer()->get(Client::class)
            ->indices()
            ->refresh(['index' => '_all']);
    }

    abstract protected function getDiContainer(): ContainerInterface;

    abstract protected function runWorker(): void;

    protected function clearElasticsearch(): void
    {
        $c = $this->getDiContainer();

        $client = $c->get(Client::class);

        $indices = $client->indices()->get(['index' => EnvironmentHelper::getVariable('CICADA_ADMIN_ES_INDEX_PREFIX') . '*']);

        foreach ($indices as $index) {
            $client->indices()->delete(['index' => $index['settings']['index']['provided_name']]);
        }

        $connection = $c->get(Connection::class);
        $connection->executeStatement('TRUNCATE admin_elasticsearch_index_task');
    }
}
