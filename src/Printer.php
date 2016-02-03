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

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;

/**
 * @SuppressWarnings("PMD")
 */
class Printer extends Standard
{
    /** @var bool */
    private $hasCommentSupport = true;

    public function disableComments()
    {
        $this->hasCommentSupport = false;
    }

    public function prettyPrintFile(array $stmts)
    {
        $p = rtrim($this->prettyPrint($stmts));

        $p = preg_replace('/^\?>\n?/', '', $p, -1);
        $p = preg_replace('/<\?php$/', '', $p);

        if (!$this->findNamespaceIn($stmts)) {
            $p = 'namespace {' . $p . '}';
        }

        return $p;
    }

    /**
     * @param array $stmts
     * @return bool
     */
    private function findNamespaceIn(array $stmts)
    {
        foreach ($stmts as $stmt) {
            if ($stmt instanceof \PhpParser\Node\Stmt\Namespace_) {
                return true;
            }
        }

        return false;
    }

    protected function preprocessNodes(array $nodes)
    {
        $this->canUseSemicolonNamespaces = false;
    }

    protected function pStmts(array $nodes, $indent = true)
    {
        $result = '';
        foreach ($nodes as $node) {
            /** @var Node $node */
            $result .= "\n"
                . $this->pComments($node->getAttribute('comments', array()))
                . $this->p($node)
                . ($node instanceof Node\Expr ? ';' : '');
        }

        return preg_replace('~\n(?!$|' . $this->noIndentToken . ')~', "", $result);
    }

    protected function pComments(array $comments)
    {
        $result = '';

        if (!$this->hasCommentSupport) {
            return $result;
        }

        foreach ($comments as $comment) {
            /** @var Comment $comment */
            $text = $comment->getReformattedText();
            if (strpos($text, '//') === 0) {
                $text = str_replace(array('/*', '*/'), '', $text);
                $text = '/*' . substr($text, 2) . '*/';
            }
            $result .= $text . "\n";
        }

        return $result;
    }
}
