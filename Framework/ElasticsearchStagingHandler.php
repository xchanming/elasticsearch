<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Maintenance\Staging\Event\SetupStagingEvent;

/**
 * @internal
 */
#[Package('framework')]
readonly class ElasticsearchStagingHandler
{
    public function __construct(
        private bool $checkElasticsearch,
        private ElasticsearchHelper $helper,
        private ElasticsearchOutdatedIndexDetector $detector
    ) {
    }

    public function __invoke(SetupStagingEvent $event): void
    {
        if (!$this->checkElasticsearch || !$this->helper->allowIndexing()) {
            return;
        }

        if (!empty($this->detector->getAllUsedIndices())) {
            $event->io->error('Found existing Elasticsearch indices, please delete them before setting up a staging environment or consider setting a index prefix');
            $event->canceled = true;
        }
    }
}
