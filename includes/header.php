<?php require_once __DIR__.'/../config/db.php';$u=user();$f=flash();?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($title??'ITEMVERSE')?> · ITEMVERSE</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?=BASE?>/assets/css/style.css">
</head>
<body>
<header class="nav">
<a class="logo" href="<?=BASE?>/index.php">ITEMVERSE</a>
<button class="burger" aria-label="Menu" onclick="document.querySelector('.links').classList.toggle('open')">☰</button>
<nav class="links">
<a href="<?=BASE?>/pages/items.php">Discover</a>
<a href="<?=BASE?>/pages/compare.php">Compare</a>
<?php if($u):?>
<a href="<?=BASE?>/pages/dashboard.php">Dashboard</a>
<?php if($u['role']==='admin'):?><a href="<?=BASE?>/admin/index.php">Admin</a><?php endif;?>
<a href="<?=BASE?>/pages/logout.php">Log out (<?=e($u['name'])?>)</a>
<?php else:?>
<a href="<?=BASE?>/pages/login.php">Log in</a>
<a class="btn" href="<?=BASE?>/pages/register.php">Sign up</a>
<?php endif;?>
</nav>
</header>
<?php if($f):?><div class="flash <?=e($f[0])?>"><?=e($f[1])?></div><?php endif;?>
<main>
