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

namespace app\admin\controller;
use think\Request;
use think\Db;
use app\admin\model\Order as OrderModel;
use app\admin\model\OrderAction;
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
		$list = $order->paginate(10);
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
 
