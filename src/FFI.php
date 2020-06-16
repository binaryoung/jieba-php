<?php
declare(strict_types=1);

namespace Binaryoung\Jieba;

use FFI as StdFFI;
use FFI\CData;
use RuntimeException;

class FFI
{
    /**
     * The loaded FFI instance
     */
    protected StdFFI $ffi;

    /**
     * The jieba instance
     */
    protected CData $jieba;

    /**
     * The TFIDF instance
     */
    protected ?CData $tfidf = null;

    /**
     * Construct
     */
    public function __construct(string $headerPath, string $libraryPath, string $dictionaryPath)
    {
        // create FFI object
        $this->ffi = StdFFI::cdef(file_get_contents($headerPath), $libraryPath);

        // create jieba object
        $this->jieba = $this->ffi->jieba_with_dict($dictionaryPath);
    }

    /**
     * Clean
     */
    public function __destruct()
    {
        $this->ffi->jieba_free($this->jieba);

        if (! is_null($this->tfidf)) {
            $this->ffi->jieba_tfidf_free($this->tfidf);
        }
    }

    /**
     * Convert FFIWords to PHP Array
     *
     * @param CData $ffiWords
     * @return array
     */
    protected function fromFFIWordsToArray(CData $ffiWords): array
    {
        $words = [];

        for ($i = 0; $i < $ffiWords->len; ++$i) {
            $words[] = StdFFI::string($ffiWords->words[$i]->data, $ffiWords->words[$i]->len);
        }

        // Free FFIWords
        $this->freeFFIWords($ffiWords);

        return $words;
    }

    /**
     * Free FFIWords
     *
     * @param CData $ffiWords
     * @return void
     */
    protected function freeFFIWords(CData $ffiWords): void
    {
        $this->ffi->jieba_words_free($ffiWords);
    }

    /**
     * Cut
     *
     * @param string $sentence
     * @param bool $hmm
     * @return array
     */
    public function cut(string $sentence, bool $hmm = true): array
    {
        $words = $this->ffi->jieba_cut($this->jieba, $sentence, strlen($sentence), $hmm);

        return $this->fromFFIWordsToArray($words);
    }

    /**
     * Cut all
     *
     * @param string $sentence
     * @return array
     */
    public function cutAll(string $sentence): array
    {
        $words = $this->ffi->jieba_cut_all($this->jieba, $sentence, strlen($sentence));

        return $this->fromFFIWordsToArray($words);
    }

    /**
     * Cut For Search
     *
     * @param string $sentence
     * @param bool $hmm
     * @return array
     */
    public function cutForSearch(string $sentence, bool $hmm = true): array
    {
        $words = $this->ffi->jieba_cut_for_search($this->jieba, $sentence, strlen($sentence), $hmm);

        return $this->fromFFIWordsToArray($words);
    }

    /**
     * TFIDF Extract
     *
     * @param string $sentence
     * @param int $topK
     * @param array $allowedPOS
     * @return array
     */
    public function TFIDFExtract(string $sentence, int $topK = 20, array $allowedPOS = []): array
    {
        // Init TFIDF
        $this->tfidf ??= $this->ffi->jieba_tfidf_new($this->jieba);

        // Convert allowedPOS PHP String Array to C String Array
        $ffiAllowedPOS = count($allowedPOS) === 0 ? $this->ffi->new('char**') : $this->ffi->new(sprintf('char*[%d]', count($allowedPOS)));

        foreach ($allowedPOS as $index => $item) {
            // Convert PHP String to C String
            $CString = $this->ffi->new(sprintf('char[%d]', strlen($item) + 1), false, true);
            // Copy PHP String to C String
            StdFFI::memcpy($CString, $item, strlen($item));

            $ffiAllowedPOS[$index] = $CString;
        }

        $words = $this->ffi->jieba_tfidf_extract($this->tfidf, $sentence, strlen($sentence), $topK, $ffiAllowedPOS, count($allowedPOS));

        // Free Allowed Post Item C String
        for ($i = 0; $i < count($allowedPOS); $i++) {
            // FIX ME: can't free data on heap
            // StdFFI::free($ffiAllowedPOS[$i]);
        }

        return $this->fromFFIWordsToArray($words);
    }

    /**
     * TextRank Extract
     *
     * @param string $sentence
     * @param int $topK
     * @param array $allowedPOS
     * @return array
     */
    public function textRankExtract(string $sentence, int $topK = 20, array $allowedPOS = []): array
    {
        // Convert allowedPOS PHP String Array to C String Array
        $ffiAllowedPOS = count($allowedPOS) === 0 ? $this->ffi->new('char**') : $this->ffi->new(sprintf('char*[%d]', count($allowedPOS)));

        foreach ($allowedPOS as $index => $item) {
            // Convert PHP String to C String
            $CString = $this->ffi->new(sprintf('char[%d]', strlen($item) + 1), false, true);
            // Copy PHP String to C String
            StdFFI::memcpy($CString, $item, strlen($item));

            $ffiAllowedPOS[$index] = $CString;
        }

        $words = $this->ffi->jieba_textrank_extract($this->jieba, $sentence, strlen($sentence), $topK, $ffiAllowedPOS, count($allowedPOS));

        // Free Allowed Post Item C String
        for ($i = 0; $i < count($allowedPOS); $i++) {
            // FIX ME: can't free data on heap
            // StdFFI::free($ffiAllowedPOS[$i]);
        }

        return $this->fromFFIWordsToArray($words);
    }

    /**
     * Tokenize
     *
     * @param string $sentence
     * @param string $mode
     * @param bool $hmm
     * @return array
     */
    public function tokenize(string $sentence, string $mode = 'default', bool $hmm = true): array
    {
        $tokens = $this->ffi->jieba_tokenize(
            $this->jieba,
            $sentence,
            strlen($sentence),
            $mode === 'search' ? 1 : 0,
            $hmm
        );

        return $this->fromFFITokensToArray($tokens);
    }

    /**
     * Convert FFI Tokens to PHP Array
     *
     * @param CData $ffiTokens
     * @return array
     */
    protected function fromFFITokensToArray(CData $ffiTokens): array
    {
        $tokens = [];

        for ($i = 0; $i < $ffiTokens->len; ++$i) {
            $tokens[] = [
                'word' => StdFFI::string($ffiTokens->tokens[$i]->word->data, $ffiTokens->tokens[$i]->word->len),
                'start' => $ffiTokens->tokens[$i]->start,
                'end' => $ffiTokens->tokens[$i]->end,
            ];
        }

        // Free FFI Tokens
        $this->freeFFITokens($ffiTokens);

        return $tokens;
    }

    /**
     * Free FFI String
     *
     * @param CData $ffiString
     * @return void
     */
    protected function freeFFIString(CData $ffiString): void
    {
        $this->ffi->jieba_str_free(StdFFI::addr($ffiString));
    }

    /**
     * Free FFI Tokens
     *
     * @param CData $ffiTokens
     * @return void
     */
    protected function freeFFITokens(CData $ffiTokens): void
    {
        $this->ffi->jieba_tokens_free($ffiTokens);
    }

    /**
     * Tag
     *
     * @param string $sentence
     * @param bool $hmm
     * @return array
     */
    public function tag(string $sentence, bool $hmm = true): array
    {
        $tags = $this->ffi->jieba_tag($this->jieba, $sentence, strlen($sentence), $hmm);

        return $this->fromFFITagsToArray($tags);
    }

    /**
     * Convert FFI Tags to PHP Array
     *
     * @param CData $ffiTags
     * @return array
     */
    protected function fromFFITagsToArray(CData $ffiTags): array
    {
        $tags = [];

        for ($i = 0; $i < $ffiTags->len; ++$i) {
            // dump(is_int(StdFFI::cast('char*', $ffiTags->tags[$i]->word->data)));
            $tags[] = [
                'word' => StdFFI::string($ffiTags->tags[$i]->word->data, $ffiTags->tags[$i]->word->len),
                'tag' => StdFFI::string($ffiTags->tags[$i]->tag->data, $ffiTags->tags[$i]->tag->len),
            ];
        }

        // Free FFI Tags
        $this->freeFFITags($ffiTags);

        return $tags;
    }

    /**
     * Free FFI Tags
     *
     * @param CData $ffiTags
     * @return void
     */
    protected function freeFFITags(CData $ffiTags): void
    {
        $this->ffi->jieba_tags_free($ffiTags);
    }

    /**
     * Add Word
     *
     * @param string $word
     * @param int|null $frequency
     * @param string|null $tag
     * @return self
     */
    public function addWord(string $word, ?int $frequency = null, ?string $tag = null): self
    {
        $this->ffi->jieba_add_word(
            $this->jieba,
            $word,
            strlen($word),
            is_null($frequency) ? -1 : $frequency,
            $tag,
            is_null($tag) ? 0 : strlen($tag)
        );

        return $this;
    }

    /**
     * Suggest Frequency
     *
     * @param string $segment
     * @return int
     */
    public function suggestFrequency(string $segment): int
    {
        return $this->ffi->jieba_suggest_freq($this->jieba, $segment, strlen($segment));
    }

    /**
     * Use Customized Dictionary
     * 这会清空默认字典，想添加新词请使用addWord
     *
     * @param string $path
     * @return self
     */
    public function useDictionary(string $path): self
    {
        if (! file_exists($path)) {
            throw new RuntimeException('字典文件路径错误');
        }

        $this->ffi->jieba_load_dict($this->jieba, $path);

        return $this;
    }
}
