<?php
declare(strict_types=1);

namespace Binaryoung\Jieba;

use RuntimeException;

/**
 * @method static array cut(string $sentence, bool $hmm = true)
 * @method static array cutAll(string $sentence)
 * @method static array cutForSearch(string $sentence, bool $hmm = true)
 * @method static array TFIDFExtract(string $sentence, int $topK = 20, array $allowedPOS = [])
 * @method static array textRankExtract(string $sentence, int $topK = 20, array $allowedPOS = [])
 * @method static array tokenize(string $sentence, string $mode = 'default', bool $hmm = true)
 * @method static array tag(string $sentence, bool $hmm = true)
 * @method static \Binaryoung\Jieba\Jieba addWord(string $word, ?int $frequency = null, ?string $tag = null)
 * @method static int suggestFrequency(string $segment)
 * @method static \Binaryoung\Jieba\Jieba useDictionary(string $path)
 */

class Jieba
{
    /**
     * The loaded ffi instance
     */
    protected FFI $ffi;

    /**
     * The static ffi instance
     */
    protected static ?FFI $staticFFI = null;

    /**
     * FFI Header Path
     */
    protected static string $headerPath = __DIR__ . '/../lib/jieba_php.h';

    /**
     * FFI Library Path
     */
    protected static ?string $libraryPath;

    /**
     * Dictionary Path
     */
    protected static string $dictionaryPath = __DIR__ . '/../data/dict.txt';

    /**
     * Construct
     */
    public function __construct()
    {
        $this->ffi = static::makeFFI();
    }

    /**
     * Proxy Method Call
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return $this->ffi->{$method}(...$arguments);
    }

    /**
     * Proxy Static Method
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        static::$staticFFI ??= static::makeFFI();

        return static::$staticFFI->{$method}(...$arguments);
    }

    /**
     * Specific Library Path
     *
     * @param string $path
     * @return self
     */
    public static function withLibraryPath(string $path): self
    {
        static::$libraryPath = $path;

        return new static;
    }

    /**
     * Specific Dictionary Path
     *
     * @param string $path
     * @return self
     */
    public static function withDictionaryPath(string $path): self
    {
        static::$dictionaryPath = $path;

        return new static;
    }

    /**
     * Return FFI Instance
     *
     * @return FFI
     */
    public static function makeFFI(): FFI
    {
        return new FFI(
            static::$headerPath,
            static::$libraryPath ?? static::defaultLibraryPath(),
            static::$dictionaryPath,
        );
    }

    /**
     * Return Default Library Path According To OS
     *
     * @return string
     */
    protected static function defaultLibraryPath(): string
    {
        if (PHP_INT_SIZE !== 8) {
            throw new RuntimeException('不支持32位系统，请自行编译lib文件');
        }

        if (stripos(PHP_OS, 'linux') === 0) {
            return __DIR__ . '/../lib/libjieba_php.so';
        }

        if (stripos(PHP_OS, 'win') === 0) {
            return __DIR__ . '/../lib/jieba_php.dll';
        }

        if (stripos(PHP_OS, 'darwin') === 0) {
            return __DIR__ . '/../lib/libjieba_php.dylib';
        }

        throw new RuntimeException('不支持的系统，请自行编译lib文件');
    }
}
