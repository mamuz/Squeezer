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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Filter
{
    /** @var ParserAbstract */
    private $parser;

    /** @var NodeTraverser */
    private $traverser;

    /** @var ClassLoader */
    private $loader;

    /** @var Collector */
    private $collector;

    /**
     * @param ParserAbstract $parser
     * @param NodeTraverser  $traverser
     * @param Collector      $collector
     * @param ClassLoader    $loader
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
    }

    /**
     * @param Finder $finder
     * @return array
     */
    public function extractClassMap(Finder $finder)
    {
        foreach ($finder as $file) {
            /** @var SplFileInfo $file */
            if ($stmts = $this->parser->parse($file->getContents())) {
                $this->traverser->traverse($stmts);
            }
        }

        $classMap = array();
        $classDependencyMap = $this->collector->getClassDependencyMap();
        foreach ($classDependencyMap as $class => $dependencies) {
            $classIsValid = $this->validateDependencies($dependencies, $classDependencyMap);
            if ($classIsValid && $file = $this->loader->findFile($class)) {
                $classMap[$class] = $file;
            }
        }

        return $classMap;
    }

    /**
     * @param array $dependencies
     * @param array $classDependencyMap
     * @return bool
     */
    private function validateDependencies(array $dependencies, array $classDependencyMap = array())
    {
        foreach ($dependencies as $dependency) {
            if (strpos($dependency, '_') === false && count(explode("\\", $dependency)) == 1) {
                continue;
            }
            if (!isset($classDependencyMap[$dependency])) {
                return false;
            } elseif (false === $this->validateDependencies($classDependencyMap[$dependency])) {
                return false;
            }
        }

        return true;
    }
}
