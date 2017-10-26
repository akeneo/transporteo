<?php

namespace Akeneo\Bundle\MigrationBundle\Command;

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM\Repository\ProductDraftRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductDraftMigrationCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('transporteo:migration:draft')
            ->setDescription('Launch the product draft migration');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ProductDraftRepository $productDraftRepository */
        $productDraftRepository = $this->getContainer()->get('pimee_workflow.repository.product_draft');
        $drafts = $productDraftRepository->createQueryBuilder('p')->getQuery()->execute();

        /** @var WriterFactory $writer */
        $writer = WriterFactory::create(Type::CSV);
        $writer->setShouldAddBOM(false);

        $filename = uniqid('draft_') . '.csv';
        $writer->openToFile(sprintf('/tmp/%s', $filename));

        $writer->addRow(['identifier', 'createdAt', 'changes', 'status', 'author']);

        $flatDraft = [];
        foreach ($drafts as $draft) {
            $flatDraft['identifier'] = $draft->getProduct()->getIdentifier()->getData();
            $flatDraft['createdAt'] = $draft->getCreatedAt()->format('Y-m-d H:i:s');
            $flatDraft['changes'] = json_encode($draft->getChanges());
            $flatDraft['status'] = $draft->getStatus();
            $flatDraft['author'] = $draft->getAuthor();

            $writer->addRow($flatDraft);
        }

        $writer->close();
        $output->writeln($filename);
    }
}
