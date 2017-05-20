<?php

use think\Db;

function downloadExcel($strTable,$filename)
{
	header("Content-type: application/vnd.ms-excel");
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=".$filename."_".date('Y-m-d').".xls");
	header('Expires:0');
	header('Pragma:public');
	echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$strTable.'</html>';
}


//根据管理员的id找到管理员的名字
function get_admin_name_by_id ($uid)
{
	return Db::name('admin')->where('admin_id='.$uid)->find()['username'];	
}