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
            return $res;
        } else {
            return false;
        }
    }

    //查看用户收到的消息
    public function userMessage ()
    {
        return $this->fetch();
    }

}

