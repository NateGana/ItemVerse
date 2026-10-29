<?php
require __DIR__.'/../config/db.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
check();
$n=trim($_POST['name']??'');$m=strtolower(trim($_POST['email']??''));$p=$_POST['password']??'';
if(mb_strlen($n)<2||!filter_var($m,FILTER_VALIDATE_EMAIL)||strlen($p)<8)flash('Enter a name, a valid email and a password of at least 8 characters.','err');
else{
$s=$pdo->prepare('SELECT id FROM users WHERE email=?');$s->execute([$m]);
if($s->fetch())flash('That email is already registered.','err');
else{$pdo->prepare('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)')->execute([$n,$m,password_hash($p,PASSWORD_DEFAULT)]);flash('Account created. You can log in now.');go('login.php');}
}
}
$title='Sign up';require __DIR__.'/../includes/header.php';
?>
<form class="panel auth" method="post" data-validate="register" novalidate>
<h1>Create your account</h1>
<input type="hidden" name="t" value="<?=csrf()?>">
<label>Name</label><input name="name" required>
<label>Email</label><input name="email" type="email" required>
<label>Password (8+ characters)</label><input name="password" type="password" required minlength="8">
<p><button class="btn" type="submit">Create account</button></p>
</form>
<?php require __DIR__.'/../includes/footer.php';?>
