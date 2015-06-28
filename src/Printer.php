<?php

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

        return $p;
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
            $result .= $comment->getReformattedText() . "\n";
        }

        return $result;
    }
}
