<?php

require "monster.php";
require 'function.php';

// ゲーム進行メッセージを一度消去
$_SESSION['history'] = "";

debug("ajax通信時のPOSTの中身です。 " . print_r($_POST, true));

if (!empty($_POST)) {
  switch (true) {
    // 攻撃選択時の処理
    case $_POST['data'] === "js-attack":
      // モンスターのHPを減らす
      History::set($_SESSION['human']->getName() . "の攻撃！");
      $_SESSION['human']->attack($_SESSION['monster']);

      // 勇者のHPを減らす
      History::set($_SESSION['monster']->getName() . "の攻撃！");
      $_SESSION['monster']->attack($_SESSION['human']);
      // 自分のHPが0になったらゲームオーバー
      if ($_SESSION['human']->getHp() <= 0) {
        gameOver();
        // 勇者が死んだらそのフラグをレスポンスとして渡す
        $gameOver = 0;
        // 勇者のHPを減らす
        History::set($_SESSION['human']->getName() . "は死んでしまった！！");
        History::set("<br>");
        // JSON形式でレスポンスを返す
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
          'human_data' => $gameOver,
          'history_data' => $_SESSION['history'],
        ]);
        $_SESSION = [];
        return;

        // モンスターを倒したら別のモンスターを呼び出す
      } else {
        debug("現在のモンスターのステータス" . $_SESSION['monster']->getHp());
        if ($_SESSION['monster']->getHp() <= 0) {
          History::set(
            $_SESSION['monster']->getName() .
              "を倒した！！ <button class='p-history__btn'>▼</button>"
          );
        }
      }

      debug(
        "モンスターのセッションの値を取得しています ajax.php " .
          get_class($_SESSION['monster'])
      );
      debug("  ");

      // 現在のステータスを取得
      $monsterData = getStatus($_SESSION['monster']);
      $humanData = getStatus($_SESSION['human']);

      // JSON形式でレスポンスを返す
      header('Content-Type: application/json; charset=UTF-8');
      echo json_encode([
        //key => value の形で渡している
        'monster_data' => $monsterData,
        'human_data' => $humanData,
        'history_data' => $_SESSION['history'],
      ]);
      break;

    // 魔法攻撃時の処理
    case $_POST['data'] === "js-magic":
      debug("POST送信の中身" . print_r($_POST, true));
      debug("SESSION['human']の中身" . print_r($_SESSION['human'], true));

      $magicData = $_SESSION['human']->magic;

      $magicStr = $magicData[mt_rand(0, 2)];

      debug("magic : " . $magicStr);

      $_SESSION['human']->humanMagicAttack($_SESSION['monster'], $magicStr);

      // 勇者のHPを減らす
      History::set($_SESSION['monster']->getName() . "の攻撃！");
      $_SESSION['monster']->attack($_SESSION['human']);
      // 自分のHPが0になったらゲームオーバー
      if ($_SESSION['human']->getHp() <= 0) {
        gameOver();
        // 勇者が死んだらそのフラグをレスポンスとして渡す
        $gameOver = 0;
        // 勇者のHPを減らす
        History::set($_SESSION['human']->getName() . "は死んでしまった！！");
        // JSON形式でレスポンスを返す
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
          'human_data' => $gameOver,
          'history_data' => $_SESSION['history'],
        ]);
        $_SESSION = [];
        return;

        // モンスターを倒したら別のモンスターを呼び出す
      } else {
        debug("現在のモンスターのステータス" . $_SESSION['monster']->getHp());
        if ($_SESSION['monster']->getHp() <= 0) {
          History::set(
            $_SESSION['monster']->getName() .
              "を倒した！！ <button class='p-history__btn'>▼</button>"
          );
        }
      }

      // 現在のステータスを取得
      $monsterData = getStatus($_SESSION['monster']);
      $humanData = getStatus($_SESSION['human']);

      // JSON形式でレスポンスを返す
      header('Content-Type: application/json; charset=UTF-8');
      echo json_encode([
        //key => value の形で渡している
        'monster_data' => $monsterData,
        'human_data' => $humanData,
        'history_data' => $_SESSION['history'],
        'magic_data' => $magicStr,
      ]);
      break;

    // 防御選択時の処理
    case $_POST['data'] === "js-gard":
      // 回復用メソッドを呼ぶ
      $_SESSION['human']->gardHp(20);

      debug("ガード時の処理です。HPを回復しました。");
      debug(" ");

      debug("現在のモンスターのステータス" . $_SESSION['monster']->getHp());
      // 勇者のHPを減らす
      History::set($_SESSION['monster']->getName() . "の攻撃！");
      $_SESSION['monster']->attack($_SESSION['human']);
      // 自分のHPが0になったらゲームオーバー
      if ($_SESSION['human']->getHp() <= 0) {
        gameOver();
        // 勇者が死んだらそのフラグをレスポンスとして渡す
        $gameOver = 0;
        // 勇者のHPを減らす
        History::set("<br>");
        History::set($_SESSION['human']->getName() . "は死んでしまった！！");
        // JSON形式でレスポンスを返す
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
          'human_data' => $gameOver,
          'history_data' => $_SESSION['history'],
        ]);
        $_SESSION = [];
        return;

        // モンスターを倒したら別のモンスターを呼び出す
      } else {
        if ($_SESSION['monster']->getHp() <= 0) {
          History::set(
            $_SESSION['monster']->getName() .
              "を倒した！！ <button class='p-history__btn' id='js-next-monster'>▼</button>"
          );
        }
      }

      $humanData = getStatus($_SESSION['human']);

      // JSON形式でレスポンスを返す
      header('Content-Type: application/json; charset=UTF-8');
      echo json_encode([
        //key => value の形で渡している
        'human_data' => $humanData,
        'history_data' => $_SESSION['history'],
      ]);

      break;
    case $_POST['data'] === "js-next-monster":
      // 新しくゲームをはじめる
      init();
      // 現在のステータスを取得
      $monsterData = getStatus($_SESSION['monster']);
      $humanData = getStatus($_SESSION['human']);
      // JSON形式でレスポンスを返す
      header('Content-Type: application/json; charset=UTF-8');
      echo json_encode([
        //key => value の形で渡している
        'monster_data' => $monsterData,
        'human_data' => $humanData,
        'history_data' => $_SESSION['history'],
      ]);

      break;

    // 逃走時の処理
    case $_POST['data'] === "js-escape":
      debug("逃走時の確率です。" . mt_rand(0, 9));
      debug("  ");

      if (!mt_rand(0, 9)) {
        History::set($_SESSION['human']->getName() . "は逃げ出した！");
        createMonster();
      } else {
        History::set($_SESSION['human']->getName() . "は逃げ出した！");

        History::set("しかし逃げられない！！");

        // 勇者のHPを減らす
        History::set($_SESSION['monster']->getName() . "の攻撃！");
        $_SESSION['monster']->attack($_SESSION['human']);
        // 自分のHPが0になったらゲームオーバー
        if ($_SESSION['human']->getHp() <= 0) {
          gameOver();
          // 勇者が死んだらそのフラグをレスポンスとして渡す
          $gameOver = 0;
          // 勇者のHPを減らす
          History::set($_SESSION['human']->getName() . "は死んでしまった！！");
          History::set("<br>");
          // JSON形式でレスポンスを返す
          header('Content-Type: application/json; charset=UTF-8');
          echo json_encode([
            'human_data' => $gameOver,
            'history_data' => $_SESSION['history'],
          ]);
          $_SESSION = [];
          return;
        }
      }

      // 現在のステータスを取得
      $monsterData = getStatus($_SESSION['monster']);
      $humanData = getStatus($_SESSION['human']);

      // JSON形式でレスポンスを返す
      header('Content-Type: application/json; charset=UTF-8');
      echo json_encode([
        'monster_data' => $monsterData,
        'human_data' => $humanData,
        'history_data' => $_SESSION['history'],
      ]);

      break;
    case $_POST['data'] === "top-page-back":
      $_SESSION = [];
      // JSON形式でレスポンスを返す
      header('Content-Type: application/json; charset=UTF-8');
      echo json_encode([
        'data' => "セッションをクリアしました",
      ]);

      debug(
        "セッションをクリアしました。トップページ画面へ戻ります。 : " .
          print_r($_SESSION, true)
      );
      debug("  ");
      break;

    default:
      break;
  }
} else {
  // POST送信されていなければエラーを返す
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode([
    "error" => "POST送信されていません。",
  ]);
}
