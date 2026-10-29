<?php
require __DIR__.'/../config/db.php';
$id=(int)($_GET['id']??0);
$s=$pdo->prepare(ITEMSQL.' WHERE i.id=? GROUP BY i.id');$s->execute([$id]);$it=$s->fetch();
if(!$it){http_response_code(404);exit('Item not found.');}
$u=user();$self=BASE.'/pages/item.php?id='.$id;
if($_SERVER['REQUEST_METHOD']==='POST'){
need();check();$act=$_POST['act']??'';$uid=$u['id'];
if($act==='review'){
$f=['rating','quality','durability','design','performance','value_money'];$v=[];$ok=true;
foreach($f as $k){$x=(int)($_POST[$k]??0);if($x<1||$x>5)$ok=false;$v[$k]=$x;}
$t=trim($_POST['title']??'');$b=trim($_POST['body']??'');
if(!$ok||mb_strlen($t)<3||mb_strlen($b)<20){flash('Give every rating, a title and at least 20 characters of review text.','err');go($self);}
$img=upload($_FILES['image']??[]);
if($img===false){flash('Images must be JPG, PNG or WebP and under 2 MB.','err');go($self);}
$pdo->prepare("INSERT INTO reviews(item_id,user_id,rating,quality,durability,design,performance,value_money,title,body,pros,cons) VALUES(?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating),quality=VALUES(quality),durability=VALUES(durability),design=VALUES(design),performance=VALUES(performance),value_money=VALUES(value_money),title=VALUES(title),body=VALUES(body),pros=VALUES(pros),cons=VALUES(cons),status='approved'")
->execute([$id,$uid,$v['rating'],$v['quality'],$v['durability'],$v['design'],$v['performance'],$v['value_money'],$t,$b,trim($_POST['pros']??''),trim($_POST['cons']??'')]);
if($img){$r=$pdo->prepare('SELECT id FROM reviews WHERE item_id=? AND user_id=?');$r->execute([$id,$uid]);$pdo->prepare('INSERT INTO review_images(review_id,filename) VALUES(?,?)')->execute([$r->fetchColumn(),$img]);}
flash('Your review is saved.');
}
if($act==='del'){$pdo->prepare('DELETE FROM reviews WHERE id=? AND user_id=?')->execute([(int)$_POST['rid'],$uid]);flash('Review deleted.');}
if($act==='vote'){$pdo->prepare('INSERT IGNORE INTO review_votes(review_id,user_id) VALUES(?,?)')->execute([(int)$_POST['rid'],$uid]);flash('Thanks for the vote.');}
if($act==='report'){$pdo->prepare("UPDATE reviews SET status='pending' WHERE id=? AND user_id<>?")->execute([(int)$_POST['rid'],$uid]);flash('Review reported to moderators.');}
if($act==='wish'){$d=$pdo->prepare('DELETE FROM wishlist WHERE user_id=? AND item_id=?');$d->execute([$uid,$id]);if(!$d->rowCount()){$pdo->prepare('INSERT INTO wishlist(user_id,item_id) VALUES(?,?)')->execute([$uid,$id]);flash('Saved to your list.');}else flash('Removed from your list.');}
go($self);
}
$title=$it['name'];require __DIR__.'/../includes/header.php';
$revs=$pdo->prepare("SELECT r.*,u.name uname,(SELECT COUNT(*) FROM review_votes v WHERE v.review_id=r.id) helpful FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.item_id=? AND r.status='approved' ORDER BY r.created_at DESC");$revs->execute([$id]);$revs=$revs->fetchAll();
$dist=array_fill(1,5,0);foreach($revs as $r)$dist[$r['rating']]++;
$mine=null;$saved=false;
if($u){$m=$pdo->prepare('SELECT * FROM reviews WHERE item_id=? AND user_id=?');$m->execute([$id,$u['id']]);$mine=$m->fetch();$w=$pdo->prepare('SELECT 1 FROM wishlist WHERE user_id=? AND item_id=?');$w->execute([$u['id'],$id]);$saved=(bool)$w->fetch();}
$rel=items($pdo,'WHERE i.category_id=? AND i.id<>?',[$it['category_id'],$id],'ar DESC',3);
$parts=['Quality'=>'q','Durability'=>'d','Design'=>'g','Performance'=>'p','Value for money'=>'v'];
?>
<div class="two">
<div class="big"><?=$it['image']?'<img src="'.BASE.'/uploads/'.e($it['image']).'" alt="">':'<div class="ph">'.e(mb_substr($it['name'],0,1)).'</div>'?></div>
<div>
<small><?=e($it['brand'])?> · <?=e($it['cat'])?></small>
<h1><?=e($it['name'])?></h1>
<div class="rate"><span class="stars"><?=stars($it['ar'])?></span> <b><?=number_format($it['ar'],1)?></b> <small><?=$it['cnt']?> reviews</small></div>
<?php if($it['price']!==null):?><p><b>$<?=number_format($it['price'],2)?></b></p><?php endif;?>
<p class="mute"><?=nl2br(e($it['description']))?></p>
<?php if($u):?><form method="post" class="inline"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="act" value="wish"><button class="btn ghost" type="submit"><?=$saved?'Remove from saved':'Save item'?></button></form><?php endif;?>
<a class="btn ghost" href="compare.php?ids[]=<?=$id?>">Compare</a>
</div></div>
<div class="two" style="margin-top:28px">
<div class="panel"><h3>Rating distribution</h3>
<?php for($k=5;$k>=1;$k--):$pc=count($revs)?$dist[$k]/count($revs)*100:0;?><div class="bar"><span><?=$k?> stars</span><i><b style="width:<?=$pc?>%"></b></i><span><?=$dist[$k]?></span></div><?php endfor;?></div>
<div class="panel"><h3>Rating breakdown</h3>
<?php foreach($parts as $l=>$k):?><div class="bar"><span><?=$l?></span><i><b style="width:<?=$it[$k]*20?>%"></b></i><span><?=number_format($it[$k],1)?></span></div><?php endforeach;?></div>
</div>
<div class="panel">
<h2><?=$mine?'Edit your review':'Write a review'?></h2>
<?php if(!$u):?><p>Please <a href="login.php"><u>log in</u></a> to review this item.</p><?php else:?>
<form method="post" enctype="multipart/form-data" data-validate="review" novalidate>
<input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="act" value="review">
<div class="rgrid">
<?php foreach(['rating'=>'Overall','quality'=>'Quality','durability'=>'Durability','design'=>'Design','performance'=>'Performance','value_money'=>'Value for money'] as $k=>$l):?>
<div><label><?=$l?></label><select name="<?=$k?>" required><option value="">Select</option><?php for($x=5;$x>=1;$x--):?><option value="<?=$x?>" <?=($mine[$k]??0)==$x?'selected':''?>><?=$x?> – <?=['','Poor','Fair','Good','Very good','Excellent'][$x]?></option><?php endfor;?></select></div>
<?php endforeach;?></div>
<label>Title</label><input name="title" required value="<?=e($mine['title']??'')?>">
<label>Your experience</label><textarea name="body" rows="5" required><?=e($mine['body']??'')?></textarea>
<div class="two"><div><label>Pros</label><textarea name="pros" rows="2"><?=e($mine['pros']??'')?></textarea></div><div><label>Cons</label><textarea name="cons" rows="2"><?=e($mine['cons']??'')?></textarea></div></div>
<label>Photo (JPG, PNG or WebP, up to 2 MB)</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp">
<p><button class="btn" type="submit"><?=$mine?'Save changes':'Publish review'?></button></p>
</form>
<?php endif;?></div>
<h2>Reviews</h2>
<?php if(!$revs):?><div class="empty">No reviews yet. Be the first to share your experience.</div><?php endif;?>
<?php foreach($revs as $r):
$im=$pdo->prepare('SELECT filename FROM review_images WHERE review_id=?');$im->execute([$r['id']]);?>
<div class="review">
<div><span class="stars"><?=stars($r['rating'])?></span> <b><?=e($r['title'])?></b></div>
<small>by <?=e($r['uname'])?> · <?=date('M j, Y',strtotime($r['created_at']))?></small>
<p><?=nl2br(e($r['body']))?></p>
<?php if($r['pros']):?><div class="pro">+ <?=e($r['pros'])?></div><?php endif;?>
<?php if($r['cons']):?><div class="con">− <?=e($r['cons'])?></div><?php endif;?>
<?php foreach($im as $x):?><img class="ri" src="<?=BASE?>/uploads/<?=e($x['filename'])?>" alt="Review photo"><?php endforeach;?>
<?php if($u):?>
<form method="post" class="inline"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="rid" value="<?=$r['id']?>">
<button class="btn ghost" name="act" value="vote">Helpful (<?=$r['helpful']?>)</button>
<?php if($r['user_id']==$u['id']):?><button class="btn danger" name="act" value="del" onclick="return confirm('Delete your review?')">Delete</button><?php else:?><button class="btn ghost" name="act" value="report">Report</button><?php endif;?></form>
<?php else:?><small class="tag"><?=$r['helpful']?> found this helpful</small><?php endif;?>
</div>
<?php endforeach;?>
<?php if($rel):?><h2 class="sec">Related items</h2><div class="grid"><?php foreach($rel as $i)echo card($i);?></div><?php endif;?>
<?php require __DIR__.'/../includes/footer.php';?>
