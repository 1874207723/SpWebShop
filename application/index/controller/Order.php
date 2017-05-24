<?php
/**
 * @Author: Marte
 * @Date:   2017-05-22 09:05:30
 * @Last Modified by:   Marte
 * @Last Modified time: 2017-05-24 16:24:25
 */
namespace app\index\controller;
use think\Db;

class order extends Base
{
    public function checkorder()
    {
        //头部调用信息
        $headCate = headcate();
        foreach ($headCate as $h=>$c) {
            $this->assign($h, $c);
        }

        $userAddr = Db::table('qb_user_address')->where('user_id', session('userInfo')['id'])->order('is_default desc')->select();
        $userGoods = Db::table('qb_cart')->where('user_id', session('userInfo')['id'])->where('selected',1)->select();
        //dump($userGoods);
        $totalAmount = 0;
        $totalPrice = [];
        $postage = [];
        $orderNumber = '';


        if (!empty($userGoods)) {
            foreach ($userGoods as $usg) {
                $ptg = Db::table('qb_goods')->field('is_free_shipping')->where('goods_id', $usg['goods_id'])->find();
                if ($ptg['is_free_shipping'] == 1) {
                    $postage[$usg['goods_id']] = 0.00;
                } else {
                    $postage[$usg['goods_id']] = 10.00;
                }
                $totalPrice[$usg['goods_id']] = $usg['goods_price']*$usg['goods_num']+$postage[$usg['goods_id']];
                $totalAmount += $usg['goods_price']*$usg['goods_num']+$postage[$usg['goods_id']];
            }

            $orderNumber = date('YmdHis', time());
            $orderNumber = $orderNumber.substr(uniqid(), 9, 4);
            $userGrade = Db::table('qb_user')->field('grade,pay_grade')->where('user_id', session('userInfo')['id'])->find();
            $userGrade = ($userGrade['grade']-$userGrade['pay_grade'])/100;
        }


        $this->assign([
            'title' => '确认订单',
            'userAddr' => $userAddr,
            'userGoods' => $userGoods,
            'totalAmount' => $totalAmount,
            'totalPrice' => $totalPrice,
            'postage' => $postage,
            'orderNumber' => $orderNumber,
            'userGrade' => $userGrade
            ]);

        return $this->fetch();
    }

    //订单页优惠卷判断
    public function checkBenefit()
    {
        $benefit = Db::table('qb_coupon_list')->alias('a')->join('qb_coupon b', 'a.cid=b.id')->field('use_time,name,money,condition,use_end_time')->where('code', input('post.benefit'))->where('uid', session('userInfo')['id'])->find();
        if(!empty($benefit)) {
            if ($benefit['use_time'] != 0) {
                return ['error' => true, 'message' => '该优惠卷已被使用'];
            } else if (intval($benefit['use_end_time']) > time()) {
                return ['error' => true, 'message' => '该优惠卷已失效'];
            } else if (input('.post.total') > $benefit['condition']) {
                return ['error' => true, 'message' => $benefit['name'].',条件不满足'];
            } else {
                Db::table('qb_coupon_list')->where('code', input('post.benefit'))->where('uid', 1)->update(['use_time' => time()]);
                return ['error' => false, 'message' => $benefit['money']];
            }
        } else {
            return ['error' => true, 'message' => '兑换码错误'];
        }
    }

    //订单页积分判断
    public function checkGrade()
    {
        $userGrade = Db::table('qb_user')->field('grade,pay_grade')->where('user_id', 1)->find();
        $useMoney = floor($userGrade['grade'] / 100)*100;
        $haveMoney = $userGrade['grade'] - $useMoney;
        $useMoney += $userGrade['pay_grade'];
        Db::table('qb_user')->where('user_id', session('userInfo')['id'])->update(['pay_grade'=>$useMoney]);
    }

    //订单页查询地址、商品
    public function qyOrder()
    {
        $addressid = $_POST['address'];
        $goodsid = $_POST['goods'];

        $address = Db::table('qb_user_address')->where('address_id', $addressid)->find();
        $goods = Db::table('qb_cart')->where('id', 'in', $goodsid)->select();

        return ['address'=>$address, 'goods'=>$goods];
    }


    //创建订单
    public function createOrder ()
    {
        $data = input('post.');
        $uid = session('userInfo')['id'];
        $addr = $data['address'];
        $goods_price = 0;
        $shipping_price = 0;
        $youhui = 0;
        $jifen = 0;
        $beizhu = '';
        if (!empty($data['youhui'])) {
            $youhui = $data['youhui'];
        }
        if (!empty($data['jifen'])) {
            $jifen = $data['jifen'];
        }
        if (!empty($data['beizhu'])) {
            $beizhu = strip_tags(addslashes($data['beizhu']));
        }
        foreach ($data['goods'] as $key => $value) {
            $goods_price += $value['goods_price'];
            $is_free_shipping = get_goods_info_by_id($value['goods_id'],'is_free_shipping');
            if (!$is_free_shipping) {
                $shipping_price += 10;
            }
            //get_goods_info_by_id('uid','')
        }
        $last_id = Db::name('order')->insertGetId([
                'order_sn' => $data['orderNumber'],
                'user_id' => $uid,
                'order_status' => 0,
                'shipping_status' => 0,
                'pay_status' => 0,
                'consignee' => $addr['consignee'],
                'province' => $addr['province'],
                'city' => $addr['city'],
                'district' => $addr['district'],
                'twon' => $addr['twon'],
                'address' => $addr['address'],
                'zipcode' => $addr['zipcode'],
                'mobile' => $addr['mobile'],
                'pay_code' => $data['changePay'],
                'pay_name' => 'Q-BUY支付',
                'goods_price' => $goods_price,
                'shipping_price' => $shipping_price,
                'coupon_price' => $youhui,
                'integral' => $jifen*100,
                'integral_money' => $jifen,
                'order_amount' => $data['firstPrice'],
                'total_amount' => $data['secondPrice'],
                'add_time' => time() ,
                'user_note' => $data['beizhu'],
            ]);

        foreach ($data['goods'] as $key => $v) {
            Db::table('qb_order_goods')->insert([
                'order_id' => $last_id,
                'goods_id' => $v['goods_id'],
                'goods_name' => $v['goods_name'],
                'goods_sn' => get_goods_info_by_id($v['goods_id'],'goods_sn'),
                'goods_num' => $v['goods_num'],
                'market_price' => get_goods_info_by_id($v['goods_id'],'market_price'),
                'goods_price' =>  $v['goods_price'],
                'cost_price' => get_goods_info_by_id($v['goods_id'],'cost_price'),
                'member_goods_price' => $v['goods_price'],
                'give_integral' => get_goods_info_by_id($v['goods_id'],'give_integral'),
                'spec_key' =>  $v['spec_key'],
                'spec_key_name' => $v['spec_key_name']
            ]);
        }

        Db::name('order_action')->insert([
                'order_id' => $last_id,
                'order_status' => 0,
                'shipping_status' => 0,
                'pay_status' => 0,
                'action_note' => '您提交了订单,请等待系统确认',
                'log_time' => time(),
                'static_desc' => '提交订单',
            ]);

        return $last_id;

    }

}