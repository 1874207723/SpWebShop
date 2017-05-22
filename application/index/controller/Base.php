<?php
//  Project name Q-Buy
//  Created by window on 17/5/10.
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

namespace app\index\controller;
use think\Request;
use think\Controller;
use think\Db;
class Base extends Controller
{
	protected $is_check_login = [''];


	public function _initialize()
	{
		if(!$this->checkLogin() && (in_array(Request::instance()->action(), $this->is_check_login) || $this->is_check_login[0] == '*'))
		{
			$this->redirect('user/login');
		}

		$config = Db::table('qb_config')->where('id', 1)->find();
		if (session('?userInfo')) {
			$this->assign('userDataInfo', session('userInfo'));
		}
		$this->assign([
			'configName' => $config['name'],
			'configIco' => $config['ico_img'],
			'configLogo' => $config['logo_img'],
			'configDesc' => $config['shop_desc'],
			'configBeian' => $config['beian'],
			'configAddr' => $config['addr'],
			'configYoubian' => $config['youbian'],
			'configJishu' => $config['jishu'],
			'configCompany' => $config['company']
			]);
	}

	//检查是否登陆 返回uid存在的结果
	public function checklogin() {
		return session('?userInfo');
	}
}

