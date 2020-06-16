<?php
require __DIR__.'/../vendor/autoload.php';

use Binaryoung\Jieba\Jieba;

$novel = file_get_contents(__DIR__.'/../data/weicheng.txt');

function sentence()
{
    global $novel;

    $sentences = explode('。', $novel);

    return $sentences[array_rand($sentences)] . '。';
}

$sentence = sentence();
dump(sprintf('sentence: %s', $sentence));

// cut
dump('cut:');
dump(Jieba::cut($sentence));

// cutAll
dump('cutAll:');
dump(Jieba::cutAll($sentence));

// cutForSearch
dump('cutForSearch:');
dump(Jieba::cutForSearch($sentence));

// tokenize
dump('tokenize:');
dump(Jieba::tokenize($sentence));

// tag
dump('tag:');
dump(Jieba::tag($sentence));

// TFIDFExtract
dump('TFIDF抽取围城关键字:');
dump(Jieba::TFIDFExtract($novel));
dump(Jieba::TFIDFExtract($novel, 5, ['i']));

// textRankExtract
dump('TextRank抽取围城关键字:');
dump(Jieba::textRankExtract($novel));
dump(Jieba::textRankExtract($novel, 5, ['ns']));

// addWord
dump('添加自定义词组:');
dump(
    Jieba::addWord('䶴䶵𦡦')
            ->addWord('讥䶯䶰䶱䶲䶳', 42, 'n')
            ->cut('讥䶯䶰䶱䶲䶳䶴䶵𦡦', false)
);

// suggestFrequency
dump('查询词频 围城：' . Jieba::suggestFrequency('围城'));

// useDictionary
dump('自定义字典:');
$jieba = new Jieba;
dump($jieba->cut('真香警告'));
$jieba->useDictionary(__DIR__ . '/dict.txt');
dump($jieba->cut('真香警告'));
dump($jieba->tag('真香警告'));
dump($jieba->suggestFrequency('真香警告'));

