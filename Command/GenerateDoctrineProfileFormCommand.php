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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;

/**
 * Generates a form type class for a given Doctrine entity.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Dani Gonzalez <daniel.gonzalez@undefined.es>
 */
class GenerateDoctrineProfileFormCommand extends GenerateDoctrineFormCommand
{

    protected function getCommandName()
    {
        return 'undf:generate:profile-form';
    }

    protected function getCommandDescription()
    {
        return 'Generates a profile form class based on a Doctrine entity';
    }

    protected function addFormClass(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        return 'Profile';
    }

    protected function addFormName(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        return 'undf_user_profile';
    }

}
