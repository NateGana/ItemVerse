<?php
require __DIR__.'/../config/db.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
check();
$s=$pdo->prepare('SELECT * FROM users WHERE email=?');$s->execute([strtolower(trim($_POST['email']??''))]);$u=$s->fetch();
if($u&&$u['status']==='active'&&password_verify($_POST['password']??'',$u['password_hash'])){
session_regenerate_id(true);
$_SESSION['user']=['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role']];
flash('Welcome back, '.$u['name'].'.');
go($u['role']==='admin'?BASE.'/admin/index.php':BASE.'/pages/dashboard.php');
}
flash('Email or password is incorrect.','err');
}
$title='Log in';require __DIR__.'/../includes/header.php';
?>
<form class="panel auth" method="post" data-validate="login" novalidate>
<h1>Log in</h1>
<input type="hidden" name="t" value="<?=csrf()?>">
<label>Email</label><input name="email" type="email" required>
<label>Password</label><input name="password" type="password" required>
<p><button class="btn" type="submit">Log in</button></p>
<small>New here? <a href="register.php"><u>Create an account</u></a></small>
</form>
<?php require __DIR__.'/../includes/footer.php';?>
