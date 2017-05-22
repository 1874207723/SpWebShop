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
	if (!empty($uid)) {
		return Db::name('admin')->where('admin_id='.$uid)->find()['username'];	
	} else {
		return 'null -> common';
	}
	
}

//根据用户的id找到用户的名字
function get_username_by_id ($uid)
{
	if (!empty($uid)) {
		return Db::name('user')->where('user_id='.$uid)->find()['username'];		
	} else {
		return 'null -> common';
	}
	
}

//根据商品的id找到商品的名字
function get_goods_name_by_id ($uid)
{
	if (!empty($uid)) {
		return Db::name('goods')->where('goods_id='.$uid)->find()['goods_name'];
	} else {
		return 'null -> common';
	}	
}

//根据角色的id找到对应角色的名称
function get_rolename_by_id ($uid)
{	
	if (!empty($uid)) {
		return Db::name('admin_role')->where('role_id='.$uid)->find()['role_name'];
	} else {
		return 'null -> common';
	}
}

//根据管理员的id找到管理员的名字
function get_return_status_by_id ($id)
{
	$arr = ['申请中','客服处理中','已完成'];
	return $arr[$id];	
}


function get_sql_size ($size)
{
	if ($size >= 1024*1024) {
		return round($size/1024/1024,2).'M';
	}
	return round($size/1024,2).'KB';
}