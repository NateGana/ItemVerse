<?php
require __DIR__.'/../config/db.php';
unset($_SESSION['user']);
session_regenerate_id(true);
flash('You have been logged out.');
go(BASE.'/index.php');
