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
use PhpParser\NodeTraverser;
use PhpParser\ParserAbstract;
use Symfony\Component\Finder\SplFileInfo;

class Loader
{
    /** @var ParserAbstract */
    private $parser;

    /** @var NodeTraverser */
    private $traverser;

    /** @var Collector */
    private $collector;

    /** @var ClassLoader */
    private $loader;

    /**
     * @param ParserAbstract      $parser
     * @param NodeTraverser       $traverser
     * @param Collector $collector
     * @param ClassLoader         $loader
     */
    public function __construct(
        ParserAbstract $parser,
        NodeTraverser $traverser,
        Collector $collector,
        ClassLoader $loader
    ) {
        $this->parser = $parser;
        $this->traverser = $traverser;
        $this->collector = $collector;
        $this->loader = $loader;

        $this->traverser->addVisitor($this->collector);
    }

    /**
     * @param SplFileInfo $file
     */
    public function load(SplFileInfo $file)
    {
        if ($stmts = $this->parser->parse($file->getContents())) {
            $this->traverser->traverse($stmts);
        }

        $classes = $this->collector->getCollection();
        foreach ($classes as $class => $dependencies) {
            $classIsValid = $this->validateDependencies($dependencies, $classes);
            if ($classIsValid && $includeFile = $this->loader->findFile($class)) {
                include_once $includeFile;
            }
        }
    }

    /**
     * @param array $dependencies
     * @param array $classes
     * @return bool
     */
    private function validateDependencies(array $dependencies, array $classes = array())
    {
        $classIsValid = true;
        foreach ($dependencies as $dependency) {
            if (isset($classes[$dependency])) {
                return $this->validateDependencies($classes[$dependency]);
            }
            if (strpos('_', $dependency) === false && count(explode("\\", $dependency)) == 1) {
                return true;
            }
            if (!$this->loader->loadClass($dependency)) {
                $classIsValid = false;
            }
        }
        return $classIsValid;
    }
}
