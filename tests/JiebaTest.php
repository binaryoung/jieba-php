<?php

namespace Binaryoung\Jieba\Tests;

use Binaryoung\Jieba\Jieba;
use PHPUnit\Framework\TestCase;

/**
 * @runClassInSeparateProcess
 */
class JiebaTest extends TestCase
{
    public function testStaticProxy()
    {
        $this->assertEquals(
            ['我', '来到', '北京', '清华大学'],
            Jieba::cut('我来到北京清华大学')
        );
    }

    public function testDynamicProxy()
    {
        $jieba = new Jieba;

        $this->assertEquals(
            ['我', '来到', '北京', '清华大学'],
            $jieba->cut('我来到北京清华大学')
        );

        $this->assertObjectHasAttribute('ffi', $jieba);
    }

    public function testWithLibraryPath()
    {
        Foo::withLibraryPath('foo');

        $this->assertEquals('foo', Foo::$libraryPath);
    }

    public function testWithDictionaryPath()
    {
        Foo::withDictionaryPath('foo');

        $this->assertEquals('foo', Foo::$dictionaryPath);
    }

    public function testChainConfig()
    {
        Foo::withLibraryPath('foo')->withDictionaryPath('bar');

        $this->assertEquals('foo', Foo::$libraryPath);
        $this->assertEquals('bar', Foo::$dictionaryPath);
    }
}

class Foo extends Jieba
{
    public static ?string $libraryPath;
    public static string $dictionaryPath;

    public function __construct()
    {
    }
}
