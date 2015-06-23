<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Marco Muths
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Squeeze;

use Squeeze\MessageInterface as Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class SqueezeCommand extends Command
{
    const EXIT_SUCCESS = 0, EXIT_VIOLATION = 1;

    /** @var Finder */
    private $finder;

    /**
     * @param Finder $finder
     */
    public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }


    protected function configure()
    {
        $this->addOption('source', 's', InputOption::VALUE_OPTIONAL, Message::OPTION_SOURCE);
        $this->addOption('filePattern', 'p', InputOption::VALUE_OPTIONAL, Message::OPTION_FILE_PATTERN);
        $this->addOption('ignore', 'i', InputOption::VALUE_OPTIONAL, Message::OPTION_IGNORE);
        $this->addOption('target', 't', InputOption::VALUE_OPTIONAL, Message::OPTION_TARGET);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription() . PHP_EOL);

        $this->finder
            ->files()
            ->name($this->getConfig()->getFilePattern())
            ->in($this->getConfig()->getSource());

        foreach ($this->finder->getIterator() as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */

        }

        //return self::EXIT_SUCCESS;
        //return self::EXIT_VIOLATION;
    }
}
