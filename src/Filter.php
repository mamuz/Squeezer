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
                $this->collector->reset();
            }
        }

        $classMap = $this->collector->getClassMap();
        $classMap = $this->removeUnloadableClassesFrom($classMap);
        $classMap = $this->sort($classMap);

        $classFileMap = array();
        foreach ($classMap as $class) {
            $classFileMap[$class] = $this->classloader->findFile($class);
        }


        return $classFileMap;
    }

    /**
     * @param array $classMap
     * @return array
     */
    private function removeUnloadableClassesFrom(array $classMap)
    {
        foreach ($classMap as $class => $dependencies) {
            if (!$this->classloader->findFile($class)) {
                unset($classMap[$class]);
                $classMap = $this->removeUnloadableClassesFrom($classMap);
                break;
            }
            foreach ($dependencies as $dependency) {
                if (!isset($classMap[$dependency]) || !$this->classloader->findFile($dependency)) {
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
        $edges = array();
        foreach ($classMap as $class => $dependencies) {
            foreach ($dependencies as $dependency) {
                $edges[] = array($dependency, $class);
            }
        }

        return $this->topologicalSort(
            array_keys($classMap),
            $edges
        );
    }

    /**
     * @param array $nodeIds
     * @param array $edges
     * @return array
     */
    private function topologicalSort(array $nodeIds, array $edges)
    {
        $sorted = $edgelessNodes = $nodes = array();

        foreach ($nodeIds as $id) {
            $nodes[$id] = array('in' => array(), 'out' => array());
            foreach ($edges as $edge) {
                if ($id == $edge[0]) {
                    $nodes[$id]['out'][] = $edge[1];
                }
                if ($id == $edge[1]) {
                    $nodes[$id]['in'][] = $edge[0];
                }
            }
        }

        foreach ($nodes as $id => $node) {
            if (empty($node['in'])) {
                $edgelessNodes[] = $id;
            }
        }

        while (!empty($edgelessNodes)) {
            $sorted[] = $id = array_shift($edgelessNodes);
            foreach ($nodes[$id]['out'] as $out) {
                $nodes[$out]['in'] = array_diff($nodes[$out]['in'], array($id));
                if (empty($nodes[$out]['in'])) {
                    $edgelessNodes[] = $out;
                }
            }
            $nodes[$id]['out'] = array();
        }

        return $sorted;
    }
}
