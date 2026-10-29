<?php
require __DIR__.'/../config/db.php';
need();$uid=user()['id'];
if($_SERVER['REQUEST_METHOD']==='POST'){
check();$n=trim($_POST['name']??'');$bio=trim($_POST['bio']??'');$p=$_POST['password']??'';
if(mb_strlen($n)<2)flash('Name must be at least 2 characters.','err');
elseif($p!==''&&strlen($p)<8)flash('New password must be at least 8 characters.','err');
else{
$pdo->prepare('UPDATE users SET name=?,bio=? WHERE id=?')->execute([$n,$bio,$uid]);
if($p!=='')$pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($p,PASSWORD_DEFAULT),$uid]);
$_SESSION['user']['name']=$n;flash('Profile updated.');
}
go('dashboard.php');
}
$title='Dashboard';require __DIR__.'/../includes/header.php';
$me=$pdo->prepare('SELECT * FROM users WHERE id=?');$me->execute([$uid]);$me=$me->fetch();
$st=$pdo->prepare('SELECT COUNT(*) c,COALESCE(AVG(rating),0) a FROM reviews WHERE user_id=?');$st->execute([$uid]);$st=$st->fetch();
$h=$pdo->prepare('SELECT r.*,i.name iname FROM reviews r JOIN items i ON i.id=r.item_id WHERE r.user_id=? ORDER BY r.created_at DESC');$h->execute([$uid]);$h=$h->fetchAll();
$sv=items($pdo,'WHERE i.id IN (SELECT item_id FROM wishlist WHERE user_id=?)',[$uid],'i.id DESC',12);
?>
<h1>Hello, <?=e($me['name'])?></h1>
<section class="stats">
<div class="stat"><b><?=$st['c']?></b><span>Reviews submitted</span></div>
<div class="stat"><b><?=number_format($st['a'],1)?></b><span>Average rating given</span></div>
<div class="stat"><b><?=count($sv)?></b><span>Saved items</span></div>
</section>
<div class="two">
<div class="panel"><h3>Review history</h3>
<?php if(!$h):?><div class="empty">You have not reviewed anything yet. <a href="items.php"><u>Find an item to review</u></a>.</div><?php endif;?>
<?php foreach($h as $r):?><p><a href="item.php?id=<?=$r['item_id']?>"><b><?=e($r['iname'])?></b></a> <span class="stars"><?=stars($r['rating'])?></span><br><small><?=e($r['title'])?> · <?=date('M j, Y',strtotime($r['created_at']))?><?=$r['status']!=='approved'?' · '.e($r['status']):''?></small></p><?php endforeach;?></div>
<form class="panel" method="post" data-validate="profile" novalidate>
<h3>Account settings</h3>
<input type="hidden" name="t" value="<?=csrf()?>">
<label>Name</label><input name="name" required value="<?=e($me['name'])?>">
<label>Email</label><input value="<?=e($me['email'])?>" disabled>
<label>About you</label><textarea name="bio" rows="3"><?=e($me['bio'])?></textarea>
<label>New password (leave blank to keep)</label><input name="password" type="password">
<p><button class="btn" type="submit">Save changes</button></p>
</form></div>
<h2 class="sec">Saved items</h2>
<?php if(!$sv):?><div class="empty">Nothing saved yet. Use Save item on any item page.</div><?php else:?><div class="grid"><?php foreach($sv as $i)echo card($i);?></div><?php endif;?>
<?php require __DIR__.'/../includes/footer.php';?>
