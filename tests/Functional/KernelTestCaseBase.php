<?php

declare(strict_types=1);

namespace CSL\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class KernelTestCaseBase extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return \CSL\Kernel::class;
    }
}


