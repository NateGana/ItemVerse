<?php
session_start();
const BASE='/itemverse';
$pdo=new PDO('mysql:host=localhost;dbname=itemverse;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
const ITEMSQL="SELECT i.*,c.name cat,COALESCE(AVG(r.rating),0) ar,COUNT(r.id) cnt,COALESCE(AVG(r.quality),0) q,COALESCE(AVG(r.durability),0) d,COALESCE(AVG(r.design),0) g,COALESCE(AVG(r.performance),0) p,COALESCE(AVG(r.value_money),0) v FROM items i JOIN categories c ON c.id=i.category_id LEFT JOIN reviews r ON r.item_id=i.id AND r.status='approved'";
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function user(){return $_SESSION['user']??null;}
function need(){if(!user()){header('Location: '.BASE.'/pages/login.php');exit;}}
function needAdmin(){if(!user()||user()['role']!=='admin'){http_response_code(403);exit('Administrators only.');}}
function csrf(){if(empty($_SESSION['t']))$_SESSION['t']=bin2hex(random_bytes(16));return $_SESSION['t'];}
function check(){if(!hash_equals($_SESSION['t']??'',$_POST['t']??'')){http_response_code(400);exit('Invalid request token.');}}
function flash($m=null,$k='ok'){if($m!==null){$_SESSION['f']=[$k,$m];return;}$x=$_SESSION['f']??null;unset($_SESSION['f']);return $x;}
function go($u){header('Location: '.$u);exit;}
function items($pdo,$where='',$args=[],$order='i.id DESC',$limit=4){$s=$pdo->prepare(ITEMSQL." $where GROUP BY i.id ORDER BY $order LIMIT ".(int)$limit);$s->execute($args);return $s->fetchAll();}
function stars($n){$f=(int)round($n);return str_repeat('★',$f).str_repeat('☆',5-$f);}
function upload($f,$max=2097152){if(!isset($f['tmp_name'])||$f['error']!==UPLOAD_ERR_OK)return null;if($f['size']>$max)return false;$i=@getimagesize($f['tmp_name']);$ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$i['mime']??'']??null;if(!$ext)return false;$n=bin2hex(random_bytes(8)).'.'.$ext;move_uploaded_file($f['tmp_name'],__DIR__.'/../uploads/'.$n);return $n;}
function card($i){$img=$i['image']?'<img src="'.BASE.'/uploads/'.e($i['image']).'" alt="">':'<div class="ph">'.e(mb_substr($i['name'],0,1)).'</div>';return '<a class="card" href="'.BASE.'/pages/item.php?id='.$i['id'].'"><div class="thumb">'.$img.'</div><div class="body"><small>'.e($i['brand']).' · '.e($i['cat']).'</small><h3>'.e($i['name']).'</h3><div class="rate"><span class="stars">'.stars($i['ar']).'</span> '.number_format($i['ar'],1).' <small>('.$i['cnt'].')</small></div><p>'.e(mb_strimwidth($i['description'],0,90,'…')).'</p></div></a>';}
