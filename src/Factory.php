<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Marco Muths
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

use Composer\Autoload\ClassLoader;
use Squeeze\MessageInterface as Message;
use Symfony\Component\Finder\Finder;

/**
 * @SuppressWarnings("PMD.CouplingBetweenObjects")
 */
class Factory
{
    /**
     * @param ClassLoader $loader
     * @return Application
     */
    public function create(ClassLoader $loader)
    {
        $app = new Application(Message::NAME, Message::VERSION);
        $app->add($this->createCommand($loader));

        return $app;
    }

    /**
     * @param ClassLoader $loader
     * @return Command
     */
    protected function createCommand(ClassLoader $loader)
    {
        $parser = new \PhpParser\Parser(new \PhpParser\Lexer\Emulative);

        $collector = new Collector;
        $filterTraverser = new \PhpParser\NodeTraverser;
        $filterTraverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver);
        $filterTraverser->addVisitor($collector);

        $writerTraverser = new \PhpParser\NodeTraverser;
        $writerTraverser->addVisitor(new Converter);
        $filter = new Filter($parser, $filterTraverser, $collector, $loader);
        $writer = new Writer($parser, $writerTraverser, new Printer);

        $finder = new Finder;
        $finder->files()->name('*.php');

        $command = new Command(Message::COMMAND);
        $command->setHelp(Message::HELP);
        $command->setDescription(Message::NAME . ' (' . Message::VERSION . ')');
        $command->setFinder($finder);
        $command->setFilter($filter);
        $command->setWriter($writer);

        return $command;
    }
}
