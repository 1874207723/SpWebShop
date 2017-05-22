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
use think\Request;
use app\admin\model\Order as OrderModel;
use app\admin\model\Goods;
use think\Db;
class Index extends Base
{
	 
    public function index()
    {
    	$sonlist = Db::name('system_module')->order('orderby')->where('level<>1')->select();
    	$parent_id = [];
    	foreach ($sonlist as $key => $value) {
    		if (session('role_id') != 1 && !in_array(ucfirst($value['ctl'].'@'.$value['act']),$this->autharr)) {
    			unset($sonlist[$key]);
    		} else{
    			$parent_id[] = $value['parent_id'];
    		}
    	}
    	$parent_id = join(',',array_unique($parent_id));
    	$parentlist = Db::name('system_module')->order('orderby')->where('level=1 and mod_id in ('.$parent_id.')')->select();

    	$this->assign(['parent' => $parentlist,'son' => $sonlist]);
    	return $this->fetch();
	}
	//home主页页面 获取到相关的数量的信息
	public function home ()
	{
		$order = new OrderModel();
		$goods = new Goods;
		$usercount = Db::name('user')->count();
		$artcount = Db::name('article')->count();
		$log = Db::name('admin_log')->where('admin_id='.session('admin_uid'))->order('log_id desc')->limit(5)->select();

		$ordercount = $order->count();
		$goodscount = $goods->count();
		$sumprice = $order->sum('order_amount');
		$admininfo = Db::table('qb_admin')->join('qb_admin_role r','r.role_id=qb_admin.role_id')->where('admin_id='.session('admin_uid'))->find();
		$orderinfo = [];
		$goodsinfo = [];
		$orderinfo['wait'] = $order->where('order_status=1 and pay_status=1')->count();
		$orderinfo['shipping'] = $order->where('shipping_status=0 and pay_status=1')->count();
		$orderinfo['success'] = $order->where('shipping_status=1 and order_status=4 and pay_status=1')->count();
		$orderinfo['jiesuan'] = $order->where('order_status=0')->count();
		$orderinfo['faild'] = $order->where('order_status=5')->count();
		
		$goodsinfo['hui'] = $goods->where('delete_time <> null')->count();
		$goodsinfo['sale'] = $goods->where('is_on_sale=1')->count();
		$goodsinfo['nosale'] = $goods->where('is_on_sale=0')->count();
		$goodsinfo['reply'] = Db::name('comment')->count();
	
		$this->assign([
				'log' => $log,
				'usercount' => $usercount,
				'artcount' => $artcount,
				'ordercount' => $ordercount,
				'goodscount' => $goodscount,
				'sumprice' => $sumprice,
				'admininfo' => $admininfo,
				'orderinfo' => $orderinfo,
				'goodsinfo' => $goodsinfo

			]);
		return $this->fetch();
	}
	//渲染系统设置页面 将系统设置传递过去
	public function systems ()
	{
		$result = Db::name('config')->find();
		$this->assign('result', $result);
		return $this->fetch();
	}

	//通过传过来的ajax数据修改配置表
	public function up_systems ()
	{
		//使用变量接受file文件信息 和 post数据
		$file = request()->file('logo_img');
		$data = input('post.');
		//若传递过来的文件不为空则不会去修改这个文件的路径
		if (!empty($file)) {
			//验证文件的类型是不是指定的格式 然后移动文件到拼接的目录 文件的名字是原来的名字
			$info = $file->validate(['ext'=>'jpg,png,gif'])->move('./static/uploads/logo/','');
		    if($info){
		        $logo_img = WEB_PATH.'/static/uploads/logo/'.$info->getSaveName();
		    }else{
		        return  $file->getError();die;
		    }
		    //查询到数据表中的logo信息 然后删掉 注意 http://后面加上路径 是不能操作的
		    $oldImgUrl = Db::name('config')->find(1)['logo_img'];
		    if ($oldImgUrl != $logo_img) {
		    	unlink('./'.str_replace(WEB_PATH, '',$oldImgUrl));
		    }
		    $data['logo_img'] = $logo_img;
	    }
		$result = Db::name('config')->where('id',1)->update($data);
		$this->setAdminLog('修改商城配置信息');
	    return $result;die;
	}



}
 
