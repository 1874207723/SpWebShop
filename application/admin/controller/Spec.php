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

	//删除掉类型的种类	
	public function delSpecType ()
	{
		$typeid = input('get.id');
		$res = Db::name('goods_type')->delete($typeid);
		$res2 = Db::name('goods_attribute')->where('type_id='.$typeid)->delete();
		Db::name('goods_attr')->where('attrid='.$typeid)->delete();
		$res3 = Db::name('spec')->where('type_id='.$typeid)->select();
		foreach ($res3 as $key => $value) {
			Db::name('spec_item')->where('spec_id='.$value['id'])->delete();
		}
		Db::name('spec')->where('type_id='.$typeid)->delete();
		return json(['res' => $res,'res2' => $res2]);
	}


	public function spmanage ()
	{
		if (!empty(input('post.'))) {
			$spec = [];
			$result = Db::table('qb_goods_type')->alias('a')->join('qb_spec s','s.type_id = a.id')->select();
			$group = DB::table('qb_goods_type')->field('id,name')->select();
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
				$zNodes[] = ['id' => -$v['id'],'pId' => $v['type_id'],'name' => $v['name'],
				'icon' =>'"'.WEB_PATH.'/static/admin/Widget/zTree/css/zTreeStyle/img/diy/diy2_2.png"'
				];
			}
			return json($zNodes);
		} else {
			return $this->fetch();
		}	
	}


	//展示添加的模板 因为是iframe层 所以要再建个模板
	public function addGoodsSpec ()
	{
		$type = Db::name('goods_type')->select();
		$this->assign(['type' => $type]);
		return $this->fetch();
	}

	public function dealAddSpec ()
	{
		$data = input('post.');
		$spec = [];
		$item = explode("\n", $data['item']);
		unset($data['item']); 
		$lastid = Db::name('spec')->insertGetId($data);
		foreach ($item as $key => $value) {
			$spec[] = ['spec_id' => $lastid,'item' => $value]; 
		}
		$result = Db::name('spec_item')->insertAll($spec);
		if (!$result) {
			return 0;
		} else {
			return 1;
		}
	}


	public function editGoodsSpec ()
	{
		$typeid = input('get.id');
		$resname = Db::name('goods_type')->where('id='.$typeid)->find();
		$res = Db::name('spec')->where('type_id='.$typeid)->select();
		//循环遍历出种类的规格信息
		foreach ($res as $key => $value) {
			$spec = [];
			//根据规格信息的id 拿到这个id的规格项的相关属性
			$result = Db::name('spec_item')->field('item')->where('spec_id='.$value['id'])->select();
			//循环遍历这个规格项 拿出item
			foreach ($result as $key2 => $value2) {
			 $spec[] = $value2['item'];
			}
			$res[$key]['item'] = implode("\n", $spec);
		}
		$this->assign(['res' => $res,'resname' => $resname]);
		return $this->fetch();
	}


	public function dealEditSpec ()
	{
		$data = input('post.');
		$typeid = input('get.id');
		
		$del = Db::name('spec')->where('type_id='.$typeid)->select();
		foreach ($del as $key => $value) {
			Db::name('spec_item')->where('spec_id='.$value['id'])->delete();
		}
		Db::name('spec')->where('type_id='.$typeid)->delete();

		if (!empty($data)) {
			$spec = [];
			$item = [];
			foreach ($data['name'] as $key => $value) {
				if ($value == '') {
					unset($data['name'][$key]);
					unset($data['order'][$key]);
					unset($data['item'][$key]);
					continue;
				}
				$spec[] = Db::name('spec')->insertGetId(['type_id' => $typeid,'name' => $data['name'][$key],'order' => $data['order'][$key]]);
			}

			foreach ($data['item'] as $key => $value) {
				$items = explode("\n",$value);
				foreach ($items as $key2 => $value2) {
					$item[] = ['spec_id' => $spec[$key],'item' => $value2]; 
				}
			}

			$result = Db::name('spec_item')->insertAll($item);		
		}
		if ($result) {
			return 1;
		} else {
			return 0;
		}
	}

}


 
