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
use think\Db;
use app\admin\model\Goods;
class Cate extends Base
{
	

	public function shoplist ()
	{
		return $this->fetch();
	}


	//接受ajax传过来的 请求板块数据的请求 返回jason数据
	public function getCateData () 
	{
		$zNodes[] = ['id' =>1, 'pId' => 0, 'name' => "Q-Buy Online", 'open' => true,'icon' => '"http://www.wntp.com/static/admin/Widget/zTree/css/zTreeStyle/img/diy/diy3.png"'];
		$result = Db::name('goods_cate')->field('id,parent_id,name,parent_id_path')->where('level in (1,2)')->select();

		foreach ($result as $key => $v) {
			$zNodes[] = ['id' => $v['id'],'pId' => $v['parent_id'],'name' => $v['name'], 'iconOpen' =>"'http://www.wntp.com/static/admin/Widget/zTree/css/zTreeStyle/img/diy/diy1_1.png'", 'iconClose' => '"http://www.wntp.com/static/admin/Widget/zTree/css/zTreeStyle/img/diy/diy1_2.png"'];
		}
		return json_encode($zNodes,JSON_UNESCAPED_UNICODE);
	}

	//接受ajax传过来的 分类的id信息 获得商品列表 
	public function getGoodsList ()
	{
		$goods = new Goods();
		$id = input('get.id');
		$result = $goods->field('goods_id,cat_id,goods_sn,store_count,goods_name,market_price,shop_price,is_recommend,is_on_sale,is_hot,is_new,on_time,last_update')->where('cat_id='.$id )->limit(10)->order('sort','asc')->paginate(10);
		$user = Goods::find(1);
		return json($result);
	}



}
 
