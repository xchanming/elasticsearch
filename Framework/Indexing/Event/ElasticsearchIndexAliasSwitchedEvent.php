<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework\Indexing\Event;

use Cicada\Core\Framework\Log\Package;

#[Package('core')]
class ElasticsearchIndexAliasSwitchedEvent
{
    /**
     * @param array<string, string> $changes
     */
    public function __construct(private readonly array $changes)
    {
    }

    /**
     * Returns the index as key and the alias as value.
     *
     * @return array<string, string>
     */
    public function getChanges(): array
    {
        return $this->changes;
    }
}
