<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework\Indexing;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Package('framework')]
class CreateAliasTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'cicada.elasticsearch.create.alias';
    }

    public static function getDefaultInterval(): int
    {
        return self::MINUTELY * 5;
    }

    public static function shouldRun(ParameterBagInterface $bag): bool
    {
        return (bool) $bag->get('elasticsearch.enabled');
    }
}
