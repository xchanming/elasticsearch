<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework\Command;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;
use Cicada\Elasticsearch\Framework\Indexing\IndexMappingUpdater;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'es:mapping:update',
    description: 'Update the Elasticsearch indices mapping',
)]
#[Package('core')]
class ElasticsearchUpdateMappingCommand extends Command
{
    /**
     * @internal
     */
    public function __construct(
        private readonly IndexMappingUpdater $indexMappingUpdater,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->indexMappingUpdater->update(Context::createCLIContext());

        return self::SUCCESS;
    }
}
