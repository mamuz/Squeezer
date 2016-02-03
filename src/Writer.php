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

use PhpParser\NodeTraverser;
use PhpParser\ParserAbstract;

class Writer
{
    /** @var ParserAbstract */
    private $parser;

    /** @var NodeTraverser */
    private $traverser;

    /** @var Printer */
    private $printer;

    /** @var string */
    private $target;

    /**
     * @param ParserAbstract $parser
     * @param NodeTraverser  $traverser
     * @param Printer        $printer
     */
    public function __construct(
        ParserAbstract $parser,
        NodeTraverser $traverser,
        Printer $printer
    ) {
        $this->parser = $parser;
        $this->traverser = $traverser;
        $this->printer = $printer;
    }

    /**
     * @param $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @param array $classMap
     * @param bool  $noComments
     */
    public function minify(array $classMap, $noComments)
    {
        if ($noComments) {
            $this->printer->disableComments();
        }

        file_put_contents($this->target, "<?php ");

        foreach ($classMap as $file) {
            if ($stmts = $this->parser->parse(file_get_contents($file))) {
                $stmts = $this->traverser->traverse($stmts);
                $code = $this->printer->prettyPrintFile($stmts);
                file_put_contents($this->target, $code, FILE_APPEND);
            }
        }
    }
}
