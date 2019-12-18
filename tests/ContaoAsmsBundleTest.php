<?php

/*
 * This file is part of [package name].
 *
 * (c) John Doe
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\AsmsBundle\Tests;

use Contao\AsmsBundle\ContaoAsmsBundle;
use PHPUnit\Framework\TestCase;

class ContaoAsmsBundleTest extends TestCase
{
    public function testCanBeInstantiated()
    {
        $bundle = new ContaoAsmsBundle();

        $this->assertInstanceOf('Contao\AsmsBundle\ContaoAsmsBundle', $bundle);
    }
}
