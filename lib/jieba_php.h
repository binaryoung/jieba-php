#include <stdarg.h>
#include <stdbool.h>
#include <stdint.h>
#include <stdlib.h>

/**
 * Tokenize mode
 */
typedef enum {
  /**
   * Default mode
   */
  Default = 0,
  /**
   * Search mode
   */
  Search = 1,
} TokenizeMode;

typedef struct CJieba CJieba;

typedef struct CJiebaTFIDF CJiebaTFIDF;

/**
 * Represents a string.
 */
typedef struct {
  char *data;
  uintptr_t len;
  bool owned;
} FfiStr;

typedef struct {
  FfiStr *words;
  uintptr_t len;
} CJiebaWords;

typedef struct {
  FfiStr word;
  FfiStr tag;
} CJiebaTag;

typedef struct {
  CJiebaTag *tags;
  uintptr_t len;
} CJiebaTags;

typedef struct {
  FfiStr word;
  uintptr_t start;
  uintptr_t end;
} CJiebaToken;

typedef struct {
  CJiebaToken *tokens;
  uintptr_t len;
} CJiebaTokens;

uintptr_t jieba_add_word(CJieba *j,
                         const char *word,
                         uintptr_t len,
                         intptr_t freq,
                         const char *tag,
                         uintptr_t tag_len);

CJiebaWords *jieba_cut(CJieba *j, const char *sentence, uintptr_t len, bool hmm);

CJiebaWords *jieba_cut_all(CJieba *j, const char *sentence, uintptr_t len);

CJiebaWords *jieba_cut_for_search(CJieba *j, const char *sentence, uintptr_t len, bool hmm);

CJieba *jieba_empty(void);

void jieba_free(CJieba *j);

void jieba_load_dict(CJieba *j, char *dict_path);

/**
 * Frees a ffi str.
 *
 * If the string is marked as not owned then this function does not
 * do anything.
 */
void jieba_str_free(FfiStr *s);

uintptr_t jieba_suggest_freq(CJieba *j, const char *segment, uintptr_t len);

CJiebaTags *jieba_tag(CJieba *j, const char *sentence, uintptr_t len, bool hmm);

void jieba_tags_free(CJiebaTags *c_tags);

CJiebaWords *jieba_textrank_extract(CJieba *j,
                                    const char *sentence,
                                    uintptr_t len,
                                    uintptr_t top_k,
                                    char *const *allowed_pos,
                                    uintptr_t allowed_pos_len);

CJiebaWords *jieba_tfidf_extract(CJiebaTFIDF *t,
                                 const char *sentence,
                                 uintptr_t len,
                                 uintptr_t top_k,
                                 char *const *allowed_pos,
                                 uintptr_t allowed_pos_len);

void jieba_tfidf_free(CJiebaTFIDF *t);

CJiebaTFIDF *jieba_tfidf_new(CJieba *j);

CJiebaTokens *jieba_tokenize(CJieba *j,
                             const char *sentence,
                             uintptr_t len,
                             TokenizeMode mode,
                             bool hmm);

void jieba_tokens_free(CJiebaTokens *c_tokens);

CJieba *jieba_with_dict(char *dict_path);

void jieba_words_free(CJiebaWords *c_words);
