<?php declare(strict_types=1);

namespace Cicada\Elasticsearch\Framework\Command;

use Cicada\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupDefinition;
use Cicada\Core\Checkout\Customer\CustomerDefinition;
use Cicada\Core\Checkout\Order\OrderDefinition;
use Cicada\Core\Checkout\Payment\PaymentMethodDefinition;
use Cicada\Core\Checkout\Promotion\PromotionDefinition;
use Cicada\Core\Checkout\Shipping\ShippingMethodDefinition;
use Cicada\Core\Content\Cms\CmsPageDefinition;
use Cicada\Core\Content\LandingPage\LandingPageDefinition;
use Cicada\Core\Content\Media\MediaDefinition;
use Cicada\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Cicada\Core\Content\Product\ProductDefinition;
use Cicada\Core\Content\Property\PropertyGroupDefinition;
use Cicada\Core\Framework\Adapter\Console\CicadaStyle;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelDefinition;
use Cicada\Elasticsearch\Admin\AdminSearcher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
#[AsCommand(
    name: 'es:admin:test',
    description: 'Allows you to test the admin search index',
)]
#[Package('services-settings')]
final class ElasticsearchAdminTestCommand extends Command
{
    private SymfonyStyle $io;

    /**
     * @internal
     */
    public function __construct(private readonly AdminSearcher $searcher)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->addArgument('term', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new CicadaStyle($input, $output);

        $term = $input->getArgument('term');
        $entities = [
            CmsPageDefinition::ENTITY_NAME,
            CustomerDefinition::ENTITY_NAME,
            CustomerGroupDefinition::ENTITY_NAME,
            LandingPageDefinition::ENTITY_NAME,
            ProductManufacturerDefinition::ENTITY_NAME,
            MediaDefinition::ENTITY_NAME,
            OrderDefinition::ENTITY_NAME,
            PaymentMethodDefinition::ENTITY_NAME,
            ProductDefinition::ENTITY_NAME,
            PromotionDefinition::ENTITY_NAME,
            PropertyGroupDefinition::ENTITY_NAME,
            SalesChannelDefinition::ENTITY_NAME,
            ShippingMethodDefinition::ENTITY_NAME,
        ];

        $result = $this->searcher->search($term, $entities, Context::createCLIContext());

        $rows = [];
        foreach ($result as $data) {
            $rows[] = [$data['index'], $data['indexer'], $data['total']];
        }

        $this->io->table(['Index', 'Indexer', 'total'], $rows);

        return self::SUCCESS;
    }
}
