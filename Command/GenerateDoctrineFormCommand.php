<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\GeneratorBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineFormGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Console\Helper\DialogHelper;

/**
 * Generates a form type class for a given Doctrine entity.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Dani Gonzalez <daniel.gonzalez@undefined.es>
 */
class GenerateDoctrineFormCommand extends GenerateDoctrineCommand
{

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
                ->setName($this->getCommandName())
                ->addOption('entity', null, InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)')
                ->addOption('class', null, InputOption::VALUE_OPTIONAL, 'Class of the new form (without the suffix "Type")')
                ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Name of the new form')
                ->addOption('fields', null, InputOption::VALUE_REQUIRED, 'The fields to add to the new form')
                ->setDescription($this->getCommandDescription())
                ->setHelp($this->getCommandHelp())
        ;
    }

    protected function getCommandName()
    {
        return 'undf:generate:form';
    }

    protected function getCommandDescription()
    {
        return 'Generates a form type class based on a Doctrine entity';
    }

    protected function getCommandHelp()
    {
        return <<<EOT
The <info>doctrine:generate:form</info> command generates a form class based on a Doctrine entity.

<info>php app/console doctrine:generate:form AcmeBlogBundle:Post</info>

Every generated file is based on a template. There are default templates but they can be overriden by placing custom templates in one of the following locations, by order of priority:

<info>BUNDLE_PATH/Resources/SensioGeneratorBundle/skeleton/form
APP_PATH/Resources/SensioGeneratorBundle/skeleton/form</info>

You can check https://github.com/sensio/SensioGeneratorBundle/tree/master/Resources/skeleton
in order to know the file structure of the skeleton
EOT
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle) . '\\' . $entity;
        $metadata = $this->getEntityMetadata($entityClass);
        $bundle = $this->getApplication()->getKernel()->getBundle($bundle);

        $generator = $this->getGenerator();
        $generator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        $generator->generate($bundle, $entity, $input->getOption('fields'), $input->getOption('class'), $input->getOption('name'));

        $output->writeln('Generating the form code: <info>OK</info>');
        $dialog->writeGeneratorSummary($output, array());
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Doctrine2 entity generator');

        // namespace
        $output->writeln(array(
            '',
            'This command helps you generate Symfony2 forms for Doctrine2 entities.',
            '',
            'First, you need to give the entity name you want to generate a form for.',
            'You must use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>.',
            ''
        ));

        //entity
        $bundleNames = array_keys($this->getContainer()->get('kernel')->getBundles());
        while (true) {
            $entity = $dialog->askAndValidate($output, $dialog->getQuestion('The Entity shortcut name', $input->getOption('entity')), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'), false, $input->getOption('entity'), $bundleNames);

            list($bundle, $entity) = $this->parseShortcutNotation($entity);

            // check reserved words
            if ($this->getGenerator()->isReservedKeyword($entity)) {
                $output->writeln(sprintf('<bg=red> "%s" is a reserved word</>.', $entity));
                continue;
            }

            try {
                $b = $this->getContainer()->get('kernel')->getBundle($bundle);

                if (!file_exists($b->getPath() . '/Entity/' . str_replace('\\', '/', $entity) . '.php')) {
                    $output->writeln(sprintf('<bg=red>Entity "%s:%s" does not exists</>.', $bundle, $entity));
                }
                break;
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
            }
        }
        $input->setOption('entity', $bundle . ':' . $entity);

        //Form class
        $input->setOption('class', $this->addFormClass($input, $output, $dialog));

        //Form name
        $input->setOption('name', $this->addFormName($input, $output, $dialog));

        // fields
        $input->setOption('fields', $this->addFields($input, $output, $dialog));

        // summary
        $bundle = $this->getApplication()->getKernel()->getBundle($bundle);
        $parts = explode('\\', trim($entity, '\\'));
        $entity = array_pop($parts);
        $parts[] = '';

        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a form class \"<info>%s</info>\" ", $bundle->getNamespace() . '\\Form\\' . implode('\\', $parts) . $input->getOption('class') . 'Type'),
            '',
        ));
    }

    protected function addFormClass(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);
        $bundle = $this->getApplication()->getKernel()->getBundle($bundle);

        $output->writeln(array(
            '',
            'Enter the class name for the new form (Do not include the suffix "Type").',
            '',
        ));
        $classValidator = function($name) use ($bundle, $entity) {
                    $parts = explode('\\', trim($entity, '\\'));
                    array_pop($parts);
                    $dirPath = $bundle->getPath() . '/Form';
                    $path = $dirPath . '/' . implode('/', $parts) . '/' . $name . 'Type.php';
                    if (file_exists($path)) {
                        throw new \InvalidArgumentException(sprintf('Class "%s" already exists. (%s)', $name . 'Type', $path));
                    }

                    return $name;
                };

        $className = $dialog->askAndValidate($output, $dialog->getQuestion('Form class', $entity), $classValidator, false, $entity);

        return $className;
    }

    protected function addFormName(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);
        $bundle = $this->getApplication()->getKernel()->getBundle($bundle);
        $parts = explode('\\', trim($entity, '\\'));
        $entity = array_pop($parts);

        $defaultName = strtolower(str_replace('\\', '_', $bundle->getNamespace()) . ($parts ? '_' : '') . implode('_', $parts) . '_' . $input->getOption('class') . 'type');
        $formName = $dialog->askAndValidate($output, $dialog->getQuestion('Form name', $defaultName), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateReservedWord'));
        return $formName;
    }

    protected function addFields(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle) . '\\' . $entity;
        $metadata = $this->getEntityMetadata($entityClass);


        $fields = array();
        $output->writeln(array(
            '',
            'Add the entity fields you want to include in the new form.',
            '',
        ));
        $output->write('<info>Entity fields:</info> ');

        //Output the list of properties of the entity class
        $generator = $this->createGenerator();
        $columns = $generator->getFieldsFromMetadata($metadata[0]);
        $count = 20;
        foreach ($columns as $i => $column) {
            if ($count > 50) {
                $count = 0;
                $output->writeln('');
            }
            $count += strlen($column);
            $output->write(sprintf('<comment>%s</comment>', $column));
            if (count($columns) != $i + 1) {
                $output->write(', ');
            } else {
                $output->write('.');
            }
        }
        $output->writeln('');


        //Validator function for the name of the form field
        $fieldValidator = function ($name) use ($fields, $columns) {
                    if (isset($fields[$name])) {
                        throw new \InvalidArgumentException(sprintf('Field "%s" is already defined.', $name));
                    }
                    if ($name && !in_array($name, $columns)) {
                        throw new \InvalidArgumentException(sprintf('Field "%s" doesn´t exist.', $name));
                    }

                    return $name;
                };
        //Validator function for the type of the form field
        $formRegistry = $this->getContainer()->get('form.registry');
        $typeValidator = function ($type) use ($formRegistry) {
                    if (!$formRegistry->hasType($type)) {
                        throw new \InvalidArgumentException(sprintf('Invalid field "%s".', $type));
                    }

                    return $type;
                };
        //Validator function for the required flat of the form field
        $requiredValidator = function ($value) {
                    if (!$formRegistry->hasType($type)) {
                        throw new \InvalidArgumentException(sprintf('Invalid field "%s".', $type));
                    }

                    return $type;
                };


        while (true) {
            $output->writeln('');
            $generator = $this->getGenerator();

            //Field name
            $fieldName = $dialog->askAndValidate($output, $dialog->getQuestion('New field (press <return> to stop adding fields)', null), $fieldValidator);
            if (!$fieldName) {
                break;
            }

            //Field type
            $guessedType = $formRegistry->getTypeGuesser()->guessType($entityClass, $fieldName);
            $defaultType = $guessedType ? $guessedType->getType() : null;
            $type = $dialog->askAndValidate($output, $dialog->getQuestion('Field type', $defaultType), $typeValidator, false, $defaultType);

            //Field required flag
            $required = $dialog->askConfirmation($output, $dialog->getQuestion('Required', 'yes'), true);

            $data = array('name' => $fieldName, 'type' => $type, 'required' => $required);

            $fields[$fieldName] = $data;
        }
        return $fields;
    }

    protected function createGenerator()
    {
        return new DoctrineFormGenerator($this->getContainer()->get('filesystem'), $this->getContainer()->get('doctrine'));
    }

}
