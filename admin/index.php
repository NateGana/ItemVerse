<?php
require __DIR__.'/../config/db.php';
needAdmin();$aid=user()['id'];
function logit($pdo,$aid,$a){$pdo->prepare('INSERT INTO admin_activity_logs(admin_id,action) VALUES(?,?)')->execute([$aid,$a]);}
if($_SERVER['REQUEST_METHOD']==='POST'){
check();$a=$_POST['act']??'';
if($a==='item'){
$n=trim($_POST['name']??'');$b=trim($_POST['brand']??'');$c=(int)($_POST['category_id']??0);$d=trim($_POST['description']??'');$p=($_POST['price']??'')!==''?(float)$_POST['price']:null;$iid=(int)($_POST['id']??0);
$img=upload($_FILES['image']??[]);
if($n===''||$b===''||!$c||$img===false){flash('Name, brand, category and a valid image are required.','err');go('index.php');}
$ft=isset($_POST['featured'])?1:0;
if($iid){$pdo->prepare('UPDATE items SET name=?,brand=?,category_id=?,description=?,price=?,featured=? WHERE id=?')->execute([$n,$b,$c,$d,$p,$ft,$iid]);if($img)$pdo->prepare('UPDATE items SET image=? WHERE id=?')->execute([$img,$iid]);logit($pdo,$aid,"Edited item $n");}
else{$pdo->prepare('INSERT INTO items(name,brand,category_id,description,price,image,featured) VALUES(?,?,?,?,?,?,?)')->execute([$n,$b,$c,$d,$p,$img,$ft]);logit($pdo,$aid,"Added item $n");}
flash('Item saved.');
}
if($a==='delitem'){$pdo->prepare('DELETE FROM items WHERE id=?')->execute([(int)$_POST['id']]);logit($pdo,$aid,'Deleted item #'.(int)$_POST['id']);flash('Item deleted.');}
if($a==='addcat'&&trim($_POST['cname']??'')!==''){$pdo->prepare('INSERT IGNORE INTO categories(name) VALUES(?)')->execute([trim($_POST['cname'])]);logit($pdo,$aid,'Added category '.trim($_POST['cname']));flash('Category added.');}
if($a==='delcat'){try{$pdo->prepare('DELETE FROM categories WHERE id=?')->execute([(int)$_POST['id']]);flash('Category deleted.');}catch(PDOException $x){flash('Move or delete the items in this category first.','err');}}
if($a==='approve'||$a==='hide'||$a==='delrev'){
$rid=(int)$_POST['id'];
if($a==='delrev')$pdo->prepare('DELETE FROM reviews WHERE id=?')->execute([$rid]);
else $pdo->prepare('UPDATE reviews SET status=? WHERE id=?')->execute([$a==='approve'?'approved':'rejected',$rid]);
logit($pdo,$aid,"Moderated review #$rid ($a)");flash('Review updated.');
}
if($a==='user'){$uid=(int)$_POST['id'];if($uid!==$aid){$pdo->prepare("UPDATE users SET status=IF(status='active','banned','active') WHERE id=?")->execute([$uid]);logit($pdo,$aid,"Toggled user #$uid");flash('User updated.');}}
go('index.php');
}
$title='Admin';require __DIR__.'/../includes/header.php';
$n=$pdo->query("SELECT (SELECT COUNT(*) FROM users) u,(SELECT COUNT(*) FROM items) i,(SELECT COUNT(*) FROM reviews) r,(SELECT COUNT(*) FROM reviews WHERE status='pending') p")->fetch();
$cats=$pdo->query('SELECT c.*,COUNT(i.id) n FROM categories c LEFT JOIN items i ON i.category_id=c.id GROUP BY c.id ORDER BY c.name')->fetchAll();
$mod=$pdo->query("SELECT r.*,u.name uname,i.name iname FROM reviews r JOIN users u ON u.id=r.user_id JOIN items i ON i.id=r.item_id WHERE r.status<>'approved' ORDER BY r.created_at DESC")->fetchAll();
$users=$pdo->query('SELECT * FROM users ORDER BY id DESC')->fetchAll();
$its=$pdo->query('SELECT i.*,c.name cat FROM items i JOIN categories c ON c.id=i.category_id ORDER BY i.id DESC')->fetchAll();
$logs=$pdo->query('SELECT l.*,u.name FROM admin_activity_logs l JOIN users u ON u.id=l.admin_id ORDER BY l.id DESC LIMIT 8')->fetchAll();
$byCat=$pdo->query("SELECT c.name,COUNT(r.id) n FROM categories c LEFT JOIN items i ON i.category_id=c.id LEFT JOIN reviews r ON r.item_id=i.id GROUP BY c.id")->fetchAll();
$byDay=$pdo->query("SELECT DATE(created_at) d,COUNT(*) n FROM reviews WHERE created_at>=DATE_SUB(CURDATE(),INTERVAL 13 DAY) GROUP BY d ORDER BY d")->fetchAll();
$edit=null;if(isset($_GET['edit'])){foreach($its as $x)if($x['id']==(int)$_GET['edit'])$edit=$x;}
$T='<input type="hidden" name="t" value="'.csrf().'">';
?>
<h1>Administration</h1>
<section class="stats">
<div class="stat"><b><?=$n['u']?></b><span>Users</span></div>
<div class="stat"><b><?=$n['i']?></b><span>Items</span></div>
<div class="stat"><b><?=$n['r']?></b><span>Reviews</span></div>
<div class="stat"><b><?=$n['p']?></b><span>Pending reviews</span></div>
</section>
<div class="two">
<div class="panel"><h3>Reviews by category</h3><canvas id="c1"></canvas></div>
<div class="panel"><h3>Reviews, last 14 days</h3><canvas id="c2"></canvas></div>
</div>
<div class="panel"><h3>Moderation queue</h3>
<?php if(!$mod):?><div class="empty">No reviews are waiting for moderation.</div><?php endif;?>
<?php foreach($mod as $r):?><div class="review"><b><?=e($r['title'])?></b> <span class="tag"><?=e($r['status'])?></span><br><small><?=e($r['uname'])?> on <?=e($r['iname'])?></small><p><?=e($r['body'])?></p>
<form method="post" class="inline"><?=$T?><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn" name="act" value="approve">Approve</button> <button class="btn ghost" name="act" value="hide">Hide</button> <button class="btn danger" name="act" value="delrev" onclick="return confirm('Delete this review?')">Delete</button></form></div><?php endforeach;?></div>
<div class="panel"><h3><?=$edit?'Edit item':'Add item'?></h3>
<form method="post" enctype="multipart/form-data" data-validate="item" novalidate><?=$T?><input type="hidden" name="act" value="item"><input type="hidden" name="id" value="<?=$edit['id']??0?>">
<div class="two"><div><label>Name</label><input name="name" required value="<?=e($edit['name']??'')?>"><label>Brand</label><input name="brand" required value="<?=e($edit['brand']??'')?>"><label>Price (optional)</label><input name="price" type="number" step="0.01" min="0" value="<?=e($edit['price']??'')?>"></div>
<div><label>Category</label><select name="category_id" required><?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=($edit['category_id']??0)==$c['id']?'selected':''?>><?=e($c['name'])?></option><?php endforeach;?></select><label>Image</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp"><label><input type="checkbox" name="featured" <?=!empty($edit['featured'])?'checked':''?>> Featured on homepage</label></div></div>
<label>Description</label><textarea name="description" rows="3"><?=e($edit['description']??'')?></textarea>
<p><button class="btn" type="submit">Save item</button> <?php if($edit):?><a class="btn ghost" href="index.php">Cancel</a><?php endif;?></p></form></div>
<div class="panel scroll"><h3>Items</h3><table><tr><th>Name</th><th>Brand</th><th>Category</th><th></th></tr>
<?php foreach($its as $x):?><tr><td><?=e($x['name'])?></td><td><?=e($x['brand'])?></td><td><?=e($x['cat'])?></td><td><a class="btn ghost" href="?edit=<?=$x['id']?>">Edit</a> <form method="post" class="inline"><?=$T?><input type="hidden" name="id" value="<?=$x['id']?>"><button class="btn danger" name="act" value="delitem" onclick="return confirm('Delete this item and its reviews?')">Delete</button></form></td></tr><?php endforeach;?></table></div>
<div class="two">
<div class="panel"><h3>Categories</h3>
<?php foreach($cats as $c):?><form method="post" class="bar"><?=$T?><input type="hidden" name="id" value="<?=$c['id']?>"><span><?=e($c['name'])?> (<?=$c['n']?>)</span><i></i><button class="btn danger" name="act" value="delcat" onclick="return confirm('Delete category?')">Delete</button></form><?php endforeach;?>
<form method="post" class="search"><?=$T?><input name="cname" placeholder="New category" required><button class="btn" name="act" value="addcat">Add</button></form></div>
<div class="panel"><h3>Recent admin activity</h3><?php if(!$logs):?><div class="empty">No activity recorded yet.</div><?php endif;?><?php foreach($logs as $l):?><p><?=e($l['action'])?><br><small><?=e($l['name'])?> · <?=e($l['created_at'])?></small></p><?php endforeach;?></div>
</div>
<div class="panel scroll"><h3>Users</h3><table><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr>
<?php foreach($users as $x):?><tr><td><?=e($x['name'])?></td><td><?=e($x['email'])?></td><td><?=e($x['role'])?></td><td><?=e($x['status'])?></td><td><?php if($x['id']!=$aid):?><form method="post" class="inline"><?=$T?><input type="hidden" name="id" value="<?=$x['id']?>"><button class="btn ghost" name="act" value="user"><?=$x['status']==='active'?'Ban':'Reactivate'?></button></form><?php endif;?></td></tr><?php endforeach;?></table></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
var cat=<?=json_encode($byCat)?>,day=<?=json_encode($byDay)?>;
Chart.defaults.color='#8b95b3';
new Chart(document.getElementById('c1'),{type:'bar',data:{labels:cat.map(function(x){return x.name}),datasets:[{label:'Reviews',data:cat.map(function(x){return +x.n}),backgroundColor:'#3b82f6'}]},options:{plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});
new Chart(document.getElementById('c2'),{type:'line',data:{labels:day.map(function(x){return x.d}),datasets:[{label:'Reviews',data:day.map(function(x){return +x.n}),borderColor:'#8b5cf6',tension:.3}]},options:{plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});
</script>
<?php require __DIR__.'/../includes/footer.php';?>
