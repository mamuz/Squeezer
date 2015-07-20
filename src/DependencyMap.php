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

class DependencyMap
{
    /** @var array */
    private $implementations = array();

    /** @var array */
    private $traitUses = array();

    /** @var array */
    private $extensions = array();

    /**
     * @param string $fqcn
     */
    public function addImplementation($fqcn)
    {
        $this->implementations[$fqcn] = $fqcn;
    }

    /**
     * @param string $fqcn
     */
    public function addTraitUse($fqcn)
    {
        $this->traitUses[$fqcn] = $fqcn;
    }

    /**
     * @param string $fqcn
     */
    public function addExtension($fqcn)
    {
        $this->extensions[$fqcn] = $fqcn;
    }

    /**
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @return array
     */
    public function getImplementations()
    {
        return $this->implementations;
    }

    /**
     * @return array
     */
    public function getTraitUses()
    {
        return $this->traitUses;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return array_merge($this->implementations, $this->traitUses, $this->extensions);
    }
}
