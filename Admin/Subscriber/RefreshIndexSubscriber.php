<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Admin\Subscriber;

use Cicada\Core\Framework\DataAbstractionLayer\Event\RefreshIndexEvent;
use Cicada\Core\Framework\Log\Package;
use Cicada\Elasticsearch\Admin\AdminIndexingBehavior;
use Cicada\Elasticsearch\Admin\AdminSearchRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('inventory')]
final class RefreshIndexSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly AdminSearchRegistry $registry)
    {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RefreshIndexEvent::class => 'handled',
        ];
    }

    public function handled(RefreshIndexEvent $event): void
    {
        $this->registry->iterate(
            new AdminIndexingBehavior(
                $event->getNoQueue(),
                $event->getSkipEntities(),
                $event->getOnlyEntities()
            )
        );
    }
}
