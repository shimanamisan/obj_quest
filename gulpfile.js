const gulp = require("gulp");
// gulp-minify-cssが非推奨になり、gulp-clean-cssを使用するようアナウンスが出ている
// https://www.npmjs.com/package/gulp-minify-css
const sass = require("gulp-sass"); // sassを使えるようにする
const sassGlob = require("gulp-sass-glob"); // 複数のimport文をまとめる
const changed = require("gulp-changed");
const imagemin = require("gulp-imagemin"); // 画像を圧縮する
const browserSync = require("browser-sync"); // ファイル変更時にブラウザを自動リロードする
const plumber = require("gulp-plumber"); // エラーが発生しても強制終了させない
const notify = require("gulp-notify"); // エラー発生時のアラート出力
const postcss = require("gulp-postcss"); // PostCSS利用
const autoprefixer = require("autoprefixer"); // 自動でベンダープレフィックスを記述する
const sourcemaps = require("gulp-sourcemaps"); // ソースマップ作成
const uglify = require("gulp-uglify"); // jsファイルを圧縮する
const stripDebug = require("gulp-strip-debug"); // console.logやalertを削除する
const rename = require("gulp-rename"); // ファイル名を変更するプラグインを追加
const browserify = require("browserify"); // jsファイルなどビルドする
const babelify = require("babelify"); // babelify は Browserify 用の Babel 変換ライブラリ
const source = require("vinyl-source-stream"); // gulpで使用するvinylオブジェクトに変換するためのもの。Browserify を扱う際に利用する
const gulp_connect = require("gulp-connect-php"); // phpを扱えるようにする
const streamify = require("gulp-streamify"); // gulpでストリームモードを利用できるようにする

// ファイルパスを予め設定
var paths = {
  srcDir: "src",
  dstDir: "dist",
};

// browserifyを使ってJSファイルをビルド（開発環境）
const js_Build = function (done) {
  browserify({
    entries: [paths.srcDir + "/js/main.js"],
    debug: true,
  })
    .transform(babelify, { presets: ["@babel/preset-env"] })
    .bundle()
    .on("error", function (e) {
      console.log(e);
    })
    .pipe(source("bundle.js")) // 引数に出力後のファイル名を記述
    .pipe(
      plumber(
        //エラーが出ても処理を止めない
        {
          errorHandler: notify.onError("Error: <%= error.message %>"),
        }
      )
    )
    .pipe(sourcemaps.write("./"))
    .pipe(
      rename({
        extname: ".min.js", // 圧縮後は min が追記されたファイル名になる
      })
    )
    .pipe(gulp.dest(paths.dstDir + "/js/"));
  done();
};

// 本番環境に上げる時の処理
const js_Build_p = function (done) {
  browserify({
    entries: [paths.srcDir + "/js/main.js"],
    debug: true,
  })
    .transform(babelify, { presets: ["@babel/preset-env"] })
    .bundle()
    .on("error", function (e) {
      console.log(e);
    })
    .pipe(source("bundle.js")) // 引数に出力後のファイル名を記述
    .pipe(
      plumber(
        //エラーが出ても処理を止めない
        {
          errorHandler: notify.onError("Error: <%= error.message %>"),
        }
      )
    )
    .pipe(streamify(stripDebug())) // ビルド時にconsole.log()の記述を削除する
    .pipe(streamify(uglify())) // streamifyを使用していないと、GulpUglifyError: Streaming not supported とエラーが出る
    .pipe(sourcemaps.write("./"))
    .pipe(
      rename({
        extname: ".min.js", // 圧縮後は min が追記されたファイル名になる
      })
    )
    .pipe(gulp.dest(paths.dstDir + "/js/"));
  done();
};

// scssファイルをコンパイル
const sass_Build = function (done) {
  gulp
    .src(paths.srcDir + "/scss/**/*.scss")
    .pipe(sassGlob())
    .pipe(sass({ outputStyle: "compressed" }).on("error", sass.logError))
    // {outputStyle: 'compressed'} はgulp-sassのオプションで出力ファイルを圧縮している
    // https://www.npmjs.com/package/gulp-sass
    .pipe(postcss([autoprefixer()]))
    .pipe(
      plumber(
        //エラーが出ても処理を止めない
        {
          errorHandler: notify.onError("Error:<%= error.message %>"),
          //エラー出力設定
        }
      )
    )
    .pipe(
      rename({
        extname: ".min.css", //.min.cssの拡張子にする
      })
    )
    .pipe(gulp.dest(paths.dstDir + "/css/"));
  done();
};

// 画像圧縮
// 圧縮前と圧縮後のディレクトリを定義
// jpg, png, gif画像の圧縮タスク
// gulp-imageminのバージョンアップによるでるエラー：imagemin.jpegtran is not a function
// imagemin.jpegtran()をimagemin.mozjpeg()に変更
const img_Build = function (done) {
  var srcGlob = paths.srcDir + "/img/*.+(jpg|jpeg|png|gif|svg)"; // /**/ で、その配下の全部のディレクトリを見に行く
  var dstGlob = paths.dstDir + "/img";
  gulp
    .src(srcGlob)
    // gulp-changedというライブラリは、読込み元と保存先のディレクトリの差分を確認して、画像圧縮を実行するか判断するもの
    .pipe(changed(dstGlob))
    .pipe(
      imagemin([
        imagemin.gifsicle({ interlaced: true }),
        // imagemin.jpegtran({progressive: true}), v6.x系の書き方
        // imagemin.mozjpeg({progressive: true}),
        imagemin.mozjpeg({ quality: 80 }),
        imagemin.optipng({ optimizationLevel: 5 }),
      ])
    )
    .pipe(gulp.dest(dstGlob));
  done();
};

const php_serve = function () {
  gulp_connect.server(
    {
      base: "./dist/",
      livereload: true,
      port: 8001,
      bin: "C:/xampp/php/php.exe",
      ini: "C:/xampp/php/php.ini",
    },
    function () {
      browserSync.init({
        proxy: "localhost:8001",
        open: "external",
      });
    }
  );

  // 監視するタスク
  gulp.watch(paths.srcDir + "/js/*.js", gulp.series(js_Build));
  gulp.watch(paths.srcDir + "/scss/**/*.scss", gulp.series(sass_Build));
  gulp.watch(paths.srcDir + "/img", gulp.series(img_Build));

  // ファイルが更新（ビルド）されたらリロードする
  gulp.watch(paths.dstDir + "/js/*.js").on("change", browserSync.reload);
  gulp.watch(paths.dstDir + "/css/*.css").on("change", browserSync.reload);

  gulp.watch("./dist/*.html").on("change", browserSync.reload);
  gulp.watch("./dist/*.php").on("change", browserSync.reload);
};

// gulp コマンドで下記のタスクが実行される
exports.default = php_serve;
// gulp buildコマンドで実行される（初回ビルド時）
exports.build = gulp.parallel(js_Build_p, sass_Build, img_Build);
