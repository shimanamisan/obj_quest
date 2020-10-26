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

// モンスター格納用
$monster = [];

// 人間の性別クラス
class Sex
{
    const MAN = 1;
    const WOMAN = 2;
}

// 生き物のクラス

if (!empty($_POST['escape'])) {
    $_SESSION = [];
    session_destroy();
    debug("セッションが削除されました");
    debug("SESSIONの中身" . print_r($_SESSION, true));
}


if (!empty($_POST['start'])) {
    $_SESSION['start'] = true;
    debug("セッションがセットされました"  . print_r($_SESSION, true));
}

// POST送信されていた場合
if (!empty($_POST)) {
    // 攻撃判定
    $attackFlg = (empty($_POST['attack'])) ? true : false ;
    // ゲームスタートの判定
    $startFlg = (empty($_POST['start'])) ? true : false ;

    debug("POST送信の中身" . print_r($_POST, true));
    debug("SESSIONの中身" . print_r($_SESSION, true));

    if ($startFlg) {
    }
} else {
}


?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/style.min.css" />
    <title>オブジェクエスト</title>
  </head>
  <body>
    <div class="l-main">
      <div class="l-main__inner">

        <?php if (empty($_SESSION)) { ?>

          <form action="" method="post">
            <ul>
              <li class="p-attack__text">
                <input class="p-attack__submit" type="submit" name="start" value="スタート">
              </li>
            </ul>
          </form>

        <?php } else { ?>

          <div class="p-status">
            <ul class="p-status__name">
              <li>ゆうしゃ</li>
            </ul>
            <ul class="p-status__list">
              <li class="p-status__item">HP:</li>
              <li class="p-status__item__num">30</li>
              <li class="p-status__item">MP:</li>
              <li class="p-status__item__num">15</li>
              <li class="p-status__item">LV:</li>
              <li class="p-status__item__num">15</li>
            </ul>
          </div>
          <div class="p-monster">
            <div class="p-monster__inner">
              <img src="./img/monster_01.png" alt="">
            </div>
          </div>
          <div class="l-main__common">
            <div class="p-attack">
            <form action="" method="post">
              <ul class="p-attack__list">
                <li class="p-attack__text">
                  <input class="p-attack__submit" type="submit" name="attack" value="たたかう">
                </li>
                <li class="p-attack__text">
                  <input class="p-attack__submit" type="submit" name="magic_attack" value="まほう">
                </li>
                <li class="p-attack__text">
                <input class="p-attack__submit" type="submit" name="gard" value="ぼうぎょ">
                </li>
                <li class="p-attack__text">
                <input class="p-attack__submit" type="submit" name="escape" value="にげる">
                </li>
              </ul>
            </form>
            </div>
            <div class="p-history">
                <ul>
                  <li>
                    ゆうしゃの攻撃！
                  </li>
                </ul>
            </div>
          </div>

        <?php } ?>
      </div><!-- end l-main__inner -->
    </div>
    <script src="js/bundle.min.js"></script>
  </body>
</html>
