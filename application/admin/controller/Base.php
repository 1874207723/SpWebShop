<?php
//  Project name Q-Buy
//  Created by window on 17/5/13.
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
use think\Controller;
use think\Db;
use app\admin\model\Admin;
class Base extends Controller
{
	protected $allowMthod = ['admin_login','verlogin'];
	protected $autharr = ['Index@index','Index@home'];
	
	public function _initialize()
	{
		
		$ling = request()->controller().'@'.request()->action();
		if(!$this->checkLogin() && !in_array(request()->action(),$this->allowMthod))
		{
			$this->error('请登录','index/admin_login');die;
		} else {
			if (request()->controller() != 'Base' && !in_array(request()->action(),$this->allowMthod)) {
				$uid = session('admin_uid');
				$role_id = Db::name('admin')->where('admin_id='.$uid)->find()['role_id'];
				$auth = Db::name('admin_role')->where('role_id='.$role_id)->find()['act_list'];
				if ($auth != 'all') {
					$arr = Db::name('system_menu')->where('id in ('.$auth.')')->select();
					foreach ($arr as $key => $value) {
						$this->autharr = array_merge($this->autharr,explode(',',$value['right'])); 
					}	
					if (!in_array($ling,$this->autharr)) {
						$this->error('对不起,您的权限不够');
					}
				}
				
			}
			
		}
		
		$this->bindData();
	}
	//将商城的信息的配置
	protected function bindData ()
	{
		$systems = Db::name('config')->find();
		$this->assign('systems',$systems);
	}

	//添加管理员日志
	protected function setAdminLog ($info)
	{
		Db::name('admin_log')->insert(['admin_id' => session('admin_uid'),'log_info' => $info,'log_ip' => request()->ip(),'log_url' => request()->pathinfo(),'log_time' => time()]);
	}


	//检查是否登陆 后台必须要登录
	protected function checkLogin ()
	{
		if (session('?admin_uid')) {
			return true;
		}
		return false;
	}
	//登陆界面
	public function admin_login ()
	{
		Db::name('admin')->update(['last_login' => time(),'last_ip' => request()->ip(),'admin_id' => session('admin_uid')]);
		session('admin_uid',null);
		return $this->fetch('user/admin_login');
	}

	//验证ajax传来的数据 然后进行判断返回
	public function verlogin ()
	{
		$user = input('post.');
		$username = $user['username'];
		$password = md5($user['password']);
		
		$result = Admin::where(['username' => $username,'password' => $password])->find();
		if (empty($result)) {
			return 0;
		}
		session('admin_uid' ,$result->admin_id);
		session('adminname' ,$result->username);
		session('role_id' ,$result->role_id);
		Db::name('admin_log')->insert(['admin_id' => $result->admin_id,'log_info' => '登录成功','log_ip' => request()->ip(),'log_url' => request()->pathinfo(),'log_time' => time()]);
		return 1;
		
	}
}
 
