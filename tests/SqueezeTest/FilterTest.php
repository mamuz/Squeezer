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

namespace SqueezeTest;

use Squeeze\Filter;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var Filter */
    protected $fixture;

    /** @var \PhpParser\ParserAbstract | \Mockery\MockInterface */
    protected $parser;

    /** @var \PhpParser\NodeTraverser | \Mockery\MockInterface */
    protected $traverser;

    /** @var \Squeeze\Collector | \Mockery\MockInterface */
    protected $collector;

    protected function setUp()
    {
        $this->parser = \Mockery::mock('PhpParser\ParserAbstract');
        $this->traverser = \Mockery::mock('PhpParser\NodeTraverser');
        $this->collector = \Mockery::mock('Squeeze\Collector');

        $this->fixture = new Filter($this->parser, $this->traverser, $this->collector);
    }

    public function testExtractClassMap()
    {
        $this->collector->shouldReceive('getClassDependencyMap')->once()->andReturn(array());

        $iterator = \Mockery::mock('Iterator');
        $iterator->shouldIgnoreMissing();

        $classMap = $this->fixture->extractClassMapFrom($iterator);

        $this->assertEmpty($classMap);
    }
}
