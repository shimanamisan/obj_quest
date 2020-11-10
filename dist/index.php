<?php
require "monster.php";
require "function.php";

// POST送信されていた場合
if (!empty($_POST)) {
  // ゲームスタートの判定
  $startFlg = !empty($_POST['start']) ? true : false;

  debug("POST送信の中身" . print_r($_POST, true));
  debug("SESSIONの中身" . print_r($_SESSION, true));

  if ($startFlg) {
    init();
    debug('ゲームがスタートしました。');
    debug('   ');
  }
}
?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script> 
    <link rel="stylesheet" href="css/style.min.css" />
    <title>オブジェクエスト</title>
  </head>
  <body>
    <div class="l-main">
      <div class="l-main__inner">
        <?php if (empty($_SESSION)) { ?>
          
          <div class="l-main__start">
            <div class="c-anime__img">
              <img src="img/top_logo.png" alt="">
            </div>
  
            <form action="" method="post" id="js-start" class="c-anime__text">
              <ul>
                <li class="c-btn__start">
                  <input class="p-attack__submit" type="submit" name="start" value="スタート">
                </li>
              </ul>
            </form>
          </div>

        <?php } else { ?>

          <div class="p-status js-game-status">
            <ul class="p-status__name">
              <li><?php echo sanitize($_SESSION['human']->getName()); ?></li>
            </ul>
            <ul class="p-status__list">
              <li class="p-status__item">HP:</li>
              <li class="p-status__item__num js-satus-hp"><?php echo sanitize(
                $_SESSION['human']->getHp()
              ); ?></li>
              <li class="p-status__item">MP:</li>
              <li class="p-status__item__num js-status-mp"><?php echo sanitize(
                $_SESSION['human']->getMp()
              ); ?></li>
              <li class="p-status__item">LV:</li>
              <li class="p-status__item__num js-status-lv"><?php echo sanitize(
                $_SESSION['human']->getLv()
              ); ?></li>
            </ul>
          </div>
          <div class="p-monster">
            <div class="p-monster__inner">
              <img src="<?php echo sanitize(
                $_SESSION['monster']->getImg()
              ); ?>"  class="js-monster-img" alt="">
            </div>
          </div>
          <div class="p-social js-social">
            <div class="p-social__view">
              <div class="p-social__title">
                <h2 id="game_finish">
                  モンスターを倒しました！
                </h2>
              </div>
              <div class="p-social__text">
                <p>
                  たたかいの結果をツイートする
                </p>
                <div class="p-social__link">
                 <?php echo sanitize(History::linkGenerate()); ?>
                </div>
              </div>
              <div class="p-social__btn">
                  <button class="c-btn js-top-btn">トップへ戻る</button>
                  <button class="c-btn js-nextMonster-btn">次のモンスターと闘う</button>
              </div>
            </div>
          </div>
          <div class="l-main__common">
            <div class="p-attack">
              <form id="js-post">
                <ul class="p-attack__list">
                  <li class="p-attack__text">
                    <input class="p-attack__submit" id="js-attack" type="submit" name="attack" value="たたかう">
                  </li>
                  <li class="p-attack__text">
                    <input class="p-attack__submit" id="js-magic" type="submit" name="magic_attack" value="まほう">
                  </li>
                  <li class="p-attack__text">
                  <input class="p-attack__submit" id="js-gard" type="submit" name="gard" value="ぼうぎょ">
                  </li>
                  <li class="p-attack__text">
                  <input class="p-attack__submit" id="js-escape" type="submit" name="escape" value="にげる">
                  </li>
                </ul>
              </form>
            </div>
            <div class="p-history">
                  <div class="p-history__text">
                    <div class="js-history">
                      
                      <?php echo !empty($_SESSION['history'])
                        ? $_SESSION['history']
                        : $_SESSION['monster']->getName() . "が現れた！"; ?>
                    
                    </div>
              </div>
            </div>
          </div>

        <?php } ?>
      </div><!-- end l-main__inner -->
    </div>
    <script src="js/bundle.min.js"></script>
  </body>
</html>
