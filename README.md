# Jieba PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/binaryoung/jieba-php.svg?style=flat-square)](https://packagist.org/packages/binaryoung/jieba-php)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/binaryoung/jieba-php/run%20tests?label=tests)](https://github.com/binaryoung/jieba-php/actions?query=workflow%3A"run+tests"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/binaryoung/jieba-php.svg?style=flat-square)](https://packagist.org/packages/binaryoung/jieba-php)

[结巴分词](https://github.com/fxsjy/jieba) PHP 实现 - The Jieba Chinese Word Segmentation Implemented in PHP  
使用 PHP 7.4 中新增的 [FFI](https://www.php.net/manual/en/class.ffi.php) 对 [jieba-rs](https://github.com/messense/jieba-rs) 进行了包装。

## Requirement

PHP >= 7.4，并开启 FFI 扩展

## Installation

You can install the package via composer:

```bash
composer require binaryoung/jieba-php
```

## Usage

```php
use Binaryoung\Jieba\Jieba;

var_dump(Jieba::cut('PHP是世界上最好的语言！'));
```

## API

```php
array cut(string $sentence, bool $hmm = true)
array cutAll(string $sentence)
array cutForSearch(string $sentence, bool $hmm = true)
array TFIDFExtract(string $sentence, int $topK = 20, array $allowedPOS = [])
array textRankExtract(string $sentence, int $topK = 20, array $allowedPOS = [])
array tokenize(string $sentence, string $mode = 'default', bool $hmm = true)
array tag(string $sentence, bool $hmm = true)
int   suggestFrequency(string $segment)
self  addWord(string $word, ?int $frequency = null, ?string $tag = null)
self  useDictionary(string $path)
```

## Examples

see examples/example.php

```bash
composer example
```

## Testing

```bash
composer test
```

## Benchmark

```bash
composer bench
```

对比 [jukuball/jieba-php](https://github.com/fukuball/jieba-php)，循环 50 次对围城每行文字作为一个句子进行分词，分词算法都采用 HMM 模式。
| 名称 | 耗时 | 单次耗时 | 内存占用 | 内存峰值 |
|---|---|---|---|---|
| jukuball/jieba-php | 51.593 | 1.032 | 493.00MB | 515.03MB |
| binaryoung/jieba-php | 8.408 | 0.16816 | 10.00MB | 22.01MB |
| 差值 | ↓513.59% | ↓513.59% | ↓4830.00% | ↓2240.20% |

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email me instead of using the issue tracker.

## Credits

-   [young](https://github.com/binaryoung)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
