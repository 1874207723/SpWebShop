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
 *　　　　　　　　　┃　　　┃ + 　　　　         神兽保佑,代码无bug
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

use think\Controller;
use think\Request;
use think\Image;
class Index extends Controller
{
    public function index()
    {
        dump(url());
        dump('http://www.wntp.com/');
    	dump(DS);
    	dump(THINK_PATH);//框架系统目录
    	dump(ROOT_PATH);//框架应用根目录
    	dump(APP_PATH);//应用目录（默认为
    	dump(CONF_PATH);//应用目录（默认为
    	dump(VENDOR_PATH);//第三方类库目录（默认为
    	dump(EXTEND_PATH);//扩展类库目录（默认为
    	dump(11);//框架系统目录
    	dump(CONF_PATH);//框架系统目录\

    	echo '<hr />';
    	$request = Request::instance();request()->bind('user','wangning');
        echo 'url---' . $request->url() . '<br/>';
        echo 'url---' . request()->url() . '<br/>';
        echo 'url---' . request()->user . '<br/>';
        echo 'url-- -' . $request->pathinfo(true) . '<br/>';
        echo 'url---' . $request->query() . '<br/>';
		echo '请求方法：' . $request->method() . '<br/>';
		echo '资源类型：' . $request->type() . '<br/>';
		echo '访问地址：' . $request->ip() . '<br/>';
		echo '是否AJax请求：' . var_export($request->isAjax(), true) . '<br/>';
		echo '请求参数：';
		dump($request->param());
		echo '请求参数：仅包含name';
		dump($request->only(['name']));
		echo '请求参数：排除name';
		dump($request->except(['name']));
        dump(input('get.'));echo '<hr />';
        dump(input('param.'));
		dump(input('route.'));

        //$image = \think\Image::open();
        //var_dump($image);
        return $this->fetch();
    }

    public function test1 ()
    {
        $data = ['name' => 'thinkphp', 'status' => '1'];
        return xml($data, 201);
    }
}
 
