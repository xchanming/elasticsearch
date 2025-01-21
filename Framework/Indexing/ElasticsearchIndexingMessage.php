<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework\Indexing;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\MessageQueue\AsyncMessageInterface;

#[Package('framework')]
class ElasticsearchIndexingMessage implements AsyncMessageInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly IndexingDto $data,
        private readonly ?IndexerOffset $offset,
        private readonly Context $context
    ) {
    }

    public function getData(): IndexingDto
    {
        return $this->data;
    }

    public function getOffset(): ?IndexerOffset
    {
        return $this->offset;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
