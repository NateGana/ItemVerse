<?php
$title='Compare';
require __DIR__.'/../includes/header.php';
$ids=array_slice(array_values(array_filter(array_unique(array_map('intval',(array)($_GET['ids']??[]))))),0,3);
$all=$pdo->query('SELECT id,name FROM items ORDER BY name')->fetchAll();
$list=[];
foreach($ids as $i){$r=items($pdo,'WHERE i.id=?',[$i],'i.id',1);if($r)$list[]=$r[0];}
$rows=['Brand'=>'brand','Price'=>'price','Overall rating'=>'ar','Quality'=>'q','Durability'=>'d','Design'=>'g','Performance'=>'p','Value for money'=>'v','Reviews'=>'cnt'];
$ranked=['ar','q','d','g','p','v','cnt'];
?>
<h1>Compare items</h1>
<form class="filters" method="get" style="grid-template-columns:repeat(3,1fr) auto">
<?php for($n=0;$n<3;$n++):?><select name="ids[]" aria-label="Item <?=$n+1?>"><option value="">Choose item</option><?php foreach($all as $a):?><option value="<?=$a['id']?>" <?=(($ids[$n]??0)==$a['id'])?'selected':''?>><?=e($a['name'])?></option><?php endforeach;?></select><?php endfor;?>
<button class="btn" type="submit">Compare</button>
</form>
<?php if(count($list)<2):?><div class="empty">Pick at least two items to see them side by side.</div><?php else:?>
<div class="panel scroll"><table>
<tr><th></th><?php foreach($list as $i):?><th><a href="item.php?id=<?=$i['id']?>"><?=e($i['name'])?></a></th><?php endforeach;?></tr>
<?php foreach($rows as $l=>$k):
$max=in_array($k,$ranked)?max(array_column($list,$k)):null;?>
<tr><td><?=$l?></td><?php foreach($list as $i):
$v=$i[$k];
if($k==='price')$v=$v===null?'N/A':'$'.number_format($v,2);
elseif($k!=='brand'&&$k!=='cnt')$v=number_format($v,1);
$best=$max!==null&&$max>0&&$i[$k]==$max;?>
<td class="<?=$best?'best':''?>"><?=e($v)?><?=$best?' ★':''?></td><?php endforeach;?></tr>
<?php endforeach;?></table></div>
<?php endif;?>
<?php require __DIR__.'/../includes/footer.php';?>
