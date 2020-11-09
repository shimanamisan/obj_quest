<?php

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
      $attackPoint = (int) $attackPoint;
      History::set($this->getName() . "のクリティカルヒット！！");
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

  public function __construct(
    $name,
    $sex,
    $hp,
    $mp,
    $lv,
    $attackMin,
    $attackMax
  ) {
    $this->name = $name;
    // $this->sex = $sex;
    $this->hp = $hp;
    $this->mp = $mp;
    $this->lv = $lv;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
    $this->defaultHp = $hp;
    $this->setSex($sex);
    $this->setMagicAttack();
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

      History::set($this->getName() . " は身を守っている！");
    }
  }

  // 魔法攻撃用メソッド
  public function humanMagicAttack($targetObj, $magicType)
  {
    $attackPoint = [50, 80, 90];

    debug(" 渡ってきた魔法攻撃のタイプです " . $magicType);
    debug(" ");
    switch ($magicType) {
      case $magicType === "flame":
        debug("炎の魔法を呼び出しています。");
        debug("");
        $magicAttackPoint = $attackPoint[mt_rand(0, 2)];
        History::set($this->getName() . "は火の魔法を唱えた！！");

        // 対象オブジェクトの現在のHPを取得し、アタックポイント分を引いた値を引数に代入して、HPプロパティにセットする
        $targetObj->setHp($targetObj->getHp() - $magicAttackPoint);
        History::set("{$magicAttackPoint}ポイントのダメージ！");
        break;
      case $magicType === "ice":
        debug("氷の魔法を呼び出しています。");
        debug("");
        $magicAttackPoint = $attackPoint[mt_rand(0, 2)];
        History::set($this->getName() . "は氷の魔法を唱えた！！");

        // 対象オブジェクトの現在のHPを取得し、アタックポイント分を引いた値を引数に代入して、HPプロパティにセットする
        $targetObj->setHp($targetObj->getHp() - $magicAttackPoint);
        History::set("{$magicAttackPoint}ポイントのダメージ！");
        break;
      case $magicType === "electron":
        debug("雷の魔法を呼び出しています。");
        debug("");
        $magicAttackPoint = $attackPoint[mt_rand(0, 2)];
        History::set($this->getName() . "は雷の魔法を唱えた！！");

        // 対象オブジェクトの現在のHPを取得し、アタックポイント分を引いた値を引数に代入して、HPプロパティにセットする
        $targetObj->setHp($targetObj->getHp() - $magicAttackPoint);
        History::set("{$magicAttackPoint}ポイントのダメージ！");
        break;
      default:
        debug("humanMagicAttackメソッドのdefaultの処理です。");
        debug("  ");
        break;
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
  // コンストラクターで初期化時に、魔法攻撃の種類を配列に格納
  private function setMagicAttack()
  {
    $this->magic = ["flame", "electron", "ice"];
  }

  // 初期化時に男勇者か女勇者か判定する
  private function setSex($str)
  {
    if ($str === 1) {
      $this->name .= "(♂)";
    } else {
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
  public function __construct(
    $name,
    $img,
    $hp,
    $attackMin,
    $attackMax,
    $magicAttack
  ) {
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
$humans[] = new Human("勇者", Sex::MAN, 150, 30, 1, 20, 30);
$humans[] = new Human("勇者", Sex::WOMAN, 100, 50, 1, 10, 30);
$monsters[] = new Monster(
  "フランケンゾンビ",
  "img/monster_01.png",
  500,
  30,
  40
);
$monsters[] = new MagicMonster(
  "エルダーリッチ",
  "img/monster_02.png",
  300,
  2,
  30,
  50
);
$monsters[] = new Monster(
  "ホワイトゴースト",
  "img/monster_03.png",
  300,
  10,
  40
);
$monsters[] = new Monster(
  "ヴァンパイア男爵",
  "img/monster_04.png",
  300,
  10,
  40
);
$monsters[] = new Monster("大きなクモ", "img/monster_05.png", 300, 30, 40);
$monsters[] = new Monster("ミイラおとこ", "img/monster_06.png", 300, 20, 40);

// モンスター生成メソッド
function createMonster()
{
  global $monsters;
  $monster = $monsters[mt_rand(0, 5)];

  History::clear();
  History::set($monster->getName() . "が現れた！");

  debug("モンスターを新しく生成しました monster.php");
  debug("  ");

  $_SESSION['monster'] = $monster;
}

// 勇者生成メソッド
function createHuman()
{
  global $humans;
  $_SESSION['human'] = $humans[mt_rand(0, 1)];

  debug("勇者を新しく生成しました monster.php");
  debug("  ");
}

// ゲーム初期化メソッド
function init()
{
  History::clear();
  createMonster();
  createHuman();

  debug("ゲームを初期化しました monster.php");
  debug("  ");
}

// ゲームオーバー
function gameOver()
{
  debug("勇者が死にました。ゲームオーバーです。 monster.php");
  debug("  ");

  debug(
    "セッションの中身を確認しています。 ajax.php" . print_r($_SESSION, true)
  );
  debug("  ");
}

// 画面出力メソッド
function sanitize($str)
{
  return htmlspecialchars($str, ENT_QUOTES);
}
