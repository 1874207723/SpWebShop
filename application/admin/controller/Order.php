<?php
//  Project name Q-Buy
//  Created by window on 17/5/19.
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
use app\admin\model\Order as OrderModel;
use app\admin\model\OrderAction;
use app\admin\model\OrderGoods;
class Order extends Base
{
	//遍历订单的柱状图信息
	public function transaction ()
	{
		$order = new OrderModel();
		$sumprice = $order->sum('order_amount');
		$ordercount = $order->count();
		$complaycount = $order->where('order_status=4')->count();
		$waitcount = $order->where('order_status in (0,1,2)')->count();
		$nocount = $order->where('order_status in (5)')->count();
		$this->assign([
				'sumprice' => $sumprice,
				'ordercount' => $ordercount,
				'complaycount' => $complaycount,
				'nocount' => $nocount,
				'waitcount' => $waitcount
				]);
		return $this->fetch();
	}


	//订单的图标信息
	public function orderChart ()
	{
		if (!empty(input('post.action'))) {
			$res=  Db::table('qb_order')->alias('o')->join('qb_region r','o.province = r.id')->field('r.name,count(o.province) as count')->where('level=1')->group('o.province')->select();
			$str = '[';
			//dump($res);
			foreach ($res as $key => $value) {
				$str .= "{name: '".mb_substr($value['name'],0,mb_strlen($value['name'])-1)."' ,value: {$value['count']} },";
			}
			$str .= ']';
			return $str;
		}
		
		return $this->fetch();
	}


	//遍历订单管理
	public function orderForm ()
	{
		$order = new OrderModel();
		$list = $order->paginate(20);
		$this->assign(['list' => $list]);
		return $this->fetch();
	}

	//【ajax】对订单进行条件查询
	public function searchOrder () 
	{

		$data = input('post.');

		$where = 'where 1=1 ';
		$keytype =  input('post.keytype');
		$keywords = input('post.keywords');
		if($keytype){
			$where .= " AND $keytype like '%$keywords%' ";
		}
		
		if(input('post.order_status')){
			$where .= " AND order_status = ".input('post.order_status');
		}
		
		$add_time_begin = input('post.add_time_begin');
		$add_time_end =  input('post.add_time_end');
		/*if($add_time_begin && $add_time_end){
			$where .= "and add_time BETWEEN '$add_time_begin' AND '$add_time_end' ";
		}*/
		$sql = "select *,FROM_UNIXTIME(add_time,'%Y-%m-%d') as create_time from qb_order $where order by order_id";
		$orderList = DB::query($sql);
		return $orderList;
	}


	//遍历查看表的信息
	public function orderInfo ()
	{
		$orderid = input('get.id');
		$order = new OrderModel();

		//$ids = implode(',',$this->getParentOrder($orderid));
		$res = $order->alias('o')->join('qb_order_goods g','g.order_id=o.order_id')->join('qb_goods gg','gg.goods_id=g.goods_id')->field('*,o.goods_price as gpric')->where('o.order_id in ('.$orderid .')')->select();
		$log = OrderAction::all(['order_id' => $orderid]);
		$this->assign(['info' => $res,'log' => $log]);
		$this->setAdminLog('查看订单 订单ID：'.$orderid);
		return $this->fetch();
	}

	//打印订单页面
	public function printOrder ()
	{
		$orderid = input('get.id');
		$order = new OrderModel();

		//$ids = implode(',',$this->getParentOrder($orderid));
		$res = $order->alias('o')->join('qb_order_goods g','g.order_id=o.order_id')->join('qb_goods gg','gg.goods_id=g.goods_id')->field('*,o.goods_price as gpric')->where('o.order_id in ('.$orderid .')')->select();
		$log = OrderAction::all(['order_id' => $orderid]);
		$this->assign(['info' => $res,'log' => $log]);
		$this->setAdminLog('打印订单 订单ID：'.$orderid);
		return $this->fetch();
	}


	//修改订单页面
	public function editOrder ()
	{
		$orderid = input('get.id');
		$order = new OrderModel();

		//$ids = implode(',',$this->getParentOrder($orderid));
		$res = $order->alias('o')->join('qb_order_goods g','g.order_id=o.order_id')->join('qb_goods gg','gg.goods_id=g.goods_id')->field('*,o.goods_price as gpric')->where('o.order_id in ('.$orderid .')')->select();
		$log = OrderAction::all(['order_id' => $orderid]);
		$region = Db::name('region');
		$province = $region->where('level=1')->select();
		$city = $region->where('parent_id='.$res[0]->province)->select();
		$district = $region->where('parent_id='.$res[0]->city)->select();
		$twon = $region->where('parent_id='.$res[0]->district)->select();
		$wuliu = Db::name('shipping')->select();
		$this->assign([
				'info' => $res,
				'log' => $log,
				'province' => $province,
				'city' => $city,
				'district' => $district,
				'twon' => $twon,
				'wuliu' => $wuliu
				]);
		return $this->fetch();
	}



	//根据id找到这个id的所有的相关的订单
	protected function getParentOrder($id) {
		$order = new OrderModel();
		$res =$order->get($id);
		$res2 = $order->where('parent_sn',$res->order_sn)->field('order_id')->select();
		if (!empty($res->parent_sn)) {
			$res=  $this->getParentOrder($this->getIdBySn($res->parent_sn));
			return $res;
		} elseif(!empty($res2)) {
			$arr = [];
			$arr[] = $id;
			foreach ($res2 as $key => $value) {
				$arr[] = $value['order_id'];
			}
			
			return $arr;
		}else {
			return [0 => $id];
		}
	}

	//根据单号 获取到 对应的id
	protected function getIdBySn ($sn)
	{
		return OrderModel::getByOrderSn($sn)['order_id'];
	}

	//根据id  找到下一级城市的相关列表
	public function getRegion ()
	{
		$id = input('post.id');
		$data = Db::name('region')->where('parent_id='.$id)->select();
		return $data;
	}



	//处理修改订单的请求
	public function dealeditorder ()
	{
		$order = new OrderModel();
		$ordergoods = new OrderGoods();
		$orderaction = new OrderAction();
		$data = input('post.');
		$orderid = $data['order_id'];
		$goods_price = [];
		$sum_price = 0;
		$flag = true;
		$oldGoodsPrice = $order->getByOrderId($orderid)->goods_price;
		foreach ($data['rec_id'] as $key => $value) {
			$goods_price[] = ['rec_id' => $value,'goods_num' => $data['goods_sum'][$key]];
			$sum_price += $data['goods_price'][$key]*$data['goods_sum'][$key];
 		}
 		$limit = $sum_price - $oldGoodsPrice;
 		$res1 = $ordergoods->saveAll($goods_price);
 		
	 	$order->where('order_id='.$orderid)->setInc('goods_price',$limit);
	 	$order->where('order_id='.$orderid)->setInc('order_amount',$limit);
	 	$order->where('order_id='.$orderid)->setInc('total_amount',$limit);
 		$orderaction->save(['order_id' => $orderid,'order_status' => 6 ,'action_note' => '管理员修改','log_time' => time() ,'status_desc' => '修改订单']);
 		unset($data['rec_id']);
 		unset($data['goods_price']);
 		unset($data['goods_sum']);
 		$order->save($data,$data['order_id']);
 		$this->setAdminLog('修改了订单 订单ID：'.$orderid);
 		$this->success('修改成功',url('order/orderinfo',null,'?id='.$orderid));
	}

	//【ajax】对订单的状态进行改变
	public function orderAction () 
	{
		$order = new OrderModel();
		$orderid = input('order_id');
		$action = input('action');
		$note = input('note');
		$orderaction = new OrderAction();
		$this->setAdminLog('修改'.$orderid.'订单状态 => '.$action);
		switch ($action) {
			case 'pay':
			 	$flag = $order->save(['pay_status' => 1],['order_id' => $orderid]);
			 	$orderaction->save(['order_id' => $orderid,'pay_status' => 1,'action_note' => $note,'log_time' => time() ,'status_desc' => '付款成功']);
			break;
			case 'invalid':
				$flag = $order->save(['order_status'=>5],['order_id' => $orderid]);
				$orderaction->save(['order_id' => $orderid,'order_status' => 5 ,'action_note' => $note,'log_time' => time() ,'status_desc' => '订单作废']);
			break;
			case 'nopay':
				$flag = $order->save(['pay_status'=>0],['order_id' => $orderid]);
				$orderaction->save(['order_id' => $orderid,'pay_status'=>0 ,'action_note' => $note,'log_time' => time() ,'status_desc' => '未支付']);
			break;
			case 'allow':
				$flag = $order->save(['order_status'=>4],['order_id' => $orderid]);
				$orderaction->save(['order_id' => $orderid,'order_status'=>4 ,'action_note' => $note,'log_time' => time() ,'status_desc' => '订单完成']);
			break;
			case 'fahuoall':
				$flag = $order->save(['shipping_status'=>1],['order_id' => $orderid]);
				$orderaction->save(['order_id' => $orderid,'shipping_status'=>1 ,'action_note' => $note,'log_time' => time() ,'status_desc' => '已发货']);
			break;
			
			case 'nofahuo':
				$flag = $order->save(['shipping_status' =>0],['order_id' => $orderid]);
				$orderaction->save(['order_id' => $orderid,'shipping_status' =>0 ,'action_note' => $note,'log_time' => time() ,'status_desc' => '未发货']);
				Db::name('order_goods')->where('order_id='.$orderid)->update(['is_send' => 0,'is_comment' => 0]);
				Db::name('delivery_doc')->where('order_id='.$orderid)->delete();
				Db::name('order_action')->where('order_id='.$orderid)->delete();
			break;
			case 'chushihua':
				$flag = $order->save(['shipping_status' =>0,'order_status' =>0 ,'pay_status' =>0 ],['order_id' => $orderid]);
				Db::name('order_goods')->where('order_id='.$orderid)->update(['is_send' => 0,'is_comment' => 0]);
				Db::name('order_action')->where('order_id='.$orderid)->delete();
				Db::name('delivery_doc')->where('order_id='.$orderid)->delete();
				$orderaction->save(['order_id' => $orderid,'shipping_status' =>0 ,'action_note' => $note,'log_time' => time() ,'status_desc' => '初始化订单']);
			break;
			case 'true':
				$flag = $order->save(['order_status' =>1],['order_id' => $orderid]);
				$orderaction->save(['order_id' => $orderid,'shipping_status' =>1 ,'action_note' => $note,'log_time' => time() ,'status_desc' => '确认订单']);
			break;
		}
		return $flag;
	}


	//渲染待发货的商品的模板
	public function waitOrder ()
	{
		$order = new OrderModel();
		$list = $order->where('(order_status in (1,2)) and (shipping_status in (0,2)) and pay_status=1')->paginate(20);
		$this->assign(['list' => $list]);
		return $this->fetch();
	}

	//【ajax】对订单进行条件查询
	public function searchWaitOrder () 
	{

		$data = input('post.');

		$where = 'where 1=1 and (order_status in (1,2)) and (shipping_status in (0,2)) and pay_status=1 ';
		$keytype =  input('post.keytype');
		$keywords = input('post.keywords');
		if($keytype){
			$where .= " AND $keytype like '%$keywords%' ";
		}
		
		if(input('post.order_status')){
			$where .= " AND order_status = ".input('post.order_status');
		}
		
		$add_time_begin = input('post.add_time_begin');
		$add_time_end =  input('post.add_time_end');
		/*if($add_time_begin && $add_time_end){
			$where .= "and add_time BETWEEN '$add_time_begin' AND '$add_time_end' ";
		}*/
		$sql = "select *,FROM_UNIXTIME(add_time,'%Y-%m-%d') as create_time from qb_order $where order by order_id";
		$orderList = DB::query($sql);
		return $orderList;
	}

	//对没有发货的订单进行 遍历
	public function sendOrder() 
	{
		$orderid = input('get.id');
		$order = new OrderModel();
		$shipping = Db::name('shipping')->select();
		$res = $order->alias('o')->join('qb_order_goods g','g.order_id=o.order_id')->join('qb_goods gg','gg.goods_id=g.goods_id')->field('*,o.goods_price as gpric')->where('g.is_send=0 and o.order_id in ('.$orderid .')')->select();
		$delivery = Db::name('delivery_doc')->where('order_id='.$orderid)->select();
		$this->assign([
				'shipping' => $shipping,
				'info' => $res,
				'delivery' => $delivery
				]);
		return $this->fetch();

	}

	//接受发货单的信息进行发货
	public function  dealAddSendOrder ()
	{
		$order = new OrderModel();
		$orderaction = new OrderAction();
		$ordergoods = new OrderGoods();
		$data = input('post.');
		$shipping_id = $data['shipping_id'];
		$shipping_name = Db::name('shipping')->where('shipping_id='.$shipping_id)->find()['shipping_name']; 
		$shipping_code = Db::name('shipping')->where('shipping_id='.$shipping_id)->find()['shipping_code']; 
		Db::name('order')->where('order_id='.$data['order_id'])->update(['shipping_code' => $shipping_code,'shipping_name' => $shipping_name]);
		if ($data['goodsnum'] == count($data['goods'])) {
			$shipping_status = 1;
		} else {
			$shipping_status = 2;
		}
		$order->save(['shipping_status' => $shipping_status,'shipping_time' => time()],['order_id' => $data['order_id']]);
		$info = $order::get($data['order_id']);
		//dump($data);die;
		$uid = Db::name('user')->find()['user_id'];
		foreach ($data['goods'] as $key => $value) {
			Db::name('delivery_doc')->insert([
				'order_id' => $info->order_id,
				'order_sn' => $info->order_sn, 
				'user_id' => $uid, 
				'admin_id' => session('admin_uid'), 
				'consignee' => $info->consignee, 
				'zipcode' => $info->zipcode, 
				'mobile' => $info->mobile, 
				'twon' => $info->twon, 
				'province' => $info->province, 
				'city' => $info->city, 
				'district' => $info->district, 
				'address' => $info->address, 
				'shipping_name' => $shipping_name,
				'shipping_code' => $shipping_code,
				'invoice_no' =>$data['invoice_no'], 
				'note' => $data['note'], 
				'create_time' => time()
				]);	
			$delid = Db::name('delivery_doc')->getLastInsID();
			$ordergoods->save(['is_send' => 1,'delivery_id' => $delid],['rec_id' => $value]);
			$orderaction->save(['order_id' => $info->order_sn,'shipping_status' =>$shipping_status ,'action_note' => $data['note'],'log_time' => time() ,'status_desc' => '商品发货']);
			send_user_message('尊敬的客户您好,您的订单'.$info->order_sn.'已经发货,请耐心等待宝贝的到来吧',$uid);
			$this->setAdminLog($info->order_id.'订单发货');
			$this->success('发货成功',url('order/waitorder'));
		}

	}


	//遍历日志的记录表
	public function orderLog ()
	{
		$info = Db::name('order_action')->paginate(25);
		$user = Db::name('admin')->select();
		$this->assign(['info' => $info,'user' => $user]);
		return $this->fetch();
	}



    //进行退款的管理
    public function returnOrder ()
    {
    	$res = Db::name('return_goods')->paginate(20);
    	$this->assign(['list' => $res]);
    	return $this->fetch();
    }

    //查看编辑退款订单
    public function editReturn ()
    {
    	$orderid = input('get.id');
    	$res = Db::name('return_goods')->where('id='.$orderid)->find();
    	$money = Db::name('order_goods')->where(['order_id' => $res['order_id'],'goods_id' => $res['goods_id']])->find()['goods_price'];
    	$this->assign(['info' => $res,'money' => $money]);
    	return $this->fetch();
    }

    //对退货/退款的订单进行判断处理
    public function dealReturnOrder ()
    {
    	$data= input('post.');
    	$info = Db::name('return_goods')->where('id='.$data['id'])->field('user_id,order_sn,order_id')->find();
    	$result = Db::name('return_goods')->update($data);
    	send_user_message('尊敬的客户您好,您申请的退款/退货订单'.$info['order_sn'].'已经被处理,快去查看吧<br /><a href="'.url('index/user/orderinfo',['order_id' => $info['order_id']]).'" style="color:#f00;font-size:20px;margin-left:50px;">戳我~查看订单</a>',$info['user_id']);
    	$this->success('修改成功',url('order/returnorder'));
    	
    }

    //进行金额的退款
    public function accountEdit ()
    {
    	$data = input('get.');
    	$this->assign(['data'=> $data]);
    	return $this->fetch();
    }




	//下载订单的总表
	public function export_order()
    {
	    	//搜索条件
			$where = 'where 1=1 ';
			$consignee = input('post.consignee');
			if($consignee){
				$where .= " AND consignee like '%$consignee%' ";
			}
			$order_sn =  input('post.order_sn');
			if($order_sn){
				$where .= " AND order_sn = '$order_sn' ";
			}
			if(input('post.order_status')){
				$where .= " AND order_status = ".input('post.order_status');
			}

			if(input('post.shipping_status')){
				$where .= " AND shipping_status = ".input('post.shipping_status');
			}
			
			if(input('post.pay_status')){
				$where .= " AND pay_status = ".input('post.pay_status');
			}

			$timegap = input('post.timegap');
			if($timegap){
				$gap = explode('-', $timegap);
				$begin = strtotime($gap[0]);
				$end = strtotime($gap[1]);
				$where .= " AND add_time>$begin and add_time<$end ";
			}


			    
			$sql = "select *,FROM_UNIXTIME(add_time,'%Y-%m-%d') as create_time from qb_order $where order by order_id";
	    	$orderList = DB::query($sql);
	    	$strTable ='<table width="500" border="1">';
	    	$strTable .= '<tr>';
	    	$strTable .= '<td style="text-align:center;font-size:12px;width:120px;">订单编号</td>';
	    	$strTable .= '<td style="text-align:center;font-size:12px;" width="100">日期</td>';
	    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货人</td>';
	    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货地址</td>';
	    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">电话</td>';
	    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">订单金额</td>';
	    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">实际支付</td>';
	    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付方式</td>';
	    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付状态</td>';
	    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">发货状态</td>';
	    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品信息</td>';
	    	$strTable .= '</tr>';
		    if(is_array($orderList)){
		    	$region	= Db::name('region')->column('name','id');
		    	$region[0] = '';
		    	foreach($orderList as $k=>$val){
		    		$strTable .= '<tr>';
		    		$strTable .= '<td style="text-align:center;font-size:12px;">&nbsp;'.$val['order_sn'].'</td>';
		    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['create_time'].' </td>';       		
	    	    	$strTable .= '<td style="text-align:left;font-size:12px;">['.$val['consignee'].']</td>';
	    	  		$strTable .= '<td style="text-align:left;font-size:12px;">'."
		                {$region[$val['province']]},
		                {$region[$val['city']]},{$region[$val['district']]},{$region[$val['twon']]},{$val['address']}".' </td>';

	                
		    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['mobile'].'</td>';
		    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['goods_price'].'</td>';
		    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['order_amount'].'</td>';
		    		if ($val['pay_status'] == 1) {
		    			$val['pay_status'] = '已支付';
		    		} else {
		    			$val['pay_status'] = '未支付';
		    		}
		    		if ($val['shipping_status'] == 1) {
		    			$val['shipping_status'] = '已发货';
		    		} elseif($val['shipping_status'] == 2) {
		    			$val['shipping_status'] = '部分发货';
		    		} else {
		    			$val['shipping_status'] = '未发货';
		    		}
		    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['pay_name'].'</td>';
		    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['pay_status'].'</td>';
		    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['shipping_status'].'</td>';
		    		$orderGoods = Db::name('order_goods')->where('order_id='.$val['order_id'])->select();
		    		$strGoods="";
		    		foreach($orderGoods as $goods){
		    			$strGoods .= "商品编号：".$goods['goods_sn']." 商品名称：".$goods['goods_name'];
		    			if ($goods['spec_key_name'] != '') $strGoods .= " 规格：".$goods['spec_key_name'];
		    			$strGoods .= "<br />";
		    		}
		    		unset($orderGoods);
		    		$strTable .= '<td style="text-align:left;font-size:12px;">'.$strGoods.' </td>';
		    		$strTable .= '</tr>';
		    	}
		    }
	    	$strTable .='</table>';
	    	unset($orderList);
	    	downloadExcel($strTable,'QBuy');
	    	exit();
    }




	public function allowAction () 
	{
		$order = Db::name('order');
		$month1 = '2016-1-1';
		$month2 = '2016-2-1';
		$month3 = '2016-3-1';
		$month4 = '2016-4-1';
		$month5 = '2016-5-1';
		$month6 = '2016-6-1';
		$month7 = '2016-7-1';
		$month8 = '2016-8-1';
		$month9 = '2016-9-1';
		$month10 = '2016-10-1';
		$month11 = '2016-11-1';
		$month12 = '2016-12-1';
		$month13 = '2016-12-30';
		$action = input('post.action');
		switch ($action) {
			case 'all':
				$count1 = $order->field('count')->where('add_time','between time',[$month1,$month2])->count();
				$count2 = $order->field('count')->where('add_time','between time',[$month2,$month3])->count();
				$count3 = $order->field('count')->where('add_time','between time',[$month3,$month4])->count();
				$count4 = $order->field('count')->where('add_time','between time',[$month4,$month5])->count();
				$count5 = $order->field('count')->where('add_time','between time',[$month5,$month6])->count();
				$count6 = $order->field('count')->where('add_time','between time',[$month6,$month7])->count();
				$count7 = $order->field('count')->where('add_time','between time',[$month7,$month8])->count();
				$count8 = $order->field('count')->where('add_time','between time',[$month8,$month9])->count();
				$count9 = $order->field('count')->where('add_time','between time',[$month9,$month10])->count();
				$count10 = $order->field('count')->where('add_time','between time',[$month10,$month11])->count();
				$count11 = $order->field('count')->where('add_time','between time',[$month11,$month12])->count();
				$count12 = $order->field('count')->where('add_time','between time',[$month12,$month13])->count();
				return [$count1,$count2,$count3,$count4,$count5,$count6,$count7,$count8,$count9,$count10,$count11,$count12];
			case 'waitpay':
				$count1 = $order->field('count')->where('pay_status=0')->where('add_time','between time',[$month1,$month2])->count();
				$count2 = $order->field('count')->where('pay_status=0')->where('add_time','between time',[$month2,$month3])->count();
				$count3 = $order->field('count')->where('pay_status=0')->where('add_time','between time',[$month3,$month4])->count();
				$count4 = $order->field('count')->where('pay_status=0')->where('add_time','between time',[$month4,$month5])->count();
				$count5 = $order->field('count')->where('pay_status=0')->where('add_time','between time',[$month5,$month6])->count();
				$count6 = $order->field('count')->where('pay_status=0')->where('add_time','between time',[$month6,$month7])->count();
				$count7 = $order->field('count')->where('pay_status=0')->where('add_time','between time',[$month7,$month8])->count();
				$count8 = $order->field('count')->where('pay_status=0')->where('add_time','between time',[$month8,$month9])->count();
				$count9 = $order->field('count')->where('pay_status=0')->where('add_time','between time',[$month9,$month10])->count();
				$count10 = $order->field('count')->where('pay_status=0')->where('add_time','between time',[$month10,$month11])->count();
				$count11 = $order->field('count')->where('pay_status=0')->where('add_time','between time',[$month11,$month12])->count();
				$count12 = $order->field('count')->where('pay_status=0')->where('add_time','between time',[$month12,$month13])->count();
			
				return [$count1,$count2,$count3,$count4,$count5,$count6,$count7,$count8,$count9,$count10,$count11,$count12,];
			case 'alsopay':
				$count1 = $order->field('count')->where('pay_status=1')->where('add_time','between time',[$month1,$month2])->count();
				$count2 = $order->field('count')->where('pay_status=1')->where('add_time','between time',[$month2,$month3])->count();
				$count3 = $order->field('count')->where('pay_status=1')->where('add_time','between time',[$month3,$month4])->count();
				$count4 = $order->field('count')->where('pay_status=1')->where('add_time','between time',[$month4,$month5])->count();
				$count5 = $order->field('count')->where('pay_status=1')->where('add_time','between time',[$month5,$month6])->count();
				$count6 = $order->field('count')->where('pay_status=1')->where('add_time','between time',[$month6,$month7])->count();
				$count7 = $order->field('count')->where('pay_status=1')->where('add_time','between time',[$month7,$month8])->count();
				$count8 = $order->field('count')->where('pay_status=1')->where('add_time','between time',[$month8,$month9])->count();
				$count9 = $order->field('count')->where('pay_status=1')->where('add_time','between time',[$month9,$month10])->count();
				$count10 = $order->field('count')->where('pay_status=1')->where('add_time','between time',[$month10,$month11])->count();
				$count11 = $order->field('count')->where('pay_status=1')->where('add_time','between time',[$month11,$month12])->count();
				$count12 = $order->field('count')->where('pay_status=1')->where('add_time','between time',[$month12,$month13])->count();
			
				return [$count1,$count2,$count3,$count4,$count5,$count6,$count7,$count8,$count9,$count10,$count11,$count12,];
			case 'waitshipping':
				$count1 = $order->field('count')->where('shipping_status=0 and pay_status=1')->where('add_time','between time',[$month1,$month2])->count();
				$count2 = $order->field('count')->where('shipping_status=0 and pay_status=1')->where('add_time','between time',[$month2,$month3])->count();
				$count3 = $order->field('count')->where('shipping_status=0 and pay_status=1')->where('add_time','between time',[$month3,$month4])->count();
				$count4 = $order->field('count')->where('shipping_status=0 and pay_status=1')->where('add_time','between time',[$month4,$month5])->count();
				$count5 = $order->field('count')->where('shipping_status=0 and pay_status=1')->where('add_time','between time',[$month5,$month6])->count();
				$count6 = $order->field('count')->where('shipping_status=0 and pay_status=1')->where('add_time','between time',[$month6,$month7])->count();
				$count7 = $order->field('count')->where('shipping_status=0 and pay_status=1')->where('add_time','between time',[$month7,$month8])->count();
				$count8 = $order->field('count')->where('shipping_status=0 and pay_status=1')->where('add_time','between time',[$month8,$month9])->count();
				$count9 = $order->field('count')->where('shipping_status=0 and pay_status=1')->where('add_time','between time',[$month9,$month10])->count();
				$count10 = $order->field('count')->where('shipping_status=0 and pay_status=1')->where('add_time','between time',[$month10,$month11])->count();
				$count11 = $order->field('count')->where('shipping_status=0 and pay_status=1')->where('add_time','between time',[$month11,$month12])->count();
				$count12 = $order->field('count')->where('shipping_status=0 and pay_status=1')->where('add_time','between time',[$month12,$month13])->count();
			
				return [$count1,$count2,$count3,$count4,$count5,$count6,$count7,$count8,$count9,$count10,$count11,$count12,];
				
			}
	}
	

}
 
