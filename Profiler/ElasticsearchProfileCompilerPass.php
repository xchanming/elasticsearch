<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Profiler;

use Cicada\Core\Framework\Log\Package;
use OpenSearch\Client;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

#[Package('core')]
class ElasticsearchProfileCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $isDebugEnabled = $container->getParameterBag()->resolveValue($container->getParameter('kernel.debug'));

        if (!$isDebugEnabled) {
            $container->removeDefinition(DataCollector::class);

            return;
        }

        $clientDecorator = new Definition(ClientProfiler::class);
        $clientDecorator->setArguments([
            new Reference('cicada.es.profiled.client.inner'),
        ]);
        $clientDecorator->setDecoratedService(Client::class);

        $container->setDefinition('cicada.es.profiled.client', $clientDecorator);

        $adminClientDecorator = new Definition(ClientProfiler::class);
        $adminClientDecorator->setArguments([
            new Reference('cicada.es.profiled.adminClient.inner'),
        ]);
        $adminClientDecorator->setDecoratedService('admin.openSearch.client');

        $container->setDefinition('cicada.es.profiled.adminClient', $adminClientDecorator);
    }
}
