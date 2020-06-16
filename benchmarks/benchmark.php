<?php
ini_set('memory_limit', '1024M');

require __DIR__.'/../vendor/autoload.php';

use Binaryoung\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Jieba as FukuballJieba;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\StreamOutput;

$novel = explode('\n', file_get_contents(__DIR__ . '/../data/weicheng.txt'));

$output = new StreamOutput(fopen('php://stdout', 'w'));
$output->writeln('<question>循环50次对围城每行文字作为一个句子进行分词，分词算法都采用HMM模式。</question>');

/**
 * binaryoung/jieba-php
 */
$output->writeln('');
$output->writeln('<info>binaryoung/jieba-php开始测试：</info>');
$progressBar = new ProgressBar($output, 50);

$start = microtime(true);

$jieba = new Jieba;
for ($i = 0; $i < 50; $i++) {
    foreach ($novel as $sentence) {
        $jieba->cut($sentence);
    }
    $progressBar->advance();
}

$time = microtime(true) - $start;
$memory = memory_get_usage(true);
$peakMemory = memory_get_peak_usage(true);
$progressBar->finish();
unset($jieba);

$output->writeln('');
$output->writeln(sprintf('<info>binaryoung/jieba-php总耗时：%.3fs，单次耗时：%.3fs, 内存占用：%.2fMB, 内存峰值：%.2fMB</info>', $time, $time / 50, $memory / 1024 / 1024, $peakMemory / 1024 / 1024));


/**
 * fukuball/jieba-php
 */
$output->writeln('');
$output->writeln('<info>fukuball/jieba-php开始测试：</info>');
$progressBar = new ProgressBar($output, 50);

$start = microtime(true);

FukuballJieba::init();
Finalseg::init();

for ($i = 0; $i < 50; $i++) {
    foreach ($novel as $sentence) {
        FukuballJieba::cut($sentence);
    }
    $progressBar->advance();
}

$fTime = microtime(true) - $start;
$fMemory = memory_get_usage(true);
$fPeakMemory = memory_get_peak_usage(true);
$progressBar->finish();

$output->writeln('');
$output->writeln(sprintf('<info>jukuball/jieba-php总耗时：%.3fs，单次耗时：%.3fs，内存占用：%.2fMB, 内存峰值：%.2fMB</info>', $fTime, $fTime / 50, $fMemory / 1024 / 1024, $fPeakMemory / 1024 / 1024));


/**
 * 对比
 */
$output->writeln('');
$table = new Table($output);
$table->setHeaders(['名称', '耗时', '单次耗时', '内存占用', '内存峰值'])
      ->setRows([
          [
              'jukuball/jieba-php',
              sprintf('%.3f', $fTime),
              sprintf('%.3f', $fTime / 50),
              sprintf('%.2fMB', $fMemory / 1024 / 1024),
              sprintf('%.2fMB', $fPeakMemory / 1024 / 1024)
          ],
          [
              'binaryoung/jieba-php',
              sprintf('%.3f', $time),
              sprintf('%.3f', $time) / 50,
              sprintf('%.2fMB', $memory / 1024 / 1024),
              sprintf('%.2fMB', $peakMemory / 1024 / 1024)
          ],
          new TableSeparator(),
          [
              '差值',
              sprintf('↓%.2f%%', ($fTime - $time) / $time * 100),
              sprintf('↓%.2f%%', (($fTime / 50) - ($time / 50)) / ($time / 50) * 100),
              sprintf('↓%.2f%%', ($fMemory - $memory) / $memory * 100),
              sprintf('↓%.2f%%', ($fPeakMemory - $peakMemory) / $peakMemory * 100),
          ],
      ]);
$table->render();
