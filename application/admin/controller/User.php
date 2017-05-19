<?php
//  Project name Q-Buy
//  Created by window on 17/5/17.
//  Copyright © 2017年 worning. All rights reserved.

/**
 *　　　　　　　 ┏┓       ┏┓+ +
 *　　　　　　　┏┛┻━━━━━━━┛┻┓ + +
 *　　　　　　　┃　　　　　　 ┃
 *　　　　　　　┃　　　━　　　┃ ++ + + +
 *　　　　　　 █████━█████  ┃+
 *　　　　　　　┃　　　　　　 ┃ +
 *　　　　　　　┃　　　┻　　　┃
 *　　　　　　　┃　　　　　　 ┃ + +
 *　　　　　　　┗━━┓　　　 ┏━┛
 *               ┃　　  ┃
 *　　　　　　　　　┃　　  ┃ + + + +
 *　　　　　　　　　┃　　　┃　Code is far away from     bug with the animal protecting
 *　　　　　　　　　┃　　　┃ + 　　　　         神兽保佑 , 代码无bug
 *　　　　　　　　　┃　　　┃
 *　　　　　　　　　┃　　　┃　　+
 *　　　　　　　　　┃　 　 ┗━━━┓ + +
 *　　　　　　　　　┃ 　　　　　┣┓
 *　　　　　　　　　┃ 　　　　　┏┛
 *　　　　　　　　　┗┓┓┏━━━┳┓┏┛ + + + +
 *　　　　　　　　　 ┃┫┫　 ┃┫┫
 *　　　　　　　　　 ┗┻┛　 ┗┻┛+ + + +
 */

namespace app\admin\controller;
use think\Request;
use think\Db;
use app\admin\model\User as UserModel;
class User extends Base
{

	//遍历品牌的信息
	public function userList ()
	{
		if (!empty(input('get.search'))) {
			$search = input('get.search');
			$userinfo = UserModel::where('username like "%'.$search.'%"')->paginate(10);
			$count = UserModel::count();
			$this->assign(['user' => $userinfo,'count' => $count]);
			return $this->fetch();
		} else {
			$show = 10;
			if (!empty(input('get.pages'))) {
				$show = input('get.pages');
			}
			$user = new UserModel();
			$userinfo = $user->paginate($show);
			$count = $user->count();
			$this->assign(['user' => $userinfo,'count' => $count]);
			return $this->fetch();
		}
	}
	//渲染修改用户的模板
	public function edituser ()
	{
		$uid = input('get.id');
		$result = UserModel::get($uid);
		$this->assign(['res' => $result,'uid' => $uid]);
		return $this->fetch();
	}
	//处理修改用户
	public function dealEditUser ()
	{
		$data = input('post.');
		$result = Db::name('user')->update($data);
		if ($result == 1 || $result == 0) {
			return 1;
		} else {
			return 0;
		}
	}


	public function deleteUser () {
		$uid = input('get.id');
		$result = UserModel::destroy($uid);
		return $result;
	}


	public function memberGrading ()
	{
		$count = UserModel::group('level')->field('count(*) as count ,level')->select();
		$allcount = UserModel::count();
		$this->assign(['count' => $count,'allcount' => $allcount]);
		return $this->fetch();
	}


	public function memberShow() 
	{
		$uid = input('get.id');
		$result = UserModel::get($uid);
		$this->assign(['res' => $result,'uid' => $uid]);
		return $this->fetch();
	}
	

}
 
