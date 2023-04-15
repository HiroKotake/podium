<ul class="ddmenu">
        <li><a href="#">HOME</a></li>
        <li><a href="#">開発</a>
            <ul>
                <li><a href="/<?php echo PWF_ADMIN_DIR; ?>/Make/plan">クラス作成</a></li>
            </ul>
        </li>
        <li><a href="#">DB</a>
            <ul>
                <li><a href="/<?php echo PWF_ADMIN_DIR; ?>/TableInfo/list">DB構成</a></li>
                <li><a href="/<?php echo PWF_ADMIN_DIR; ?>/Migrate/show">テーブル作成</a></li>
                <li><a href="/<?php echo PWF_ADMIN_DIR; ?>/Import/show">データ挿入</a></li>
            </ul>
        </li>
        <li><a href="#">ユーザ管理</a>
            <ul>
<?php if ($Logined): ?>
                <li><a href="/<?php echo PWF_ADMIN_DIR; ?>/Top/logout">ログアウト</a></li>
<?php else: ?>
                <li><a href="/<?php echo PWF_ADMIN_DIR; ?>/Top">ログイン</a></li>
<?php endif; ?>
                <li><a href="/<?php echo PWF_ADMIN_DIR; ?>/Users/edit?hd=<?php echo $UserInfo->Id;?>">個人情報</a></li>
<?php if (isset($UserInfo) && $UserInfo->Level == PWF_AUTH_LEVEL_MASTER): ?>
                <li><a href="/<?php echo PWF_ADMIN_DIR; ?>/Users/list">管理者リスト</a></li>
<?php endif; ?>
                <li><a href="/<?php echo PWF_ADMIN_DIR; ?>/Users/passwd">パスワード変更</a></li>
            </ul>
        </li>
    </ul>
