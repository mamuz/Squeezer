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
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class Command extends BaseCommand
{
    const EXIT_SUCCESS = 0, EXIT_VIOLATION = 1;

    /** @var Finder */
    private $finder;

    /** @var Filter */
    private $filter;

    /** @var Writer */
    private $writer;

    /**
     * @param Finder $finder
     */
    public function setFinder(Finder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param Filter $filter
     */
    public function setFilter(Filter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param Writer $writer
     */
    public function setWriter(Writer $writer)
    {
        $this->writer = $writer;
    }


    protected function configure()
    {
        $this->addOption('source', 's', InputOption::VALUE_OPTIONAL, Message::OPTION_SOURCE);
        $this->addOption('exclude', 'i', InputOption::VALUE_OPTIONAL, Message::OPTION_EXCLUDE);
        $this->addOption('target', 't', InputOption::VALUE_OPTIONAL, Message::OPTION_TARGET);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln($this->getDescription() . PHP_EOL);
            $this->writer->setTarget($input->getOption('target'));
            $this->finder->in($input->getOption('source'))->exclude($input->getOption('exclude'));
            $classMap = $this->filter->extractClassMap($this->finder);
            $this->writer->minify($classMap);
        } catch (\Exception $e) {
            return self::EXIT_VIOLATION;
        }

        return self::EXIT_SUCCESS;
    }
}
