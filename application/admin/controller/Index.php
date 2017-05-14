<?php
//  Project name Q-Buy
//  Created by window on 17/5/13.
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
class Index extends Base
{
	 
    public function index()
    {
    	return $this->fetch();
	}

	public function home ()
	{
		$usercount = Db::name('user')->count();
		$this->assign(['usercount' => $usercount]);
		return $this->fetch();
	}
	//渲染系统设置页面 将系统设置传递过去
	public function systems ()
	{
		$result = Db::name('config')->find();
		$this->assign('result', $result);
		return $this->fetch();
	}

	//通过传过来的ajax数据修改配置表
	public function up_systems ()
	{
		//使用变量接受file文件信息 和 post数据
		$file = request()->file('logo_img');
		$data = input('post.');
		//若传递过来的文件不为空则不会去修改这个文件的路径
		if (!empty($file)) {
			//验证文件的类型是不是指定的格式 然后移动文件到拼接的目录 文件的名字是原来的名字
			$info = $file->validate(['ext'=>'jpg,png,gif'])->move('./static/uploads/logo/','');
		    if($info){
		        $logo_img = WEB_PATH.'/static/uploads/logo/'.$info->getSaveName();
		    }else{
		        return  $file->getError();die;
		    }
		    //查询到数据表中的logo信息 然后删掉 注意 http://后面加上路径 是不能操作的
		    $oldImgUrl = Db::name('config')->find(1)['logo_img'];
		    if ($oldImgUrl != $logo_img) {
		    	unlink('./'.str_replace(WEB_PATH, '',$oldImgUrl));
		    }
		    $data['logo_img'] = $logo_img;
	    }
		$result = Db::name('config')->where('id',1)->update($data);
	    return $result;die;
	}



}
 
