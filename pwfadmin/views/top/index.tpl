<html>
<head>
    <meta charset="UTF-8">
    <title>Podium Admin Top Page</title>
    <?php include(PWF_ADMIN_VIEW_PATH . 'common/css.tpl'); ?>
</head>
<body>
    <?php include(PWF_ADMIN_VIEW_PATH . 'common/menu.tpl'); ?>
    <?php if ($Logined): ?>
    User: <?php echo $UserInfo->Name; ?><br />
    <?php else: ?>
        <?php if (!empty($Message)): ?>
        <span>
    <b><font color="red"><?php echo $Message; ?></font></b><br />
        </span>
        <?php endif; ?>
    <div>
        <form action="/<?php echo PWF_ADMIN_DIR;?>/Top/auth" method="post">
            ログインID:<input type="text" name="uid" /><br />
            パスワード:<input type="password" name="pwd" /><br />
            <input type="hidden" name="token" value="<?php echo $Token; ?>" /><br />
            <input type="submit" value="ログイン" class="btnSubmit" />
        </form>
    </div>
    <?php endif; ?>
<?php include(PWF_ADMIN_VIEW_PATH . 'common/footer.tpl');?>
</body>
</html>