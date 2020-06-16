<?php

namespace Binaryoung\Jieba\Tests;

use Binaryoung\Jieba\FFI;
use Binaryoung\Jieba\Jieba;
use PHPUnit\Framework\TestCase;

/**
 * @runClassInSeparateProcess
 */
class FFITest extends TestCase
{
    public function makeFFI(): FFI
    {
        return Jieba::makeFFI();
    }

    public function testCut()
    {
        $this->assertEquals(
            ['我们', '中出', '了', '一个', '叛徒'],
            $this->makeFFI()->cut('我们中出了一个叛徒')
        );

        $this->assertEquals(
            ['我们', '中出', '了', '一个', '叛徒', '👪'],
            $this->makeFFI()->cut('我们中出了一个叛徒👪')
        );

        $this->assertEquals(
            ['我', '来到', '北京', '清华大学'],
            $this->makeFFI()->cut('我来到北京清华大学')
        );

        $this->assertEquals(
            ['他', '来到', '了', '网易', '杭研', '大厦'],
            $this->makeFFI()->cut('他来到了网易杭研大厦')
        );
    }

    public function testCutWithoutHMM()
    {
        $this->assertEquals(
            ['abc', '网球', '拍卖会', 'def'],
            $this->makeFFI()->cut('abc网球拍卖会def', false)
        );

        $this->assertEquals(
            ['我们', '中', '出', '了', '一个', '叛徒'],
            $this->makeFFI()->cut('我们中出了一个叛徒', false)
        );

        $this->assertEquals(
            ['我', '来到', '北京', '清华大学'],
            $this->makeFFI()->cut('我来到北京清华大学', false)
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCutWeiChen()
    {
        $jieba = $this->makeFFI();

        foreach (explode('\n', file_get_contents(__DIR__ . '/../data/weicheng.txt')) as $sentence) {
            $jieba->cut($sentence);
        }
    }

    public function testCutAll()
    {
        $this->assertEquals(
            ['我', '来', '来到', '到', '北', '北京', '京', '清', '清华', '清华大学', '华', '华大', '大', '大学', '学'],
            $this->makeFFI()->cutAll('我来到北京清华大学')
        );

        $this->assertEquals(
            ['abc', '网', '网球', '网球拍', '球', '球拍', '拍', '拍卖', '拍卖会', '卖', '会', 'def'],
            $this->makeFFI()->cutAll('abc网球拍卖会def')
        );
    }

    public function testCutForSearch()
    {
        $this->assertEquals(
            ['小明', '硕士', '毕业', '于', '中国', '科学', '学院', '科学院', '中国科学院', '计算', '计算所', '，', '后', '在', '日本', '京都', '大学', '日本京都大学', '深造'],
            $this->makeFFI()->cutForSearch('小明硕士毕业于中国科学院计算所，后在日本京都大学深造')
        );

        $this->assertEquals(
            ['小', '明', '硕士', '毕业', '于', '中国', '科学', '学院', '科学院', '中国科学院', '计算', '计算所', '，', '后', '在', '日本', '京都', '大学', '日本京都大学', '深造'],
            $this->makeFFI()->cutForSearch('小明硕士毕业于中国科学院计算所，后在日本京都大学深造', false)
        );

        $this->assertEquals(
            ['南京', '京市', '南京市', '长江', '大桥', '长江大桥'],
            $this->makeFFI()->cutForSearch('南京市长江大桥')
        );
    }

    public function testTFIDFExtract()
    {
        $this->assertEquals(
            ['北京烤鸭', '纽约', '天气'],
            $this->makeFFI()->TFIDFExtract('今天纽约的天气真好啊，京华大酒店的张尧经理吃了一只北京烤鸭。后天纽约的天气不好，昨天纽约的天气也不好，北京烤鸭真好吃', 3)
        );

        $this->assertEquals(
            ['欧亚', '吉林', '置业', '万元', '增资'],
            $this->makeFFI()->TFIDFExtract('此外，公司拟对全资子公司吉林欧亚置业有限公司增资4.3亿元，增资后，吉林欧亚置业注册资本由7000万元增加到5亿元。吉林欧亚置业主要经营范围为房地产开发及百货零售等业务。目前在建吉林欧亚城市商业综合体项目。2013年，实现营业收入0万元，实现净利润-139.13万元。', 5)
        );

        $this->assertEquals(
            ['欧亚', '吉林', '置业', '增资', '实现'],
            $this->makeFFI()->TFIDFExtract('此外，公司拟对全资子公司吉林欧亚置业有限公司增资4.3亿元，增资后，吉林欧亚置业注册资本由7000万元增加到5亿元。吉林欧亚置业主要经营范围为房地产开发及百货零售等业务。目前在建吉林欧亚城市商业综合体项目。2013年，实现营业收入0万元，实现净利润-139.13万元。', 5, ['ns', 'n', 'vn', 'v'])
        );
    }

    public function testTextRankExtract()
    {
        $this->assertEquals(
            ['吉林', '欧亚', '置业', '实现', '收入', '增资'],
            $this->makeFFI()->textRankExtract('此外，公司拟对全资子公司吉林欧亚置业有限公司增资4.3亿元，增资后，吉林欧亚置业注册资本由7000万元增加到5亿元。吉林欧亚置业主要经营范围为房地产开发及百货零售等业务。目前在建吉林欧亚城市商业综合体项目。2013年，实现营业收入0万元，实现净利润-139.13万元。', 6, ['ns', 'n', 'vn', 'v'])
        );

        $this->assertEquals(
            ['纽约', '天气', '不好'],
            $this->makeFFI()->textRankExtract('It is nice weather in New York City. and今天纽约的天气真好啊，and京华大酒店的张尧经理吃了一只北京烤鸭。and后天纽约的天气不好，and昨天纽约的天气也不好，and北京烤鸭真好吃', 3)
        );
    }

    public function testTokenize()
    {
        $this->assertEquals(
            [
                ['word' => '南京市', 'start' => 0, 'end' => 3],
                ['word' => '长江大桥', 'start' => 3, 'end' => 7],
            ],
            $this->makeFFI()->tokenize('南京市长江大桥', 'default', false)
        );

        $this->assertEquals(
            [
                ['word' => '南京', 'start' => 0, 'end' => 2],
                ['word' => '京市', 'start' => 1, 'end' => 3],
                ['word' => '南京市', 'start' => 0, 'end' => 3],
                ['word' => '长江', 'start' => 3, 'end' => 5],
                ['word' => '大桥', 'start' => 5, 'end' => 7],
                ['word' => '长江大桥', 'start' => 3, 'end' => 7],
            ],
            $this->makeFFI()->tokenize('南京市长江大桥', 'search', false)
        );

        $this->assertEquals(
            [
                ['word' => '我们', 'start' => 0, 'end' => 2],
                ['word' => '中', 'start' => 2, 'end' => 3],
                ['word' => '出', 'start' => 3, 'end' => 4],
                ['word' => '了', 'start' => 4, 'end' => 5],
                ['word' => '一个', 'start' => 5, 'end' => 7],
                ['word' => '叛徒', 'start' => 7, 'end' => 9],
            ],
            $this->makeFFI()->tokenize('我们中出了一个叛徒', 'default', false)
        );

        $this->assertEquals(
            [
                ['word' => '我们', 'start' => 0, 'end' => 2],
                ['word' => '中出', 'start' => 2, 'end' => 4],
                ['word' => '了', 'start' => 4, 'end' => 5],
                ['word' => '一个', 'start' => 5, 'end' => 7],
                ['word' => '叛徒', 'start' => 7, 'end' => 9],
            ],
            $this->makeFFI()->tokenize('我们中出了一个叛徒', 'default')
        );

        $this->assertEquals(
            [
                ['word' => '永和', 'start' => 0, 'end' => 2],
                ['word' => '服装', 'start' => 2, 'end' => 4],
                ['word' => '饰品', 'start' => 4, 'end' => 6],
                ['word' => '有限公司', 'start' => 6, 'end' => 10],
            ],
            $this->makeFFI()->tokenize('永和服装饰品有限公司', 'default')
        );
    }

    public function testTag()
    {
        $this->assertEquals(
            [
                [ 'word' => '我', 'tag' => 'r' ],
                [ 'word' => '是', 'tag' => 'v' ],
                [
                    'word' => '拖拉机',
                    'tag' => 'n',
                ],
                [
                    'word' => '学院', 'tag' => 'n',
                ],
                [
                    'word' => '手扶拖拉机',
                    'tag' => 'n',
                ],
                [
                    'word' => '专业', 'tag' => 'n',
                ],
                [ 'word' => '的', 'tag' => 'uj' ],
                [ 'word' => '。', 'tag' => 'x' ],
                [
                    'word' => '不用', 'tag' => 'v',
                ],
                [
                    'word' => '多久', 'tag' => 'm',
                ],
                [ 'word' => '，', 'tag' => 'x' ],
                [ 'word' => '我', 'tag' => 'r' ],
                [ 'word' => '就', 'tag' => 'd' ],
                [ 'word' => '会', 'tag' => 'v' ],
                [
                    'word' => '升职', 'tag' => 'v',
                ],
                [
                    'word' => '加薪',
                    'tag' => 'nr',
                ],
                [ 'word' => '，', 'tag' => 'x' ],
                [
                    'word' => '当上', 'tag' => 't',
                ],
                [
                    'word' => 'CEO',
                    'tag' => 'eng',
                ],
                [ 'word' => '，', 'tag' => 'x' ],
                [
                    'word' => '走上', 'tag' => 'v',
                ],
                [
                    'word' => '人生', 'tag' => 'n',
                ],
                [
                    'word' => '巅峰', 'tag' => 'n',
                ],
                [ 'word' => '。', 'tag' => 'x' ],
            ],
            $this->makeFFI()->tag('我是拖拉机学院手扶拖拉机专业的。不用多久，我就会升职加薪，当上CEO，走上人生巅峰。')
        );

        $this->assertEquals(
            [
                [
                    'word' => '今天', 'tag' => 't',
                ],
                [
                    'word' => '纽约',
                    'tag' => 'ns',
                ],
                [ 'word' => '的', 'tag' => 'uj' ],
                [
                    'word' => '天气', 'tag' => 'n',
                ],
                [
                    'word' => '真好', 'tag' => 'd',
                ],
                [ 'word' => '啊', 'tag' => 'zg' ],
                [ 'word' => '，', 'tag' => 'x' ],
                [
                    'word' => '京华',
                    'tag' => 'nz',
                ],
                [
                    'word' => '大酒店',
                    'tag' => 'n',
                ],
                [ 'word' => '的', 'tag' => 'uj' ],
                [
                    'word' => '张尧', 'tag' => 'x',
                ],
                [
                    'word' => '经理', 'tag' => 'n',
                ],
                [ 'word' => '吃', 'tag' => 'v' ],
                [ 'word' => '了', 'tag' => 'ul' ],
                [
                    'word' => '一只', 'tag' => 'm',
                ],
                [
                    'word' => '北京烤鸭',
                    'tag' => 'n',
                ],
                [ 'word' => '。', 'tag' => 'x' ],
            ],
            $this->makeFFI()->tag('今天纽约的天气真好啊，京华大酒店的张尧经理吃了一只北京烤鸭。')
        );
    }

    public function testAddWord()
    {
        $this->assertEquals(
            ['西湖', '花园', '小区', '很大'],
            $this->makeFFI()->cut('西湖花园小区很大', false)
        );

        $this->assertEquals(
            ['西湖花园', '小区', '很大'],
            $this->makeFFI()->addWord('西湖花园')->cut('西湖花园小区很大', false)
        );

        $this->assertEquals(
            ['讥', '䶯', '䶰', '䶱', '䶲', '䶳', '䶴', '䶵', '𦡦'],
            $this->makeFFI()->cut('讥䶯䶰䶱䶲䶳䶴䶵𦡦', false)
        );

        $ffi = $this->makeFFI()->addWord('讥䶯䶰䶱䶲䶳')->addWord('䶴䶵𦡦');
        $this->assertEquals(
            ['讥䶯䶰䶱䶲䶳', '䶴䶵𦡦'],
            $ffi->cut('讥䶯䶰䶱䶲䶳䶴䶵𦡦', false)
        );

        $ffi = $this->makeFFI()->addWord('讥䶯䶰䶱䶲䶳', 66, 'n');
        $this->assertEquals(
            [['word' => '讥䶯䶰䶱䶲䶳', 'tag' => 'n']],
            $ffi->tag('讥䶯䶰䶱䶲䶳')
        );
        $this->assertEquals(
            66,
            $ffi->suggestFrequency('讥䶯䶰䶱䶲䶳')
        );

        // without tag
        $ffi = $this->makeFFI()->addWord('讥䶯䶰䶱䶲䶳', 42);
        $this->assertEquals(
            [['word' => '讥䶯䶰䶱䶲䶳', 'tag' => '']],
            $ffi->tag('讥䶯䶰䶱䶲䶳')
        );
        $this->assertEquals(
            42,
            $ffi->suggestFrequency('讥䶯䶰䶱䶲䶳')
        );

        // without frequency
        $ffi = $this->makeFFI()->addWord('讥䶯䶰䶱䶲䶳', null, 'i');
        $this->assertEquals(
            [['word' => '讥䶯䶰䶱䶲䶳', 'tag' => 'i']],
            $ffi->tag('讥䶯䶰䶱䶲䶳')
        );
        // TODO: why?
        $this->assertEquals(
            2,
            $ffi->suggestFrequency('讥䶯䶰䶱䶲䶳')
        );
    }

    public function testSuggestFreq()
    {
        $this->assertEquals(
            348,
            $this->makeFFI()->suggestFrequency('中出')
        );

        $this->assertEquals(
            1263,
            $this->makeFFI()->suggestFrequency('出了')
        );

        $this->assertEquals(
            2,
            $this->makeFFI()->suggestFrequency('🚀')
        );
    }

    public function testWithDictionary()
    {
        $ffi = $this->makeFFI();

        $this->assertEquals(
            ['真香', '警告'],
            $ffi->cut('真香警告')
        );

        $this->assertEquals(
            1,
            $ffi->suggestFrequency('真香警告')
        );

        $this->assertEquals(
            [
                ['word' => '真香', 'tag' => 'x'],
                ['word' => '警告', 'tag' => 'n'],
            ],
            $ffi->tag('真香警告')
        );

        // use dictionary
        $ffi->useDictionary(__DIR__ . '/dict.txt');

        $this->assertEquals(
            ['真香警告'],
            $ffi->cut('真香警告')
        );

        $this->assertEquals(
            42,
            $ffi->suggestFrequency('真香警告')
        );

        $this->assertEquals(
            [['word' => '真香警告', 'tag' => 'i']],
            $ffi->tag('真香警告')
        );
    }

    public function testNotExistsDictionaryPath()
    {
        $this->expectExceptionMessage('字典文件路径错误');

        $this->makeFFI()->useDictionary('null.txt');
    }
}
