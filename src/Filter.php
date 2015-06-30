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

use PhpParser\NodeTraverser;
use PhpParser\ParserAbstract;
use Symfony\Component\Finder\SplFileInfo;

class Filter
{
    /** @var ParserAbstract */
    private $parser;

    /** @var NodeTraverser */
    private $traverser;

    /** @var Collector */
    private $collector;

    /**
     * @param ParserAbstract $parser
     * @param NodeTraverser  $traverser
     * @param Collector      $collector
     */
    public function __construct(
        ParserAbstract $parser,
        NodeTraverser $traverser,
        Collector $collector
    ) {
        $this->parser = $parser;
        $this->traverser = $traverser;
        $this->collector = $collector;
    }

    /**
     * @param \Iterator|SplFileInfo[] $files
     * @return array
     */
    public function extractClassMapFrom(\Iterator $files)
    {
        foreach ($files as $file) {
            if ($stmts = $this->parser->parse($file->getContents())) {
                $this->collector->bind($file->getRealPath());
                $this->traverser->traverse($stmts);
            }
        }

        $classMap = array();
        $classDependencyMap = $this->collector->getClassDependencyMap();
        foreach ($classDependencyMap as $class => $dependencies) {
            if ($this->validate($dependencies)
                && $file = $this->collector->getFileBy($class)
            ) {
                $classMap[$class] = $file;
            }
        }

        return $classMap;
    }

    /**
     * @param array $dependencies
     * @return bool
     */
    private function validate(array $dependencies)
    {
        foreach ($dependencies as $dependency) {
            if (strpos($dependency, '_') === false
                && count(explode("\\", $dependency)) == 1
            ) {
                continue;
            }
            if (!isset($this->collector->getClassDependencyMap()[$dependency])) {
                return false;
            }
        }

        return true;
    }
}
