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

use Composer\Autoload\ClassLoader;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\ParserAbstract;
use Symfony\Component\Finder\SplFileInfo;

class Loader
{
    /** @var ParserAbstract */
    private $parser;

    /** @var NodeTraverser */
    private $traverser;

    /** @var DependencyVisitor */
    private $visitor;

    /** @var ClassLoader */
    private $loader;

    /**
     * @param ParserAbstract    $parser
     * @param NodeTraverser     $traverser
     * @param DependencyVisitor $visitor
     * @param ClassLoader       $loader
     */
    public function __construct(
        ParserAbstract $parser,
        NodeTraverser $traverser,
        DependencyVisitor $visitor,
        ClassLoader $loader
    ) {
        $this->parser = $parser;
        $this->traverser = $traverser;
        $this->visitor = $visitor;
        $this->loader = $loader;

        $this->traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver);
        $this->traverser->addVisitor($this->visitor);
    }

    public function traverse(SplFileInfo $file)
    {
        try {
            if ($stmts = $this->parser->parse($file->getContents())) {
                $this->traverser->traverse($stmts);
            }
        } catch (Error $error) {
            //
        }

        $classes = $this->visitor->getCollection();

        foreach ($classes as $class => $dependencies) {
            $classIsValid = true;
            foreach ($dependencies as $dependency) {
                if (!$this->loader->loadClass($dependency)) {
                    $classIsValid = false;
                }
            }
            if ($classIsValid && $file = $this->loader->findFile($class)) {
                include_once $file;
            }
        }
    }
}
