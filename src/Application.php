<?php

namespace Squeeze;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;

class Application extends BaseApplication
{
    /**
     * @SuppressWarnings("PMD.UnusedFormalParameter")
     */
    protected function getCommandName(InputInterface $input)
    {
        return MessageInterface::COMMAND;
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
