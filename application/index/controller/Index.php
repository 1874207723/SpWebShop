<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Image;
class Index extends Controller
{
    public function index()
    {
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
    	$request = Request::instance();
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
		dump(input('get.'));

        //$image = \think\Image::open();
        //var_dump($image);
        return $this->fetch();
    }
}
 
