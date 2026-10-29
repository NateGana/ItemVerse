<?php
$title='Discover';
require __DIR__.'/includes/header.php';
$n=$pdo->query("SELECT (SELECT COUNT(*) FROM items) i,(SELECT COUNT(*) FROM reviews WHERE status='approved') r,(SELECT COUNT(*) FROM users) u")->fetch();
$cats=$pdo->query("SELECT c.*,COUNT(i.id) n FROM categories c LEFT JOIN items i ON i.category_id=c.id GROUP BY c.id ORDER BY c.name")->fetchAll();
$sets=['Featured'=>items($pdo,'WHERE i.featured=1',[],'i.id DESC',4),'Trending'=>items($pdo,'',[],'cnt DESC,ar DESC',4),'Highest rated'=>items($pdo,'',[],'ar DESC,cnt DESC',4),'Recently reviewed'=>items($pdo,'',[],'(SELECT MAX(created_at) FROM reviews x WHERE x.item_id=i.id) DESC',4)];
?>
<section class="hero">
<h1>Find out what is worth buying before you buy it.</h1>
<p>Real ratings on quality, durability, design, performance and value, written by people who own the item.</p>
<form class="search" action="<?=BASE?>/pages/items.php" method="get"><input name="q" placeholder="Search items, brands or categories" aria-label="Search"><button class="btn" type="submit">Search</button></form>
</section>
<section class="stats">
<div class="stat"><b><?=$n['i']?></b><span>Items</span></div>
<div class="stat"><b><?=$n['r']?></b><span>Reviews</span></div>
<div class="stat"><b><?=$n['u']?></b><span>Members</span></div>
</section>
<h2 class="sec">Categories</h2>
<div class="cats"><?php foreach($cats as $c):?><a class="chip" href="<?=BASE?>/pages/items.php?cat=<?=$c['id']?>"><?=e($c['name'])?> <small><?=$c['n']?></small></a><?php endforeach;?></div>
<?php foreach($sets as $h=>$list):?>
<h2 class="sec"><?=e($h)?></h2>
<?php if(!$list):?><div class="empty">Nothing here yet.</div><?php else:?>
<div class="grid"><?php foreach($list as $i)echo card($i);?></div>
<?php endif;endforeach;?>
<?php require __DIR__.'/includes/footer.php';?>
