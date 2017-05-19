<?php
//  Project name Q-Buy
//  Created by window on 17/5/18.
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


/**
* 用户账号类
*/
namespace app\admin\model;
use think\Model;
use traits\model\SoftDelete;

class OrderAction extends Model
{
	protected $type = [
		'log_tion' => 'timestamp:Y/m/d H:i:s'
	];
	//获取器 用于订单的状态
    public function getOrderStatussAttr($value,$data)
    {
        $status = [
        		0 => '待确认',
        		1 => '已确认',
        		2 => '已收货',
        		3 => '已取消',
        		4 => '已完成', //评价完
                5 => '已作废',
        		6 => '操作中'
        		];
        return $status[$data['order_status']];
    }
    //获取器 用于发货的状态
    public function getShippingStatussAttr($value,$data)
    {
        $status = [
        		0 => '未发货',
        		1 => '已发货',
        		2 => '部分发货',
        		];
        return $status[$data['shipping_status']];
    }

    //获取器 支付的状态
    public function getPayStatussAttr($value,$data)
    {
        $status = [
        		0 => '未支付',
        		1 => '已支付',
        		];
        return $status[$data['pay_status']];
    }
    //获取器获取到用户的id对应的名字
    public function getActionUserAttr($value)
    {
    	if ($value == 0) {
    		return '管理员';
    	}
		return model('user')->getByUserId($value)['username'];
    }

}