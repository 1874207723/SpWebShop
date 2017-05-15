<?php
//  Project name Q-Buy
//  Created by window on 17/5/15.
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
class Spec extends Base
{
	//遍历类型的相关的信息 进行多表联合查询 查询到 类型表 和 属性表 然后 拼接好数据传送至ztree插件 进行遍历展示
	public function specManage ()
	{
		if (!empty(input('post.'))) {
			$spec = [];
			$result = Db::table('qb_goods_type')->alias('t')->join('qb_goods_attribute a','a.type_id = t.id')->select();
			$group = DB::table('qb_goods_type')->select();
			
			$zNodes[] = ['id' =>1, 'pId' => 0, 'name' => "Q-Buy Online", 'open' => true,'icon' => '"'.WEB_PATH.'/static/admin/Widget/zTree/css/zTreeStyle/img/diy/diy3.png"'];
			foreach ($group as $key => $value) {
				$zNodes[] = [
				'id' => $value['id'],
				'pId' => 1,
				'name' => $value['name'], 
				'iconOpen' =>'"'.WEB_PATH.'/static/admin/Widget/zTree/css/zTreeStyle/img/diy/diy1_1.png"', 
				'iconClose' => '"'.WEB_PATH.'/static/admin/Widget/zTree/css/zTreeStyle/img/diy/diy1_2.png"'
				];
			}
			foreach ($result as $key => $v) {
				$zNodes[] = ['id' => $v['attr_id'],'pId' => $v['id'],'name' => $v['attr_name'],
				'icon' =>'"'.WEB_PATH.'/static/admin/Widget/zTree/css/zTreeStyle/img/diy/diy2_2.png"'
				];
			}
			return json_encode($zNodes);
		} else {
			return $this->fetch();
		}
	}

	//展示添加的模板 因为是iframe层 所以要再建个模板
	public function addType ()
	{
		return $this->fetch();
	}

	//进行处理添加的类型
	public function dealAddType() {
		
		$data = input('post.');
		$name = $data['name'];
		unset($data['name']);
		$addtype = Db::name('goods_type')->insertGetId(['name' => $name]);
		if (!empty($data)) {
			$spec = [];
			foreach ($data['attr_name'] as $key => $value) {
				if ($value == '') {
					unset($data['attr_name'][$key]);
					unset($data['attr_input_type'][$key]);
					unset($data['attr_values'][$key]);
					unset($data['order'][$key]);
					continue;
				}
				$spec[] = ['type_id' => $addtype,'attr_name' => $data['attr_name'][$key],'attr_input_type' => $data['attr_input_type'][$key],'attr_values' => $data['attr_values'][$key],'order' => $data['order'][$key]];
			}
			$addtype = Db::name('goods_attribute')->insertAll($spec);				
		}
		if ($addtype) {
			return 1;
		} else {
			return 0;
		}
		

// 0 => string '浆果 瓜果 橘果 核果 仁果' (length=34)
// 1 => string '寒性水果 温性水果 热性水果' (length=38)
	}

	//进行处理修改的类型 
	public function dealEditType() {
		
		$data = input('post.');
		$typeid = input('get.id');
		
		$name = $data['name'];
		unset($data['name']);
		Db::name('goods_type')->where(['id' => $typeid])->update(['name' => $name]);
		Db::name('goods_attribute')->where('type_id='.$typeid)->delete();
		if (!empty($data)) {
			$spec = [];
			foreach ($data['attr_name'] as $key => $value) {
				if ($value == '') {
					unset($data['attr_name'][$key]);
					unset($data['attr_input_type'][$key]);
					unset($data['attr_values'][$key]);
					unset($data['order'][$key]);
					continue;
				}
				$spec[] = ['type_id' => $typeid,'attr_name' => $data['attr_name'][$key],'attr_input_type' => $data['attr_input_type'][$key],'attr_values' => $data['attr_values'][$key],'order' => $data['order'][$key]];
			}
			$addtype = Db::name('goods_attribute')->insertAll($spec);				
		}
		return 1;
	}

	//获得类型的属性
	public function getSpecType ()
	{
		$typeid = input('get.id');
		$typename = Db::name('goods_type')->find($typeid)['name'];
		$result = Db::table('qb_goods_type')->alias('t')->join('qb_goods_attribute a','a.type_id = t.id')->where('id',$typeid)->select();
		$this->assign(['res' => $result,'typename' => $typename,'id' => $typeid]);
		return $this->fetch();
	}


	public function delSpecType ()
	{
		$typeid = input('get.id');
		$res = Db::name('goods_type')->delete($typeid);
		$res2 = Db::name('goods_attribute')->where('type_id='.$typeid)->delete();
		return json(['res' => $res,'res2' => $res2]);
	}

}
 
