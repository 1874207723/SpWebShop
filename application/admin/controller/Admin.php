<?php
//  Project name Q-Buy
//  Created by window on 17/5/20.
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
use app\admin\model\Admin as AdminModel;
class Admin extends Base
{
	public function adminList ()
	{
		$admin = new AdminModel();
		$res = $admin->paginate(20);
		$this->assign(['list' => $res]);
		return $this->fetch();
	}


	//添加管理员
	public function addAdminUser ()
	{
		$role = Db::name('admin_role')->select();
		$this->assign(['role' => $role]);
		return $this->fetch();
	}

	//修改管理员
	public function editAdminUser ()
	{
		$uid = input('get.id');
		$user = Db::name('admin')->where('admin_id='.$uid)->find();
		$role = Db::name('admin_role')->select();
		$this->assign(['role' => $role,'user' => $user]);
		return $this->fetch();
	}

	//处理添加管理员
	public function dealAddAdminUser ()
	{
		$data = input('post.');
		if ($data['act'] == 'add') {
			$data['password'] = md5($data['password']);
			$data['add_time'] = time();
			unset($data['act']);
			$res = Db::name('admin')->insert($data);
		} elseif ($data['act'] == 'edit') {
			unset($data['act']);
			if (empty($data['password'])) {
				unset($data['password']);
			} else {
				$data['password'] = md5($data['password']);
			}
			$res = Db::name('admin')->update($data);
			if ($res == 0) { $res = 1;}
		}
		
		if ($res) {
			$this->setAdminLog('添加管理员:'.$data['username']);
			$this->success("Deal Success!",url('admin/adminlist'));
		} else {
			$this->error('Deal Faild!!');
		}
	}

	//删除管理员
	public function deleteAdminUser ()
	{
		$admin_id = input('get.id');
		$res = Db::name('admin')->where('admin_id='.$admin_id)->delete();
		
		if ($res) {
			$this->setAdminLog('删除管理员');
			$this->success("Deal Success!",url('admin/adminlist'));
		} else {
			$this->error('Deal Faild!!');
		}
	}



	//角色列表的遍历
	public function roleList ()
	{
		$res = Db::name('admin_role')->where('role_id','>','1')->paginate(20);
		$this->assign(['list' => $res]);
		return $this->fetch();
	}

	//添加角色
	public function addRole ()
	{
		$res = Db::name('system_menu')->select();
		$module = Db::name('system_module')->where('level=1')->select();
		$this->assign(['list' => $res,'module' => $module]);
		return $this->fetch();
	}

	//处理添加角色的请求
	public function dealAddRole ()
	{
		$data = input('post.');
		$data['act_list'] = implode(',',$data['right']);
		unset($data['right']);
		$res = Db::name('admin_role')->insert($data);
		if ($data) {
			$this->setAdminLog('添加角色 角色名：'.$data['role_name']);
			$this->success('增加角色成功',url('admin/roleList'));
		} else {
			$this->error('增加角色失败');
		}
	}

	//修改角色
	public function editRole ()
	{
		$role_id = input('get.role_id');
		//取出指定角色的权限信息
		$role = Db::name('admin_role')->where('role_id='.$role_id)->find();
		$act = explode(',',$role['act_list']);
		//取出所有的权限列表
		$res = Db::name('system_menu')->select();
		//取出权限的一级标题列表
		$module = Db::name('system_module')->where('level=1')->select();

		$this->assign(['role' => $role,'list' => $res,'module' => $module,'act' => $act]);
		return $this->fetch();
	}

	//处理修改角色的请求
	public function dealEditRole ()
	{
		$data = input('post.');
		$data['act_list'] = implode(',',$data['right']);
		unset($data['right']);
		$res = Db::name('admin_role')->update($data);
		if ($data || $data == 0) {
			$this->setAdminLog('修改角色');
			$this->success('修改角色成功',url('admin/roleList'));
		} else {
			$this->error('修改失败');
		}
	}

	//删除角色
	public function deleteRole ()
	{
		$authid = input('post.id');
		$this->setAdminLog('删除角色');
		$res= Db::name('admin_role')->where('role_id='.$authid)->delete();
		return $res;
	}


	//权限资源列表页
	public function authList () 
	{
		$count  =Db::name('system_menu')->count();
		$res = Db::name('system_menu')->paginate(20);
		$this->assign(['list' => $res,'count'=>$count]);
		return $this->fetch();
	}

	//添加权限的页面
	public function addAuth ()
	{
		if (!empty(input('post.'))) {
			$data = input('post.');
			$data['right'] = implode(',', $data['right']);
			if (Db::name('system_menu')->insert($data)) {
				$this->setAdminLog('添加资源权限');
				$this->success('添加成功',url('admin/authlist'));
			} else {
				$this->error('添加失败');
			}
		} else {
			$res = Db::name('system_module')->where('level=1')->select();
			$this->assign(['list' => $res]);
			return $this->fetch();
		}
	}

	//修改权限的页面
	public function editAuth ()
	{
		$sysid = input('get.id');
		$info = Db::name('system_menu')->where('id='.$sysid)->find();
		$right = explode(',',$info['right']);
		$res = Db::name('system_module')->where('level=1')->select();
		$this->assign(['list' => $res,'info' => $info,'right' => $right]);
		return $this->fetch();
	}

	//处理修改权限的页面
	public function dealEditAuth ()
	{
		$data = input('post.');
		$data['right'] = implode(',', $data['right']);
		if (Db::name('system_menu')->update($data)) {
			$this->setAdminLog('修改资源权限');
			$this->success('修改成功',url('admin/authlist'));
		} else {
			$this->error('修改失败');
		}
	}

	//删除权限
	public function deleteAuth ()
	{
		$authid = input('post.id');
		$this->setAdminLog('删除资源权限');
		$res= Db::name('system_menu')->where('id='.$authid)->delete();
		return $res;
	}

	//遍历管理员日志
	public function adminLog ()
	{
		$res = Db::name('admin_log')->paginate(20);
		$this->assign(['res' => $res]);
		return $this->fetch();
	}
}
 
