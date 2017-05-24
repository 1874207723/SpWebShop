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
use app\index\model\User as UserModel;
use app\index\model\Region as regionModel;
use app\index\model\UserAddress as AddressModel;
use think\Request;
use think\Validate;
use think\Db;
use \message\Ucpaas;
use think\Controller;
class User extends Base
{
    protected $is_check_login = ['personaldata','deliveryaddress'];

    //展示注册页面
    public function register()
    {
        return $this->fetch();
    }

    //判断手机号与验证码、数据入库、设置session（注册页面）。
    public function registerIn()
    {
        if (input('post.')) {
            $data = input('post.');
            if(!captcha_check(input('post.captcha'))){
                return 1;die;
            } elseif (!$this->checkPhoneCode($data['mobile'],$data['phone'])){
                return 2;die;
            }else {
                $user = new UserModel();
                
                $user->insert(["username"=>$data['username'],"password"=>md5($data['password']),"mobile"=>$data['mobile'],"reg_time"=>time(),'last_ip' => request()->ip()]);
                $userInfo = $user->field('user_id,level')->where('mobile', $data['mobile'])->find();
                //设置session
                session('userInfo', ['id'=>$userInfo['user_id'], 'level'=>$userInfo['level'], 'mobile'=>$data['mobile']]);
                return 3;
            }
          
        }
    }

    //检查手机号和手机号验证码是否正确
    protected function checkPhoneCode ($phonenum,$code)
    {
        $res = Db::name('sms_log')->where('mobile ='.$phonenum.' and code='.$code.' and add_time+180 > '.time())->select();
        if (!empty($res)) {
            return true;
        } else {
            return false;
        }
    }

      //注册 手机号发送短信
    public function sendPhoneMessage ()
    {
        $phonenum = input('post.phonenum');
        $res = Db::name('user')->where('mobile='.$phonenum)->find();
            if (!empty($res)) {
                return '该手机号码已经被注册！！';die;
            }
       // $is_exist = Db::name('msm_log')->where('mobile='.$phonenum);
        $timeout = Db::name('sms_log')->where('status=1 and mobile='.$phonenum.' and add_time + 60 > '.time())->select();
        //dump($timeout);
        if (!empty($timeout)) {
            return '还没有超过60s哦~~';die;
        }
        $msginfo = Db::name('msg_model')->where('type=1')->find();
        $options['accountsid'] = $msginfo['account_sid'];
        $options['token'] = $msginfo['auth_token'];
        
        $message = new Ucpaas($options);
        $appId = $msginfo['appid'];
        $to = $phonenum;
        $templateId = $msginfo['templateid'];
        $shuffle = '1111222233334444555566667777888899990000';
        $randcode = substr(str_shuffle($shuffle),0,6);
        $param = $randcode.',3';
        $message = json_decode($message->templateSMS($appId,$to,$templateId,$param),true);
        if ($message['resp']['respCode'] == '000000') {
            $res = Db::name('sms_log')->insert([
                    'mobile' => $phonenum,
                     'add_time' => time(),
                     'code' => $randcode,
                     'status' => 1,
                     'scene' => 1
                ]);
            return 1;
        } else {
            $res = Db::name('sms_log')->insert([
                    'mobile' => $phonenum,
                     'add_time' => time(),
                     'code' => $randcode,
                     'status' => 0,
                     'scene' => 1
                ]);
            return '短信发送失败,请刷新页面尝试重新发送';
        }
    }

    //展示登录界面
    public function login()
    {
        return $this->fetch();
    }

    //退出登录
    public function logout()
    {
        session('userInfo', null);
        $this->success('退出成功','index/index');
    }

    //用户登录，手机号、密码匹配。
    public function loginIn()
    {
        $user = new UserModel();
        //当手机号与密码匹配存在时，返回字段信息。
        $userInfo = $user->field('user_id,mobile,level')->where(['mobile' => input('post.mobile'), 'password' => md5(input('post.password'))])->find();
        //匹配成功，设置session值。
        if (isset($userInfo)) {
            $user->where('mobile', input('post.mobile'))->update(['last_login'=>time(), 'last_ip'=>$_SERVER['REMOTE_ADDR']]);
            session('userInfo', ['id'=>$userInfo['user_id'], 'level'=>$userInfo['level'], 'mobile'=>input('post.mobile')]);
            //自动登录，设置cookie值。
            if (input('post.checkbox')) {
                cookie('userInfo', ['id'=>$userInfo['user_id'], 'level'=>$userInfo['level'], 'mobile'=>input('post.mobile')], 60*60*24*30);
            }
        }
        return json_encode($userInfo);
    }

    //个人资料页面展示
    public function personaldata()
    {
        if (session('userInfo')) {
            //模型实例化，遍历页面
            $user = UserModel::get(['user_id' => input('session.userInfo.id')]);
            $this->assign('image', $user->head_pic);
            $this->assign('username', $user->username);
            $this->assign('sex', $user->sex);
            $this->assign('age', $user->age);
            $this->assign('mobile', $user->mobile);
            $this->assign('title', '个人中心');
            return $this->fetch();
        }
    }

    //个人资料页面，表单数据处理。
    public function handleFormdata()
    {
        if (input('post.')) {
            $user = new UserModel();

            //文件上传，设置图片路径。
            $file = request()->file('file');
            if ($file) {
                $info = $file->move('static'. DS. 'uploads/user/'.input('session.userInfo.id'),'head_pic');
                //$exp = $info->getExtension();
                $imagePath = '__STATIC__/uploads/user/'.input('session.userInfo.id').'/'. $info->getSaveName();
            }

            //判断用户是否修改密码
            if (input('post.password')) {
                $validate = new Validate(['password' => 'min:6|max:10']);
                $result = $validate->check(['password' => input('post.password')]);
                //判断用户新密码格式是否正确，用tp5验证类。
                if (!$result) {
                    //用户新密码格式错误，返回错误。
                    echo json_encode(['checkPwd'=>false]);
                } else {
                    //更改密码情况下，更新用户数据并跳转页面。
                    $user->where('user_id', input('session.userInfo.id'))->update(['username'=>input('post.username'), 'sex'=>input('post.sex'), 'age'=>input('post.age'), 'password'=>md5(input('post.password')), 'head_pic'=>$imagePath]);
                    echo json_encode(['checkPwd'=>true]);
                }
            } else {
                //不需要更改密码情况下，更新用户数据并跳转。
                $user->where('user_id', input('session.userInfo.id'))->update(['username'=>input('post.username'), 'sex'=>input('post.sex'), 'age'=>input('post.age'), 'head_pic'=>$imagePath]);
                echo json_encode(['checkPwd'=>true]);
            }
        }
    }

    //遍历管理收货地址信息页面
    public function deliveryaddress()
    {
        $region = RegionModel::all(['parent_id' => 0]);
        $address = AddressModel::all(['user_id' => input('session.userInfo.id')]);
        $user_id = input('session.userInfo.id');
        $this->assign('region', $region);
        $this->assign('address', $address);
        $this->assign('user_id', $user_id);
        $this->assign('title', '我的收货地址');
        return $this->fetch();
    }

    //四级联动根据父id查找数据
    public function searchpalce2()
    {
        $region = RegionModel::all(['parent_id' => input('post.parentId')]);
        return json($region);
    }

    //插入数据，查询并返回用户收货地址信息。
    public function handleDelivery()
    {
        Db::table('qb_user_address')->insert(input('post.'));
        return json(['isInsert' => true]);
        // $result = Db::table('qb_user_address')->alias('a')->join('qb_region r','r.type_id = a.id')->select(););
    }

    //删除用户收货地址信息
    public function deleteAddr()
    {
        Db::table('qb_user_address')->delete(input('post.address_id'));
        return json(['isDelete' => true]);
    }

    //设置用户信息默认地址
    public function defaultAddr()
    {
        Db::table('qb_user_address')->where(['user_id'=>input('session.userInfo.id')])->update(['is_default'=>0]);
        Db::table('qb_user_address')->update(['is_default'=>1, 'address_id'=>input('post.address_id')]);
        return json(['isUpdate' => true]);
    }


    //遍历展示用户的订单信息
    public function orderList ()
    {
        $arr = [];
        $uid = session('userInfo')['id'];
        $userinfo = Db::name('user')->where('user_id='.$uid)->find();
        $res = Db::name('order')->where('user_id='.$uid)->paginate(10);
        $goods = Db::name('order')->alias('o')->join('qb_order_goods g','g.order_id=o.order_id')->where('user_id='.$uid)->select();
        foreach ($res as $key => $v) {
            $arr[$key] = get_order_status($v['order_status'],$v['shipping_status'],$v['pay_status']);
        }
        $pay = Db::name('order')->where('pay_status=0 and user_id='.$uid)->count();
        $shipping = Db::name('order')->where('pay_status=1 and shipping_status=0 and user_id='.$uid)->count();
        $shou = Db::name('order')->where('pay_status=1 and order_status= 1 and shipping_status=1 and user_id='.$uid)->count();
        $ping = Db::name('order')->where('pay_status=1 and order_status= 2 and shipping_status=1 and user_id='.$uid)->count();
        $this->assign([
            'goods' => $goods,
            'arr' => $arr,
            'pay' => $pay,
            'shipping' => $shipping,
            'shou' => $shou,
            'ping' => $ping,
            'userinfo' => $userinfo,
            'list' => $res, 
            'title' => '我的订单'
            ]);
        return $this->fetch();
    }

    //订单详情页面展示
    public function orderInfo()
    {
        $order_id = input()['order_id'];
        $list = Db::name('order')->where('order_id='.$order_id)->find();
        $goods = Db::name('order')->alias('o')->join('qb_order_goods g','g.order_id=o.order_id')->where('o.order_id='.$order_id)->select();
        $action = Db::name('order_action')->order('action_id desc')->where('order_id='.$order_id)->select();
        $btn = order_btn($list['order_id']);
        $this->assign([
                'action' => $action,
                'btn' => $btn,
                'title' => '订单查看',
                'res' => $list,
                'goods' => $goods
            ]);
        return $this->fetch();
    }

    //商品评价页面
    public function goodsComment ()
    {
        $goods_id = input('get.goods_id');
        $rec_id = input('get.rec_id');
        $order_id = input('get.order_id');

         $count = Db::name('comment')->where('goods_id='.$goods_id)->count();
         $level = Db::name('comment')->where('goods_id='.$goods_id)->sum('goods_rank');
         $pingfen = round($level/$count,2);
        $res = Db::name('order_goods')->alias('o')->join('qb_goods g','g.goods_id=o.goods_id')->where('o.rec_id='.$rec_id)->find();

        $othergoods = Db::name('goods')->field('goods_id,goods_name,original_img')->where('brand_id='.$res['brand_id'].' or cat_id='.$res['cat_id'])->limit(5)->select();
        $this->assign(['order_id'=> $order_id, 'goods_id' => $goods_id,'rec_id' => $rec_id,'other' => $othergoods,'pingfen' => $pingfen,'res' => $res,'title' => '商品评价']);
        return $this->fetch();
    }

    //对商品评价进行添加
    public function dealComment ()
    {
        $data = input('post.');
        $files = request()->file('img');
        $uid = session('userInfo')['id'];
        $username = get_username_by_id($uid);
        $logo_img = '';
        $is_com = Db::name('order_goods')->where('rec_id='.$data['rec_id'])->find()['is_comment'];
        if ($is_com == 1) {
            return '您已经评价过此订单了哦~';
        }
        if (!empty($files)) {
             foreach($files as $file){
                // 移动到框架应用根目录/public/uploads/ 目录下
                $info = $file->validate(['size'=>15678,'ext'=>'jpeg,jpg,png,gif'])->move('./static/uploads/comment/'.$data['goods_id']);
                if($info){
                     $logo_img .= WEB_PATH.'/static/uploads/comment/'.$data['goods_id'].'/'.$info->getSaveName().'|';
                }else{
                    // 上传失败获取错误信息
                    echo $file->getError();
                }    
            }
        }
       
        $result = Db::name('comment')->insert([
                'goods_id' => $data['goods_id'],
                'username' => $username,
                'content' => $data['content'],
                'deliver_rank' => $data['deliver_rank'],
                'goods_rank' => $data['goods_rank'],
                'service_rank' => $data['service_rank'],
                'order_id' => $data['order_id'],
                'add_time' => time(),
                'ip_address' => request()->ip(),
                'is_show' => 1,
                'user_id' => $uid,
                'img' => $logo_img
            ]);
        Db::name('order_goods')->where('rec_id='.$data['rec_id'])->update(['is_comment' => 1]);
        Db::name('goods')->where('goods_id='.$data['goods_id'])->setInc('comment_count');
        $res = Db::name('order_goods')->where('order_id='.$data['order_id'].' and is_comment=0 and is_send=1')->find();
        if (empty($res)) {
            Db::name('order')->where('order_id='.$data['order_id'])->update(['order_status'=>4,'confirm_time' => time()]);
        }
        Db::name('order_action')->insert([
                'order_id' => $data['order_id'],
                'action_user' => $uid,
                'action_note' => '您对商品进行了评价',
                'log_time' => time(),
                'status_desc' => '评价商品'
                ]);
        $give_grade = get_goods_info_by_id($data['goods_id'],'give_integral');
        $user_grade = Db::name('user')->where('user_id='.$uid)->find()['grade'];
        Db::name('user')->where('user_id='.$uid)->update(['grade' => $user_grade+$give_grade]);
        return $result;

    }


    //移动商品到收藏夹
    public function setGoodsCollect ()
    {
        $goods_id = input('get.id');
        $uid = session('userInfo')['id'];
        $check = Db::name('goods_collect')->where(['user_id' => $uid,'goods_id' => $goods_id])->find();
        if (empty($check)) {
            $res = Db::name('goods_collect')->insert(['user_id' => $uid,'goods_id' => $goods_id,'add_time' => time()]);
            return $res;
        }
        return '你已经收藏了该商品..';
    }

    //商品确认收货
    public function trueShipping ()
    {
        $psd = md5(input('post.psd'));
        $goods_id = input('post.gid');
        $rec_id = input('post.rid');
        $order_id = input('post.oid');
        $check = Db::name('user')->where(['mobile' => session('userInfo')['mobile'],'password' => $psd])->select();
        if (empty($check)) {
            return '你输入的密码不正确';
        }
        $res = Db::name('order')->where('order_id='.$order_id)->update(['order_status' => 2,'confirm_time' => time()]);
        Db::name('order_action')->insert([
                'order_id' => $order_id,
                'action_user' => session('userInfo')['id'],
                'action_note' => '订单已经被收货',
                'log_time' => time(),
                'status_desc' => '订单收货'
                ]);
        if ($res == 0) {
            return '你已经收货过了,请不要重复';
        }
        return 1;
    }

    //展示商品的物流的信息
    public function showshipping ()
    {
        $order_id = input('order_id');
        $delivery = Db::name('order_goods')->where('order_id='.$order_id)->column('delivery_id');
        $res = Db::name('delivery_doc')->where('id in('.join(',',$delivery).')' )->select();
        $str = '';
        if (empty($res)) {
            return "<p>对不起~暂无物流信息</p>";
        }
        foreach ($res as $key => $value) {
            $time = date('Y-m-d H:i:s',$value['create_time']);
            $str .= '<p style="margin-top:20px;">第:'.($key+1).'件商品</p>';
            $str .= '<p>发货单号:'.$value['invoice_no'].'</p>';
            $str .= '<p>快递名称:'.$value['shipping_name'].'</p>';
            $str .= '<p>发货时间:'.$time.'</p>';
        }
        return $str;
    }

    //商品收藏
    public function collectGoods ()
    {
        $uid = session('userInfo')['id'];

        $res = Db::name('goods_collect')->alias('c')->join('qb_goods g','g.goods_id=c.goods_id')->where('user_id='.$uid)->paginate(16);
        $count = Db::name('goods_collect')->where('user_id='.$uid)->count();
        $this->assign(['res' => $res,'title'=>'我的收藏宝贝','count'=>$count]);
        return $this->fetch();
    } 

    //删除收藏
    public function delCollect ()
    {
        $id = input('get.id');
        Db::name('goods_collect')->where('collect_id='.$id)->delete();
    }

    //遍历会员积分页
    public function userGrade ()
    {
        $uid = session('userInfo')['id'];

        $lastsign = Db::name('user')->where('user_id='.$uid)->find()['sign_time'];
  
        $lasttime = date('H:i:s',$lastsign+23*60*60-(time()-$lastsign));
        $userinfo = Db::name('user')->where('user_id='.$uid)->find();
        $this->assign(['lasttime' => $lasttime,'info' => $userinfo,'title' => '积分管理','lastsign' => $lastsign]);
        return $this->fetch();
    }

    //用户进行签到
    public function userSign ()
    {
        $uid = session('userInfo')['id'];
        $lastsign = Db::name('user')->where('user_id='.$uid)->find()['sign_time'];
        $grade = Db::name('user')->where('user_id='.$uid)->find()['grade'];

        if ($lastsign + 60*60*23 <= time()) {
            $res = Db::name('user')->where('user_id='.$uid)->update(['sign_time' => time(),'grade' => 'grade+5']);
            Db::name('user')->where('user_id='.$uid)->setInc('grade', $grade+5);
            send_user_message('您好,今天签到得了5积分哦~',$uid);
            return $res;
        } else {
            return false;
        }
    }

    //查看用户收到的消息
    public function userMessage ()
    {
        $uid = session('userInfo')['id'];
       $res = Db::name('message')->where('user_id',$uid)->paginate(15);
        $this->assign(['title' => '个人消息','res' => $res]);
        return $this->fetch();
    }

    //消息查看
    public function messageInfo ()
    {
        $id = input()['id'];
        $info = Db::name('message')->where('message_id='.$id)->find();
        Db::name('message')->update(['is_read' => 1,'message_id' => $id]);
        $this->assign(['info' => $info,'title' => '消息查看']);
        return $this->fetch();
    }




    //支付界面的展示
    public function orderPay ()
    {
        $order_id  = input()['order_id'];
        $uid = session('userInfo')['id'];
        $res = Db::name('order')->where(['order_id' => $order_id,'pay_status' => 0])->find();
        if (empty($res)) {
            $this->redirect('user/personaldata');
        }
        $goods = Db::name('order')->alias('o')->join('qb_order_goods g','g.order_id=o.order_id')->where('o.order_id='.$order_id)->select();
        $token = md5($order_id.$uid).date('YmdHis',time());
        $this->assign(['token' => $token,'res' => $res ,'goods' => $goods ,'title' => 'QBUY支付']);
        return $this->fetch();
    }

    //取消订单
    public function quxiaoOrder ()
    {
        $order_id = input('get.order_id');
        $res = Db::name('order')->where('order_id='.$order_id)->update(['order_status' => 3]);
        Db::name('order_action')->insert([
                'order_id' => $order_id,
                'action_user' => session('userInfo')['id'],
                'action_note' => '用户取消订单',
                'log_time' => time(),
                'status_desc' => '订单取消'
                ]);
        $this->redirect('user/personaldata');
    }

    //处理用户订单的 货到付款
    public function shippingCodOrder ()
    {
        $order_id = input('get.order_id');
        Db::name('order')->where('order_id='.$order_id)->update([
                'order_status' => 1,
                'shipping_status' => 0,
                'pay_status' => 1,
                'pay_code' => 'cod',
                'pay_name' => '货到付款',
                'pay_time' => time()     
            ]);
        Db::name('order_action')->insert([
                'order_id' => $order_id,
                'action_user' => session('userInfo')['id'],
                'action_note' => '用户货到付款',
                'log_time' => time(),
                'status_desc' => '订单付款（到货）'
                ]);
        $this->redirect('/index.php/index/user/paysuccess?order_id='.$order_id);
    }

    //用户支付成功的页面
    public function paySuccess ()
    {
        if (empty(input('get.order_id'))) {
           $this->redirect('index/index');
        }
        $order_id = input('get.order_id');
         $othergoods = Db::name('goods')->field('goods_id,goods_name,original_img')->where('is_on_sale=1 and is_recommend=1')->order('sort,goods_id desc')->limit(6)->select();
        $this->assign(['order_id' => $order_id,'other' => $othergoods,'title' => 'QBUY快购']);
        return $this->fetch();
    }

    //提示用户假面
    public function noticePay ()
    {
         $order_id = input('get.order_id');
        $this->assign(['title' => '注意']);
        return $this->fetch();
    }

    public function showPayHtml ()
    {
        if (!empty(input('post.'))) {
            $data = input('post.');
            $time = substr($data['token'],32);
            $token = substr($data['token'],0,32);
            $md = md5($data['order_id'].session('userInfo')['id']);
            if ($md != $token || $time+30*60 <= date('YmdHis',time())) {
                $this->error('很抱歉支付失败,原因：超时或非法操作,请您到个人中心订单页面查找订单进行付款');
            }
            $res=  Db::name('user')->where(['mobile' => $data['mobile'],'password' => md5($data['password'])])->find();
            if (empty($res)) {
                $order_id = $data['order_id'];
                $token = $data['token'];
                $res = Db::name('order')->where('order_id='.$order_id)->find();
                $this->assign(['error' => '手机号密码错误','token' => $token,'res' => $res]);
                return $this->fetch();die;
            }
            $res = Db::name('order')->where('order_id='.$data['order_id'])->update([
                        'pay_status' => 1,
                        'order_status' => 1,
                        'pay_time' => time()
                ]);
            Db::name('order_action')->insert([
                'order_id' => $data['order_id'],
                'action_user' => session('userInfo')['id'],
                'action_note' => '用户付款',
                'log_time' => time(),
                'status_desc' => '订单付款'
                ]);

            echo '<script>window.close();</script>'; 
        } else {
            $order_id = input('get.order_id');
            $token = input('get.token');
            $res = Db::name('order')->where('order_id='.$order_id)->find();
            $this->assign(['token' => $token,'res' => $res]);
            return $this->fetch();
        }
    }

    //使用ajax每隔2s去调用这个方法查看订单是不是付款了
    public function checkOrderIsPay ()
    {
        $order_id = input('get.order_id');
        $res = Db::name('order')->where('order_id='.$order_id)->find()['pay_status'];
        if ($res == 1) {
            return 1;
        } else {
            return 0;
        }
    }


}

