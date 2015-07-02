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
    private $classMap = array();

    /** @var array */
    private $dependencies = array();

    /** @var Node\Stmt\ClassLike[] */
    private $foundClasses = array();

    /** @var array */
    private $uses = array();

    /** @var bool */
    private $hasFoundInvalidStmt = false;

    private $invalidFunctions = array(
        'basename',
        'chgrp',
        'chmod',
        'chown',
        'clearstatcache',
        'copy',
        'delete',
        'dirname',
        'disk_​free_​space',
        'disk_​total_​space',
        'diskfreespace',
        'file_​exists',
        'file_​get_​contents',
        'file_​put_​contents',
        'file',
        'fileatime',
        'filectime',
        'filegroup',
        'fileinode',
        'filemtime',
        'fileowner',
        'fileperms',
        'filesize',
        'filetype',
        'fnmatch',
        'fopen',
        'glob',
        'is_​dir',
        'is_​executable',
        'is_​file',
        'is_​link',
        'is_​readable',
        'is_​uploaded_​file',
        'is_​writable',
        'is_​writeable',
        'lchgrp',
        'lchown',
        'link',
        'linkinfo',
        'lstat',
        'mkdir',
        'move_​uploaded_​file',
        'parse_​ini_​file',
        'parse_​ini_​string',
        'pathinfo',
        'readfile',
        'readlink',
        'realpath',
        'rename',
        'rmdir',
        'stat',
        'symlink',
        'tempnam',
        'touch',
        'unlink',
        'stream_resolve_include_path',
        'stream_is_local',
    );

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $this->collect(array($node->extends));
            $this->collect($node->implements);
            $this->foundClasses[] = $node;
        } elseif ($node instanceof Node\Stmt\Interface_) {
            $this->collect($node->extends);
            $this->foundClasses[] = $node;
        } elseif ($node instanceof Node\Stmt\Trait_) {
            $this->foundClasses[] = $node;
        } elseif ($node instanceof Node\Stmt\TraitUse) {
            $this->collect($node->traits);
        } elseif ($node instanceof Node\Stmt\UseUse) {
            $this->uses[$node->name->toString()] = $node->alias;
        } elseif ($node instanceof Node\Stmt\Declare_) {
            $this->hasFoundInvalidStmt = true;
        } elseif ($node instanceof Node\Expr\Include_) {
            $this->hasFoundInvalidStmt = true;
        } elseif ($node instanceof Node\Expr\FuncCall) {
            if (method_exists($node->name, 'toString')) {
                $function = $node->name->toString();
                if (in_array($function, $this->invalidFunctions)) {
                    $this->hasFoundInvalidStmt = true;
                }
            }
        }

        if ($node instanceof Node && $comment = $node->getDocComment()) {
            $text = $comment->getText();
            foreach ($this->uses as $namespace => $alias) {
                $text = str_replace('@' . $alias, '@'. $namespace, $text);
            }
            $comment->setText($text);
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

    public function reset()
    {
        if (count($this->foundClasses) == 1
            && !$this->hasFoundInvalidStmt
        ) {
            $node = array_shift($this->foundClasses);
            $name = $node->namespacedName;
            if ($name instanceof Node\Name) {
                $name = $name->toString();
            }
            $this->classMap[$name] = $this->dependencies;
        }

        $this->dependencies = array();
        $this->foundClasses = array();
        $this->hasFoundInvalidStmt = false;
    }

    /**
     * @return array
     */
    public function getClassMap()
    {
        return $this->classMap;
    }
}
