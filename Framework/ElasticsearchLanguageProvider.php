<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityRepository;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Filter\NandFilter;
use Cicada\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\Language\LanguageCollection;
use Cicada\Elasticsearch\Framework\Indexing\Event\ElasticsearchIndexerLanguageCriteriaEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

#[Package('core')]
class ElasticsearchLanguageProvider
{
    /**
     * @internal
     */
    public function __construct(private readonly EntityRepository $languageRepository, private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function getLanguages(Context $context): LanguageCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new NandFilter([new EqualsFilter('salesChannels.id', null)]));
        $criteria->addSorting(new FieldSorting('id'));

        $this->eventDispatcher->dispatch(new ElasticsearchIndexerLanguageCriteriaEvent($criteria, $context));

        /** @var LanguageCollection $languages */
        $languages = $this->languageRepository
            ->search($criteria, $context)
            ->getEntities();

        return $languages;
    }
}
