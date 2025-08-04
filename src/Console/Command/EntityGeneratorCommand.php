<?php

namespace Kim1ne\B24\Console\Command;

use Kim1ne\B24\Service\EntityGenerator\EntityGenerator;
use Bitrix\Main\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'b24.entity.generate',
    description: 'Generate entity for table'
)]
class EntityGeneratorCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $tableName = $input->getArgument('table_name');

            $this->tableExists($tableName);

            $className = $input->getArgument('class_name');

            if (empty($className)) {
                $className = null;
            }

            $namespace = $input->getArgument('namespace');

            $filepath = str_replace(
                '//',
                '/',
                $_SERVER['DOCUMENT_ROOT'] . '/' . $input->getArgument('filepath')
            );

            $entityGenerator = new EntityGenerator($tableName);

            $entityGenerator
                ->setNamespace($namespace)
                ->setClassName($className)
                ->createFile($filepath);
        } catch (\Throwable $exception) {
            $output->writeln('<error>Error: ' . $exception->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>generated class: ' . $entityGenerator->getFullClassName() . '</info>');

        return Command::SUCCESS;
    }

    private function tableExists(string $tableName): void
    {
        $connection = Application::getConnection();

        if ($connection->isTableExists($tableName) === false) {
            throw new \RuntimeException("Table is \"$tableName\" not found");
        }
    }

    protected function configure(): void
    {
        $this
            ->addArgument('table_name', InputArgument::REQUIRED, 'Table name')
            ->addArgument('namespace', InputArgument::REQUIRED, 'Namespace')
            ->addArgument('filepath', InputArgument::REQUIRED, 'File path')
            ->addArgument('class_name', InputArgument::OPTIONAL, 'Class name');
    }
}
