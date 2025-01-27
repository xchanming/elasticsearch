<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework\Indexing\Event;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Event\CicadaEvent;
use Cicada\Core\Framework\Log\Package;
use Cicada\Elasticsearch\Framework\AbstractElasticsearchDefinition;

#[Package('framework')]
class ElasticsearchIndexConfigEvent implements CicadaEvent
{
    /**
     * @param array<mixed> $config
     */
    public function __construct(
        private readonly string $indexName,
        private array $config,
        private readonly AbstractElasticsearchDefinition $definition,
        private readonly Context $context
    ) {
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }

    /**
     * @return array<mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function getDefinition(): AbstractElasticsearchDefinition
    {
        return $this->definition;
    }

    /**
     * @param array<mixed> $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
