<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Admin;

use Cicada\Core\Framework\Api\Serializer\JsonEntityEncoder;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Cicada\Core\Framework\DataAbstractionLayer\Entity;
use Cicada\Core\Framework\DataAbstractionLayer\EntityCollection;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @internal
 */
#[Package('inventory')]
final class AdminSearchController
{
    public function __construct(
        private readonly AdminSearcher $searcher,
        private readonly DefinitionInstanceRegistry $definitionRegistry,
        private readonly JsonEntityEncoder $entityEncoder,
        private readonly AdminElasticsearchHelper $adminEsHelper
    ) {
    }

    #[Route(path: '/api/_admin/es-search', name: 'api.admin.es-search', methods: ['POST'], defaults: ['_routeScope' => ['administration']])]
    public function elastic(Request $request, Context $context): Response
    {
        if ($this->adminEsHelper->getEnabled() === false) {
            throw ElasticsearchAdminException::esNotEnabled();
        }

        $term = trim($request->request->getString('term'));
        $entities = $request->request->all('entities');

        if ($term === '') {
            throw ElasticsearchAdminException::missingTermParameter();
        }

        $limit = $request->get('limit', 10);

        $results = $this->searcher->search($term, $entities, $context, $limit);

        foreach ($results as $entityName => $result) {
            $definition = $this->definitionRegistry->getByEntityName($entityName);

            /** @var EntityCollection<Entity> $entityCollection */
            $entityCollection = $result['data'];
            $entities = [];

            foreach ($entityCollection->getElements() as $key => $entity) {
                $entities[$key] = $this->entityEncoder->encode(new Criteria(), $definition, $entity, '/api');
            }

            $results[$entityName]['data'] = $entities;
        }

        return new JsonResponse(['data' => $results]);
    }
}
