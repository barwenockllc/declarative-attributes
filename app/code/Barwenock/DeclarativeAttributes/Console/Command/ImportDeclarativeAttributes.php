<?php
/**
 * @author Barwenock
 * @copyright Copyright (c) Barwenock
 * @package Declarative Attributes Import for Magento 2
 */

declare(strict_types=1);

namespace Barwenock\DeclarativeAttributes\Console\Command;

class ImportDeclarativeAttributes extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesManagement
     */
    protected $attributesManagement;

    /**
     * @param \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesManagement $attributesManagement
     * @param string|null $name
     */
    public function __construct(
        \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesManagement $attributesManagement,
        string $name = null
    ) {
        $this->attributesManagement = $attributesManagement;
        parent::__construct($name);
    }

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('import:declarative-attributes');
        $this->setDescription('Import product attributes from CSV file');
        parent::configure();
    }

    /**
     * Executes the current command
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        try {
            $this->attributesManagement->attributesProcess();

            $output->writeln("<info>Attributes import finished successfully.</info>");
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln(sprintf(
                '<error>Error was happened during import %s.</error>',
                $exception->getMessage()
            ));
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
