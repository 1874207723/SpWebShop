<?php



use think\Db;

/**
 * 获得订单的状态的名称
 * @param  [type] $order    [order_status]
 * @param  [type] $shipping [shipping_status]
 * @param  [type] $pay      [pay_status]
 * @return [type]           [description]
 */
function get_order_status ($order,$shipping,$pay) 
{
	if ($order == 3) {
		return '已取消';
	} elseif ($order == 4) {
		return '已完成';
	} elseif ($order == 5) {
		return '已作废';
	}elseif ($order == 6) {
        return '已退货退款';
    }
	$arr = [
		'000' => '未付款',
        '101' => '待发货',
		'100' => '未付款',
		'111' => '待收货',
		'211' => '待评价',
		'121' => '部分发货',
	];
	if (!empty($arr[$order.$shipping.$pay])){
		return $arr[$order.$shipping.$pay];
	} else {
		return '<font color=red>无效订单</font>';
	}

}


/**
 * 获取订单状态的 显示按钮
 * @param type $order_id  订单id
 * @param type $order     订单数组
 * @return array()
 */
function order_btn($order_id = 0, $order = array())
{
    if(empty($order)) {
        $order =Db::name('order')->where("order_id", $order_id)->find();
    }
    /**
     *  订单用户端显示按钮
    去支付     AND pay_status=0 AND order_status=0 AND pay_code ! ="cod"
    取消按钮  AND pay_status=0 AND shipping_status=0 AND order_status=0
    确认收货  AND shipping_status=1 AND order_status=0
    评价      AND order_status=1
    查看物流  if(!empty(物流单号))
     */
    $btn_arr = array(
        'pay_btn' => 0, // 去支付按钮
        'cancel_btn' => 0, // 取消按钮
        'receive_btn' => 0, // 确认收货
        'comment_btn' => 0, // 评价按钮
        'shipping_btn' => 0, // 查看物流
        'return_btn' => 0, // 退货按钮 (联系客服)
    );


    // 货到付款
    if($order['pay_code'] == 'cod')
    {
        if(($order['order_status']==0 || $order['order_status']==1) && $order['shipping_status'] == 0) // 待发货
        {
            $btn_arr['cancel_btn'] = 1; // 取消按钮 (联系客服)
        }
        if($order['shipping_status'] == 1 && $order['order_status'] == 1) //待收货
        {
            $btn_arr['receive_btn'] = 1;  // 确认收货
            $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
        }       
    }
    // 非货到付款
    else
    {
        if($order['pay_status'] == 0 && ($order['order_status'] == 1 || $order['order_status'] == 0)) // 待支付
        {
            $btn_arr['pay_btn'] = 1; // 去支付按钮
            $btn_arr['cancel_btn'] = 1; // 取消按钮
        }
        if($order['pay_status'] == 1 && in_array($order['order_status'],array(0,1)) && $order['shipping_status'] == 0) // 待发货
        {
            $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
        }
        if($order['pay_status'] == 1 && $order['order_status'] == 1  && $order['shipping_status'] == 1) //待收货
        {
            $btn_arr['receive_btn'] = 1;  // 确认收货
            $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
        }
    }
    if($order['order_status'] == 2)
    {
        $btn_arr['comment_btn'] = 1;  // 评价按钮
        $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
    }
    if($order['shipping_status'] != 0)
    {
        $btn_arr['shipping_btn'] = 1; // 查看物流
    }
    if($order['shipping_status'] == 2 && $order['order_status'] == 1) // 部分发货
    {            
        $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
    }
    
    return $btn_arr;
}


//根据商品id找到对应的信息
function get_goodsinfo_by_id ($id){
	return Db::name('goods')->where('goods_id='.$id)->field('original_img')->find()['original_img'];
}

//根据商品id找到对应的信息
function get_goods_info_by_id ($id,$info){
    return Db::name('goods')->where('goods_id='.$id)->find()[$info];
}
