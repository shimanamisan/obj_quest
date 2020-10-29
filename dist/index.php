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

// 生き物のクラス（抽象クラス）
abstract class Creature
{
    protected $name;
    protected $hp;
    protected $attackMin;
    protected $attackMax;

    // 名前のセット関数
    public function setName($str)
    {
        $this->name = $str;
    }

    // 名前を取得する関数
    public function getName()
    {
        return $this->name;
    }

    public function setHp($str)
    {
        $this->hp = $str;
    }

    public function getHp()
    {
        return $this->hp;
    }

    public function attack($targetObj)
    {
        $attackPoint = mt_rand($this->attackMin, $this->attackMax);

        // 10分の1の確率でクリティカルヒット
        if (!mt_rand(0, 9)) {
            $attackPoint = $attackPoint * 1.5;
            // 小数点を切り捨てる
            $attackPoint = (int)$attackPoint;
            History::set($this->getName() . "のクリティカルヒット！！");
            History::set("{$attackPoint}ポイントのダメージ！！");
        }
        // 対象オブジェクトの現在のHPを取得し、アタックポイント分を引いた値を引数に代入して、HPプロパティにセットする
        $targetObj->setHp($targetObj->getHp() - $attackPoint);
        History::set("{$attackPoint}ポイントのダメージ！");
    }
}

// 人クラス
class Human extends Creature
{
    protected $sex;
    protected $defaultHp; // 回復上限値の算出の際に使用

    public function __construct($name, $sex, $hp, $mp, $lv, $attackMin, $attackMax)
    {
        $this->name = $name;
        $this->sex = $sex;
        $this->hp = $hp;
        $this->mp = $mp;
        $this->lv = $lv;
        $this->attackMin = $attackMin;
        $this->attackMax = $attackMax;
        $this->_setUpDefaultHp($hp);
        $this->_setSex($sex);
    }

    // ガード時にHPを回復させる
    public function gardHp($str)
    {
        // 現在のHPがデフォルトのHPより小さければ回復する
        if ($this->hp < $this->defaultHp) {
            debug("デフォルトのHPより小さいので回復します。");
            debug("  ");

            History::set($this->getName() . "は身を守っている！");
            $this->hp += 20;

            // HPの上限値を超えてしまった場合は、デフォルトHPに設定する
            if ($this->hp > $this->defaultHp) {
                // HPの上限値を超えてしまった差分を算出する
                $overHp = $str - ($this->hp - $this->defaultHp);
                $this->hp = $this->defaultHp;
                History::set($this->getName() . "のHPが${overHp}回復！");
            } else {
                History::set($this->getName() . "のHPが${str}回復！");
            }
        } else {
            debug("HPの上限値に到達したので回復できません。");
            debug("  ");

            History::set($this->getName() . "は身を守っている！");
        }
    }

    public function getMp()
    {
        return $this->mp;
    }

    public function getLv()
    {
        return $this->lv;
    }
    
    public function magic()
    {
        return $this->magic[0];
    }
    
    // コンストラクターで初期化時にデフォルトHPを格納
    private function _setUpDefaultHp($str)
    {
        $this->defaultHp = $hp;
    }
    // 初期化時に男勇者か女勇者か判定する
    private function _setSex($str)
    {
        if (Sex::MAN) {
            $this->name .= "(♂)";
        } elseif (Sex::WOMAN) {
            $this->name .= "(♀)";
        }
    }
}

// モンスタークラス
class Monster extends Creature
{
    // モンスター画像表示用
    protected $img;

    public function __construct($name, $img, $hp, $attackMin, $attackMax)
    {
        $this->name = $name;
        $this->img = $img;
        $this->hp = $hp;
        $this->attackMin = $attackMin;
        $this->attackMax = $attackMax;
    }

    // 画像のファイルパスを取得
    public function getImg()
    {
        return $this->img;
    }
}

// 魔法を使えるモンスタークラス
class MagicMonster extends Monster
{
    private $magicAttack;

    // 魔法用のステータスをプロパティに追加
    public function __construct($name, $img, $hp, $attackMin, $attackMax, $magicAttack)
    {
        // インスタンス生成時に親のプロパティをそのまま継承する
        parent::__construct($name, $img, $hp, $attackMin, $attackMax);
        $this->magicAttack = $magicAttack;
    }

    // 継承している攻撃用メソッドを魔法モンスター用にオーバーライドする
    public function attack($targetObj)
    {
        // 5分の1の確率で魔法攻撃（0でなければ true の判定になる。0だったら false の判定）
        if (!mt_rand(0, 4)) {
            History::set($this->name . "の魔法攻撃！");
            $targetObj->getHp($this->getHp() - $magicAttack);
            History::set($this->magicAttack . "ポイントのダメージ！！");
        } else {
            // 魔法攻撃でなければ、親クラスから通常攻撃メソッドを呼び出す。こうすることで親クラスで通常攻撃メソッドが変更されても
            // MagicMonsterクラスでも反映される
            parent::attack($targetObj);
        }
    }
}

// インターフェースの実装
interface HistoryInterFace
{
    public static function set($str);
    public static function clear();
}

// 履歴管理クラス
class History implements HistoryInterFace
{
    public static function set($str)
    {
        // 履歴管理用のセッションが作成されていなければ作成する
        if (empty($_SESSION['history'])) {
            $_SESSION['history'] = "";
        }

        // 文字列を格納（ .= で上書きせずに続けて文字列連結する）
        $_SESSION['history'] .= "<div class='u-js-history'>{$str}</div>";
    }
    public static function clear()
    {
        unset($_SESSION['history']);
    }
}

// インスタンス生成
$humans[] = new Human("勇者", Sex::MAN, 200, 30, 1, 50, 100);
$humans[] = new Human("勇者", Sex::WOMAN, 100, 50, 1, 30, 130);
$monsters[] = new Monster("フランケンゾンビ", "img/monster_01.png", 100, 2, 40);
$monsters[] = new MagicMonster("エルダーリッチ", "img/monster_02.png", 100, 2, 20, 50);
$monsters[] = new Monster("ホワイトゴースト", "img/monster_03.png", 100, 2, 40);
$monsters[] = new Monster("ヴァンパイア男爵", "img/monster_04.png", 100, 2, 40);
$monsters[] = new Monster("大きなクモ", "img/monster_05.png", 100, 2, 40);
$monsters[] = new Monster("大怪我した人", "img/monster_06.png", 100, 2, 40);

// モンスター生成メソッド
function createMonster()
{
    global $monsters;
    $monster = $monsters[mt_rand(0, 5)];

    History::clear();
    History::set($monster->getName() . "が現れた！");
    $_SESSION['monster'] = $monster;
}

// 勇者生成メソッド
function createHuman()
{
    global $humans;
    $_SESSION['human'] = $humans[mt_rand(0, 1)];
}

// ゲーム初期化メソッド
function init()
{
    History::clear();
    createMonster();
    createHuman();
}

// ゲームオーバー
function gameOver()
{
    $_SESSION = [];
}

// 画面出力メソッド
function sanitize($str)
{
    return htmlspecialchars($str, ENT_QUOTES);
}



// POST送信されていた場合
if (!empty($_POST)) {
  
    // 攻撃判定
    $attackFlg = (!empty($_POST['attack'])) ? true : false ;
    // 逃げる判定
    $escapeFlg = (!empty($_POST['escape'])) ? true : false;
    // 防御判定
    $gardFlg = (!empty($_POST['gard'])) ? true : false;
    // 勇者魔法攻撃判定
    $magicAttackFlg = (!empty($_POST['magic_attack'])) ? true : false;
    // ゲームスタートの判定
    $startFlg = (!empty($_POST['start'])) ? true : false ;

    debug("POST送信の中身" . print_r($_POST, true));
    debug("SESSIONの中身" . print_r($_SESSION, true));

    debug("attackフラグです " . $attackFlg);
    debug("  ");
    debug("ゲームスタートフラグです " . $startFlg);
    debug("  ");

    if ($startFlg) {
        init();
        debug('ゲームがスタートしました。');
        debug('   ');
    } else {
        // 攻撃毎にゲーム進行エリアは消去する
        $_SESSION['history'] = "";
      
        // 攻撃するを押している場合
        switch (true) {
          case $attackFlg:
            // モンスターのHPを減らす
            History::set($_SESSION['human']->getName(). "の攻撃！");
            $_SESSION['human']->attack($_SESSION['monster']);

            // 勇者のHPを減らす
            History::set($_SESSION['monster']->getName() . "の攻撃！");
            $_SESSION['monster']->attack($_SESSION['human']);
            // 自分のHPが0になったらゲームオーバー
            if ($_SESSION['human']->getHp() <= 0) {
                gameOver();
            // モンスターを倒したら別のモンスターを呼び出す
            } else {
                if ($_SESSION['monster']->getHp() <= 0) {
                    History::set($_SESSION['monster']->getName() . "を倒した！！");
                    createMonster();
                }
            }
          break;
          case $escapeFlg:
            if (!mt_rand(0, 9)) {
                History::set($_SESSION['human']->getName() . "は逃げ出した！");
                createMonster();
            } else {
                History::set($_SESSION['human']->getName() . "は逃げ出した！");
                  
                History::set("しかし逃げられない！！");
            }
          break;
          case $gardFlg:
            // 回復用メソッドを呼ぶ
            $_SESSION['human']->gardHp(20);
          break;
          case $magicAttackFlg:
            // 回復用メソッドを呼ぶ
            debug("魔法攻撃判定用です。");
            debug("   ");
          break;
          default:
          break;
        }
    }
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
              <li><?php echo sanitize($_SESSION['human']->getName());?></li>
            </ul>
            <ul class="p-status__list">
              <li class="p-status__item">HP:</li>
              <li class="p-status__item__num"><?php echo sanitize($_SESSION['human']->getHp());?></li>
              <li class="p-status__item">MP:</li>
              <li class="p-status__item__num"><?php echo sanitize($_SESSION['human']->getMp());?></li>
              <li class="p-status__item">LV:</li>
              <li class="p-status__item__num"><?php echo sanitize($_SESSION['human']->getLv());?></li>
            </ul>
          </div>
          <div class="p-monster">
            <div class="p-monster__inner">
              <img src="<?php echo sanitize($_SESSION['monster']->getImg());?>" alt="">
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
                  <div class="p-history__text">
                    <p class="js-history">
                      <?php echo (!empty($_SESSION['history'])) ? trim($_SESSION['history'], " \t\n\r") : trim($_SESSION['monster']->getName()."が現れた！", " \t\n\r"); ?>
                    </p>
              </div>
            </div>
          </div>

        <?php } ?>
      </div><!-- end l-main__inner -->
    </div>
    <script src="js/bundle.min.js"></script>
    <script>
      // let test =
      //     console.log(test)

    </script>
  </body>
</html>
