# podium
WEB Framework for PHP

## はじめに
Podium Web Framework (以下PWF)は、PHPによるWEB構築用支援ライブラリである。
開発をしようとした要因は、昨今のPHPのWEBフレームワークは重厚長大になりつつあり、業務支援用として開発するには十分過ぎる機能を持っているが、その分オーバーヘッドが大きくなっているように思え、 より軽量でシンプルなフレームワークを開発し、ゲーム等のフロントエンドアプリに対するバックヤードとしてのPHP向けフレームワークを提供しようと考えたからである。

## セットアップ
1. 準備
PWFを動作させるには、以下のソフトウェアが必要である。
```
PHP Version 7.4以降
WEBサーバ(Apache,nginx)
DB: MySQL,PostgreSQL,SQLite等必要に応じて
KVSを使用する場合はredisを想定しているので、redisを設定し、PHPのライブラリとしてpecl-redis5を準備
```
その後、適当なフォルダにPWFを配置する。

2. WEBサーバの設定
(Apacheを基準とする)
・DirectoryRootとしてPWF内のpublicフォルダを指定
・.htaccessを使用するように指定し、.htaccessに以下を設定
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
```

## PWFマニュアル
PWFの詳細については、[マニュアルページ](http://www.teleios.jp/podium/manual.html)を参照願います。