<?php

class WordBingo
{
    /**
     * 実行部分
     *
     * @return void
     */
    public function exec()
    {
        // 1.ビンゴカードのサイズ入力受付
        $s = intval(trim(fgets(STDIN)));
        // 2.ビンゴカードの入力受付
        $word_bingo = [];
        for ($i = 0; $i < $s; $i++) {
            $line = trim(fgets(STDIN));
            $line_words = explode(' ', $line);
            $word_bingo[$i] = $line_words;
        }
        $this->assertWordBingo($word_bingo, $s); // ビンゴカード内容チェック

        // 3.ビンゴの抽選回数入力受付
        $n = intval(trim(fgets(STDIN)));
        // 4.抽選結果の入力受付
        for ($i = 0; $i < $n; $i++) {
            $lottery[$i] = trim(fgets(STDIN));
        }
        $this->assertLottery($lottery, $n); // ビンゴ抽選内容チェック

        // 5.ビンゴ判定の開始
        $is_bingo = $this->checkBingoLine($word_bingo, $lottery); // 横列のビンゴ判定
        if (!$is_bingo) {
            $is_bingo = $this->checkBingoRow($word_bingo, $lottery, $s); // 縦列のビンゴ判定
            if (!$is_bingo) {
                $is_bingo = $this->checkBingoLeaning($word_bingo, $lottery, $s); // 斜め列のビンゴ判定
            }
        }

        // 6.結果の標準出力
        $result = $is_bingo ? "yes" : "no";
        echo $result . PHP_EOL; // 最後は改行
    }

    /**
     * ビンゴカード内容のチェック
     *
     * @param array $word_bingo ビンゴカード
     * @param int $s ビンゴカードサイズ
     * @return void
     */
    private function assertWordBingo($word_bingo, $s)
    {
        // -- 3 ≤ S ≤ 1000
        if ($s < 3 || 1000 < $s) {
            // 規格外のビンゴカードサイズ
            exit(1);
        }

        $one_dimensional_words = array_reduce($word_bingo, 'array_merge', []);
        $words_count = count($one_dimensional_words);
        $unique_words_count = count(array_unique($one_dimensional_words));
        // -- Ai1,j1 =/ Ai2,j2((i1 ,j1) =/ (i2, j2))
        if ($words_count != $unique_words_count) {
            // ビンゴカードの単語に被りがある
            exit(1);
        }
        foreach ($word_bingo as $line) {
            if (count($line) != $s) {
                // ビンゴ単語の数が異なる
                exit(1);
            }
            foreach ($line as $word) {
                /* -- Ai,j 、wi(単語) は半角英数字のみです。スペースや記号は含まれません。
                【参考】
                https://qiita.com/grrrr/items/0b35b5c1c98eebfa5128
                https://qiita.com/grrrr/items/7c8811b5cf37d700adc4
                 */
                if (!preg_match("/^[a-zA-Z0-9]+$/", $word)) {
                    // 単語に半角英数字以外が混じっている
                    exit(1);
                }

                // -- 1 ≤ A の文字数 ≤ 100
                $strlen = strlen($word);
                if ($strlen < 1 || 100 < $strlen) {
                    // 規格外のビンゴ単語の文字数
                    exit(1);
                }
            }
        }
    }

    /**
     * ビンゴ抽選内容のチェック
     *
     * @param array $lottery 抽選内容
     * @param int $n ビンゴ抽選回数
     * @return void
     */
    private function assertLottery($lottery, $n)
    {
        // -- 1 ≤ N ≤ 2000
        if ($n < 3 || 2000 < $n) {
            // 規格外のビンゴ単語数
            exit(1);
        }

        $lottery_count = count($lottery);
        $unique_lottery_count = count(array_unique($lottery));
        // -- 1 ≤ w の文字数 ≤ 100
        if ($lottery_count != $unique_lottery_count) {
            // ビンゴ抽選に被りがある
            exit(1);
        }
        if ($lottery_count != $n) {
            // ビンゴ抽選回数が異なる
            exit(1);
        }
        foreach ($lottery as $word) {
            /* -- Ai,j 、wi(単語) は半角英数字のみです。スペースや記号は含まれません。
            【参考】
            https://qiita.com/grrrr/items/0b35b5c1c98eebfa5128
            https://qiita.com/grrrr/items/7c8811b5cf37d700adc4
             */
            if (!preg_match("/^[a-zA-Z0-9]+$/", $word)) {
                // 単語に半角英数字以外が混じっている
                exit(1);
            }

            $strlen = strlen($word);
            // -- wi =/ wj (i =/ j)
            if ($strlen < 1 || 100 < $strlen) {
                // 規格外のビンゴ単語の文字数
                exit(1);
            }
        }
    }

    /**
     * 横列のビンゴ判定
     *
     * @param array $word_bingo ビンゴカード
     * @param array $lottery 抽選単語
     * @return bool
     */
    private function checkBingoLine($word_bingo, $lottery)
    {
        $is_bingo = false;
        foreach ($word_bingo as $row => $line) {
            $is_line_bingo = true;
            foreach ($line as $word) {
                if (!in_array($word, $lottery)) {
                    // 抽選結果と一致しなかったのでビンゴなし
                    $is_line_bingo = false;
                    break;
                }
            }
            if ($is_line_bingo) {
                $is_bingo = true;
                break;
            }
        }

        return $is_bingo;
    }

    /**
     * 縦列のビンゴ判定
     *
     * @param array $word_bingo ビンゴカード
     * @param array $lottery 抽選単語
     * @param int $s ビンゴカードのサイズ
     * @return bool
     */
    private function checkBingoRow($word_bingo, $lottery, $s)
    {
        $is_bingo = false;
        // 列番号
        for ($i = 0; $i < $s; $i++) {
            $is_row_bingo = true;
            // 行番号
            for ($j = 0; $j < $s; $j++) {
                if (!in_array($word_bingo[$j][$i], $lottery)) {
                    $is_row_bingo = false;
                    break;
                }
            }
            // ビンゴになっていたら処理中断
            if ($is_row_bingo) {
                $is_bingo = true;
                break;
            }
        }
        return $is_bingo;
    }

    /**
     * 斜め列のビンゴ判定
     *
     * @param array $word_bingo ビンゴカード
     * @param array $lottery 抽選単語
     * @param int $s ビンゴカードのサイズ
     * @return bool
     */
    private function checkBingoLeaning($word_bingo, $lottery, $s)
    {
        $is_bingo = true;

        $i = 0;
        $j = 0;
        while ($i < $s) {
            if (!in_array($word_bingo[$i][$j], $lottery)) {
                $is_bingo = false;
                break;
            }
            $i++;
            $j++;
        }
        if (!$is_bingo) {
            $is_bingo = true;

            $i = 0;
            $j = $s - 1;
            while ($i < $s) {
                if (!in_array($word_bingo[$i][$j], $lottery)) {
                    $is_bingo = false;
                    break;
                }
                $i++;
                $j--;
            }
        }
        return $is_bingo;
    }
}

// 実行エントリポイント（インスタンスの生成とexec()の呼び出し）
$instance = new WordBingo();
$instance->exec();
