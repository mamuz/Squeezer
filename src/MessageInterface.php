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

interface MessageInterface
{
    const VERSION = 'dev-master';

    const NAME = 'Squeezer by Marco Muths';

    const COMMAND = 'squeeze';

    const HELP = 'Please visit <info>https://github.com/mamuz/Squeezer</info> for detailed informations.';

    const ARGUMENT_TARGET = 'File for squeezed output';

    const OPTION_SOURCE = 'Directory to squeeze';

    const OPTION_EXCLUDE = 'Exclude directories from source';

    const OPTION_NOCOMMENTS = 'Strip comments from code';

    const PROGRESS_FILTER = '<comment>Filter classes from %d found files</comment>';

    const PROGRESS_WRITE = '<comment>Write classes to %s</comment>';

    const PROGRESS_DONE = '<info>Squeezing successfull</info>';
}
