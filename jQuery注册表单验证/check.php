<?php
$user = isset($_POST['user'])?$_POST['user']:'';
$link = mysqli_connect('localhost','root','','php26');
mysqli_set_charset($link,'utf8');
$sql = "select * from stu where name='{$user}' ";
$res = mysqli_query($link,$sql);
$row = mysqli_fetch_assoc($res);
if($row){
	echo 1;  // 代表重复
}else{
	echo 2;  //代表不重复
}