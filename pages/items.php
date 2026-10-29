<?php
$title='Discover items';
require __DIR__.'/../includes/header.php';
$q=trim($_GET['q']??'');$cat=(int)($_GET['cat']??0);$min=(int)($_GET['min']??0);$sort=$_GET['sort']??'new';
$w=[];$a=[];
if($q!==''){$w[]='(i.name LIKE ? OR i.brand LIKE ? OR i.description LIKE ?)';array_push($a,"%$q%","%$q%","%$q%");}
if($cat){$w[]='i.category_id=?';$a[]=$cat;}
$where=$w?'WHERE '.implode(' AND ',$w):'';
$having=$min?'HAVING ar>='.$min:'';
$order=['new'=>'i.id DESC','rated'=>'ar DESC,cnt DESC','reviews'=>'cnt DESC'][$sort]??'i.id DESC';
$s=$pdo->prepare(ITEMSQL." $where GROUP BY i.id $having ORDER BY $order");$s->execute($a);$list=$s->fetchAll();
$cats=$pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
?>
<h1>Discover items</h1>
<form class="filters" method="get">
<input name="q" value="<?=e($q)?>" placeholder="Search" aria-label="Search">
<select name="cat" aria-label="Category"><option value="0">All categories</option><?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=$cat==$c['id']?'selected':''?>><?=e($c['name'])?></option><?php endforeach;?></select>
<select name="min" aria-label="Minimum rating"><option value="0">Any rating</option><?php for($r=4;$r>=1;$r--):?><option value="<?=$r?>" <?=$min==$r?'selected':''?>><?=$r?>+ stars</option><?php endfor;?></select>
<select name="sort" aria-label="Sort"><option value="new" <?=$sort=='new'?'selected':''?>>Newest</option><option value="rated" <?=$sort=='rated'?'selected':''?>>Highest rated</option><option value="reviews" <?=$sort=='reviews'?'selected':''?>>Most reviewed</option></select>
<button class="btn" type="submit">Apply</button>
</form>
<form action="compare.php" method="get">
<?php if(!$list):?><div class="empty">No items match these filters. Try a broader search.</div><?php else:?>
<div class="grid"><?php foreach($list as $i):?><div class="cmpwrap"><label class="cmpbox"><input class="cmp" type="checkbox" name="ids[]" value="<?=$i['id']?>"> Compare</label><?=card($i)?></div><?php endforeach;?></div>
<p><button class="btn ghost" type="submit">Compare selected (up to 3)</button></p>
<?php endif;?>
</form>
<?php require __DIR__.'/../includes/footer.php';?>
