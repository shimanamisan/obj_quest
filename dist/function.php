<?php

ini_set('log_errors', 'on');  //ログを取るか
ini_set('error_log', 'php.log');  //ログの出力ファイルを指定
session_start(); //セッション使う

//===================
//デバッグログ関数
//===================
//デバッグフラグ
$debug_flg = true; //falseにすればログ出ない(いちいちdebug関数を消しにいく必要がない)
//デバッグログ関数
function debug($str)
{
    global $debug_flg;
    if (!empty($debug_flg)) {
        error_log($str);
    }
}

// インスタンスから情報を引き出す
function getStatus($instance)
{
    debug("渡ってきた引数はこちらです。function.php" . print_r($instance, true));

    if (get_class($instance) === 'Monster') {
        $array = [
            "hp" => $instance->getHp(),
            "name" => $instance->getName(),
            "img" => $instance->getImg(),
        ];

        return $array;
    } elseif (get_class($instance) === 'Human') {
        $array = [
            "hp" => $instance->getHp(),
            "name" => $instance->getName(),
            "lv" => $instance->getLv(),
            ];
    
        return $array;
    } elseif (get_class($instance) === "MagicMonster") {
        $array = [
            "hp" => $instance->getHp(),
            "name" => $instance->getName(),
            "img" => $instance->getImg(),
        ];
        return $array;
    } else {
        // 条件に当てはまらなければ空配列を返す
        return $array = [];
    }
}
