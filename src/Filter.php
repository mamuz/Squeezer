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

class Filter
{

    /** @var ParserAbstract */
    private $parser;

    /** @var NodeTraverser */
    private $traverser;

    /** @var Collector */
    private $collector;

    /** @var ClassLoader */
    private $classloader;

    /**
     * @param ParserAbstract $parser
     * @param NodeTraverser  $traverser
     * @param Collector      $collector
     * @param ClassLoader    $classloader
     */
    public function __construct(
        ParserAbstract $parser,
        NodeTraverser $traverser,
        Collector $collector,
        ClassLoader $classloader
    ) {
        $this->parser = $parser;
        $this->traverser = $traverser;
        $this->collector = $collector;
        $this->classloader = $classloader;
    }

    /**
     * @param \Iterator|SplFileInfo[] $files
     * @return array
     */
    public function extractClassMapFrom(\Iterator $files)
    {
        foreach ($files as $file) {
            if ($stmts = $this->parser->parse($file->getContents())) {
                $this->traverser->traverse($stmts);
            }
        }

        $classMap = $this->collector->getClassMap();
        $classMap = $this->removeInternalClassesFrom($classMap);
        $classMap = $this->removeUnloadableClassesFrom($classMap);
        $classMap = $this->sort($classMap);

        return $classMap;
    }

    /**
     * @param array $classMap
     * @return array
     */
    private function removeInternalClassesFrom(array $classMap)
    {
        foreach ($classMap as $class => $dependencies) {
            foreach ($dependencies as $index => $dependency) {
                if (!$this->classloader->findFile($dependency)
                    && (class_exists($dependency, false)
                        || interface_exists($dependency, false)
                        || trait_exists($dependency, false))
                ) {
                    $reflectionClass = new \ReflectionClass($class);
                    if ($reflectionClass->isInternal() || $reflectionClass->getExtensionName()) {
                        unset($classMap[$class][$index]);
                    }
                }
            }
        }

        return $classMap;
    }

    /**
     * @param array $classMap
     * @return array
     */
    private function removeUnloadableClassesFrom(array $classMap)
    {
        foreach ($classMap as $class => $dependencies) {
            foreach ($dependencies as $dependency) {
                if (!isset($classMap[$dependency])
                    || !$this->classloader->findFile($dependency)
                ) {
                    unset($classMap[$class]);
                    $classMap = $this->removeUnloadableClassesFrom($classMap);
                    break 2;
                }
            }
        }

        return $classMap;
    }

    /**
     * @param array $classMap
     * @return array
     */
    private function sort(array $classMap)
    {
        $classes = array_keys($classMap);

        set_error_handler(null);
        foreach ($classes as $class) {
            class_exists($class, true);
        }
        restore_error_handler();

        $classIncludes = array_merge(
            get_declared_interfaces(),
            get_declared_traits(),
            get_declared_classes()
        );

        $classMap = array();
        foreach ($classIncludes as $class) {
            if (in_array($class, $classes)) {
                $classMap[$class] = $this->classloader->findFile($class);
            }
        }

        return $classMap;
    }
}
