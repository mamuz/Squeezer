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

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class Collector extends NodeVisitorAbstract
{
    /** @var array */
    private $collection = array();

    /** @var array */
    private $dependencies = array();

    public function leaveNode(Node $node)
    {
        $this->dependencies = array();
        if ($node instanceof Node\Stmt\Class_) {
            $this->collect(array($node->extends));
            $this->collect($node->implements);
        } elseif ($node instanceof Node\Stmt\Interface_) {
            $this->collect($node->extends);
        } elseif ($node instanceof Node\Stmt\TraitUse) {
            $this->collect($node->traits);
        }

        if ($node instanceof Node\Stmt\ClassLike) {
            if (property_exists($node, 'namespacedName')) {
                $name = $node->namespacedName;
                if ($name instanceof Node\Name) {
                    $name = $name->toString();
                }
                $this->collection[$name] = $this->dependencies;
            }
        }
    }

    /**
     * @param Node\Name[]|null $names
     */
    private function collect($names)
    {
        if ($names) {
            foreach ($names as $name) {
                if ($name) {
                    $name = $name->toString();
                    $this->dependencies[$name] = $name;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getCollection()
    {
        return $this->collection;
    }
}
