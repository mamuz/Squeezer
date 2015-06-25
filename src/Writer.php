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

class Writer
{
    /** @var string */
    private $code = "<?php\n";

    /**
     * @param string $filepath
     */
    public function write($filepath)
    {
        $classes = array_merge(
            get_declared_interfaces(),
            get_declared_classes(),
            get_declared_traits()
        );

        foreach ($classes as $class) {
            if (preg_match('/^[a-z]+/', $class)
                || false === strpos($class, "\\")
                || 0 !== strpos($class, 'Composer')
                || 0 !== strpos($class, __NAMESPACE__)
            ) {
                continue;
            }

            $class = new \ReflectionClass($class);

            if ($class->isInternal() || $class->getExtensionName()) {
                continue;
            }
            $this->code .= $this->extractContentFrom($class);
        }

        file_put_contents($filepath, $this->code);
        file_put_contents($filepath, php_strip_whitespace($filepath));
    }

    /**
     * @param \ReflectionClass $class
     * @return string
     */
    private function extractContentFrom(\ReflectionClass $class)
    {
        $fileName = $class->getFileName();
        if (false === $fileName || !file_exists($fileName)) {
            return '';
        }

        $content = file_get_contents($fileName);
        if (strpos($content, '<<<' != false)) {
            return '';
        }

        $tokens = token_get_all($content);
        print_r($tokens);exit;

        $content = preg_replace('/^<\?php/', '', $content);
        $content = preg_replace('/(^namespace .*)(;)/mi', '$1 {', $content);

        return $content . "\n}";
    }
}
