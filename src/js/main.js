const $ = require("jquery");

let $attack = $("#js-attack")
let $gard = $("#js-gard")
let $magic = $("#js-magic")
let $escape = $("#js-escape")

function sleep(waitMsec){
    // 現在の時刻を格納
    let startMsec = new Date();

    // 指定ミリ秒間だけループさせる（CPUは常にビジー状態）
    while (new Date() - startMsec < waitMsec);
}

// アクション用ボタンを活性化・非活性化させるための関数
function btnDisabled(){
    $attack.prop("disabled", true)
    $gard.prop("disabled", true)
    $magic.prop("disabled", true)
    $escape.prop("disabled", true)
}
function btnActive(){   
    setTimeout( () => {
        $attack.prop("disabled", false)
        $gard.prop("disabled", false)
        $magic.prop("disabled", false)
        $escape.prop("disabled", false)
    }, 1000)
}
// ゲーム終了時の処理をまとめた関数
function endGame(){
    // ステータスの色を変更
    $(".js-game-status").css({
        "color": "red",
        "border": "solid"
    })
    setTimeout(()=>{
    // ゲームを進行して１秒待ってからゲーム終了画面を表示させる
    sleep(1000);
    // ゲーム終了後のボックスを表示させる
    $("#game_finish").text("勇者は死んでしまった！！");
    $('.js-social').css({
        'opacity': 1,
        'z-index': 1
    });
    bgm.pause();
    }, 1000)
}
// ゲーム進行履歴を書き換える関数
function gameHistory($element){
    // ajax通信で追加した新しい要素は、直接取得するのではなく、
    // 親要素からみた子要素として取得してやる
    $element.children().each(function(i){
        $(this).delay(200 * i).animate({opacity:1}, 500)
    })
}
// 魔法のBGMを実行する関数
function  magicAttackBgm($str){
    console.log(typeof $str);
    
    switch (true){
        case $str === "flame":
                flame_mp3.volume = 0.2;
                flame_mp3.play()
            break;
        case $str === "electron":
                electron_mp3.volume = 0.2;
                electron_mp3.play()
            break;
        case $str === "ice":
            ice_mp3.volume = 0.2;
            ice_mp3.play()
        break;
        default:
            console.log("魔法攻撃が正常に行われていません。")
        break;
    }
}

// BGM用ファイルを読み込む
const bgm = new Audio("./bgm/battle_bgm.mp3");
const attack_mp3 = new Audio("./bgm/attack_01.mp3");
const cure_mp3 = new Audio("./bgm/cure.mp3");
const escape_mp3 = new Audio("./bgm/escape.mp3");
const electron_mp3 = new Audio("./bgm/magic_electron.mp3");
const flame_mp3 = new Audio("./bgm/magic_flame.mp3");
const ice_mp3 = new Audio("./bgm/magic_ice.mp3");

$(function(){
/******************************************
 スタート事に戦闘BGMを流す処理
******************************************/
let $start = document.getElementById('js-start');

// モンスター討伐後、トップページへ戻るボタンを押したときの処理
$(".js-top-btn").on("click", function(e){
    e.preventDefault();
    
    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType: "json",
        data: { data: "top-page-back"}

    }).done(function(res){
        console.log(res.data);
        // PHP柄でセッションがクリアされているのでリロードさせる
        window.location.href = "/";
        return;

    }).fail(function(){
        console.log("Ajax error js-top-btn")
    })
})

// ページが読み込まれた際に、指定の要素が存在していなければ戦闘画面と判断しBGMを流す
$(window).on("load", function(){
    if($start === null){
        bgm.volume = 0.1;
        bgm.loop = true;
        bgm.play();
    }
})

// 戦闘用のメッセージを表示（初回用）
let $delayHistory = $('.u-js-history');
$delayHistory.each(function(i){
    $(this).delay(200 * i).animate({ opacity:1 }, 500)
})

/******************************************
 クリックした際にAjax通信を行って戦闘処理を進めていく
******************************************/
let $history = $(".js-history");
let $hp = $('.js-satus-hp');
let $mp = $('.js-status-mp');
let $lv = $('.js-status-lv');
let $monsterImg = $('.js-monster-img');

// 攻撃時の処理
$('#js-attack').on("click", function(e){
    // フォームの送信処理をストップさせる（画面を再読み込みさせない）
    e.preventDefault();
    btnDisabled();
    ajaxAttack("js-attack");

})

// 防御時の処理
$("#js-gard").on("click", function(e){
    // フォームの送信処理をストップさせる（画面を再読み込みさせない）
    e.preventDefault();
    btnDisabled();
    ajaxGard("js-gard");

})

// 魔法攻撃時の処理
$("#js-magic").on("click", function(e){
    e.preventDefault();
    btnDisabled();
    ajaxMagic("js-magic");
})

// 逃走時の処理
$("#js-escape").on("click", function(e){
    e.preventDefault();
    btnDisabled();
    ajaxEscape("js-escape");
})

// バトル終了時、次のモンスターを呼び出す処理
$(".js-nextMonster-btn").on("click", function(e){
    e.preventDefault();
    ajaxNext("js-next-monster");
})

/******************************************
 攻撃時の関数
******************************************/
function ajaxAttack($str){

    // 子要素を削除
    $history.empty();

    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType: "json",
        data: { data: $str }
    }).done(function(res){
        // 履歴用ボックスを書き換える
        $history.append(res.history_data);

        console.log(res.history_data);
        console.log(res.monster_data);
        console.log(res.human_data);

        if(res.human_data === 0){
            // ゲーム進行履歴管理関数
            gameHistory($history);
            // 勇者のHPを書き換える
            $hp.text(res.human_data);
            //
            endGame();

            return;
        }
        attack_mp3.volume = 0.3;
        attack_mp3.play();

        // 勇者のHPを書き換える
        $hp.text(res.human_data.hp);

        if(res.monster_data.hp <= 0){
            
            // ゲーム進行履歴管理関数
            gameHistory($history);
            
            $('.js-social').css({
                'opacity': 1,
                'z-index': 1
            });

            bgm.pause();
            return;
        }
        // モンスター討伐時に次のモンスターの画像をsrc属性に渡す
        $monsterImg.attr('src', res.monster_data.img);
        // ゲーム進行履歴管理関数
        gameHistory($history);

        if(res.monster_data.hp <= 0){
            btnDisabled();
        }

        btnActive();

    }).fail(function(){
        console.log('通信失敗！')
    });
}

/******************************************
 防御時の関数
******************************************/
function ajaxGard($str){

    // 子要素を削除
    $history.empty();

    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType: "json",
        data: { data: $str }
    }).done(function(res){
        
        $history.append(res.history_data);
        // 勇者のHPを書き換える
        $hp.text(res.human_data.hp);
        console.log(res.history_data);
        console.log(res.monster_data);
        console.log(res.human_data);
        cure_mp3.volume = 0.3;
        cure_mp3.play();
        
        if(res.human_data === 0){
            // 履歴用ボックスを書き換える
            $history.append(res.history_data);
            // 勇者のHPを書き換える
            $hp.text(res.human_data);
            // ゲーム進行履歴管理関数
            gameHistory($history);
            //
            endGame();
            
            return;
        }
        // ゲーム進行履歴管理関数
        gameHistory($history);

        btnActive();

    }).fail(function(){
        console.log('通信失敗！')
    });
}

/******************************************
 魔法攻撃時の関数
******************************************/
function ajaxMagic($str){

     // 子要素を削除
     $history.empty();

    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType: "json",
        data: { data: $str }
    }).done(function(res){
        console.log(res.history_data);
        console.log(res.monster_data);
        console.log(res.human_data);
        console.log(res.magic_data);

        magicAttackBgm(res.magic_data);

        $history.append(res.history_data);

        $hp.text(res.human_data.hp);

        if(res.human_data === 0){
            // ゲーム進行履歴管理関数
            gameHistory($history);
            // 勇者のHPを書き換える
            $hp.text(res.human_data);
            // 
            endGame();
            return;
        }

        if(res.monster_data.hp <= 0){
            
             // ゲーム進行履歴管理関数
             gameHistory($history);
            
             setTimeout(() => {
                sleep(1000);
                
                $('.js-social').css({
                    'opacity': 1,
                    'z-index': 1
                });
   
    
                bgm.pause();
                return;
             })

        }
        // ゲーム進行履歴管理関数
        gameHistory($history);

        btnActive();

    }).fail(function(){
        console.log('通信失敗！')
    });

}

/******************************************
 逃走時の関数
******************************************/
function ajaxEscape($str){

    // 子要素を削除
    $history.empty();

    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType: "json",
        data: { data: $str }
    }).done(function(res){
        console.log(res.history_data);
        console.log(res.monster_data);
        console.log(res.human_data);

        $history.append(res.history_data);

        escape_mp3.volume = 0.3;
        escape_mp3.play();

        $hp.text(res.human_data.hp);

        if(res.human_data === 0){
            // ゲーム進行履歴管理関数
            gameHistory($history);
            // 勇者のHPを書き換える
            $hp.text(res.human_data);
            // 
            endGame();
            return;
        }

        // 逃走成功時にモンスターの画像をsrc属性に渡す
        $monsterImg.attr('src', res.monster_data.img);
        // ゲーム進行履歴管理関数
        gameHistory($history);

        btnActive();

    }).fail(function(){
        console.log('通信失敗！')
    });
}

/******************************************
 次のモンスターを呼び出す関数
******************************************/
function ajaxNext($str){
 // 子要素を削除
 $history.empty();

 $.ajax({
     type: "POST",
     url: "ajax.php",
     dataType: "json",
     data: { data: $str }
 }).done(function(res){

     // PHP柄でセッションがクリアされているのでリロードさせる
     window.location.href = "/";
     return;

 }).fail(function(){
     console.log('通信失敗！')
 });
}

})
