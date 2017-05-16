<?php
/**
 * @Author: Marte
 * @Date:   2017-05-13 11:07:12
 * @Last Modified by:   Marte
 * @Last Modified time: 2017-05-15 20:17:44
 */
namespace app\index\model;
use think\Model;
use think\Db;
class User extends Model
{
    //查询用户名并返回（注册页面）
    function checkName($data)
    {
        return Db::name('user')->field('user_id')->where('username', $data['username'])->find();

    }

    //添加用户数据，设置session值（注册页面）
    function addUser($data)   //adduser.html
    {
        Db::name('user')
            ->insert(["username"=>$data['username'],"password"=>md5($data['password']),"mobile"=>$data['mobile']]);
        return Db::name('user')->field('user_id')->where('username', $data['username'])->find();

    }


    // function checkLogin($data)
    // {
    //     $check = Db::name('user')->field('user_id')->where('username', $data['phoneNumber'])->where('password', md5($data['password']))->find();
    //     // if ($check) {
    //     //     $user['id'] = $check['user_id'];
    //     //     $user['username'] = $data['phoneNumber'];
    //     //     Session::set('userInfo', $user);
    //     //var_dump($check);
    //     return $check;
    // }
        // $checkName = Db::name('user')
        //     ->field('user_id')
        //     ->where('username', $data['phoneNumbe'])
        //     ->find();
        // if (!$checkName) {
        //     return 'nopassName';
        // } else {
        //     $checkPwd = Db::name('user')
        //         ->field('password')
        //         ->where('username', $data['phoneNumbe'])
        //         ->find();
        //     if ($checkPwd['password'] != md5($data['password'])) {
        //         return 'nopassPwd';
        //     } else {
        //         $id = $checkName[0]['user_id'];
        //         $user['id'] = $id;
        //         $user['username'] = $data['phoneNumbe'];
        //         Session::set('username',$data['phoneNumbe']);
        //         return 'correct';
        //     }
        // }

    // function checkName($data)
    // {
    //     $checkName = Db::name('user')
    //         ->where('username', $data['username'])
    //         ->find();
    //     if ($checkName) {
    //         return "true";
    //     } else {
    //         return "false";
    //     }
    // }

    // function checkPwd($data)
    // {
    //     $checkPwd = Db::name('user')
    //         ->field('password')
    //         ->where('username', $data['username'])
    //         ->find();
    //     if ($checkPwd['password'] == md5($data['password'])) {
    //         return "true";
    //     } else {
    //         return "false";
    //     }
    // }
}