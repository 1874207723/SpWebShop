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
            $data = [];
            if(!captcha_check(input('post.captcha'))){
                $data['checkCaptcha'] = null;
            } else {
                $user = new UserModel();
                $user->insert(["username"=>input('post.username'),"password"=>md5(input('post.password')),"mobile"=>input('post.mobile'),"reg_time"=>time()]);
                $userInfo = $user->field('user_id,level')->where('mobile', input('post.mobile'))->find();
                //设置session
                session('userInfo', ['id'=>$userInfo['user_id'], 'level'=>$userInfo['level'], 'mobile'=>input('post.mobile')]);
                $data['checkCaptcha'] = true;
            }
            return json_encode($data);
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

}

