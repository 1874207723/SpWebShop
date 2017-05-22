<?php
//  Project name Q-Buy
//  Created by window on 17/5/15.
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
use app\admin\model\Brand as BrandModel;
class Brand extends Base
{

	//遍历品牌的信息
	public function brandManage ()
	{
		$show = 10;
		if (!empty(input('get.pages'))) {
			$show = input('get.pages');
		}
		$brand = new BrandModel();
		$brandinfo = $brand->order('sort')->paginate($show);
		$count = $brand->count();
		$this->assign(['brand' => $brandinfo,'count' => $count]);
		return $this->fetch();
	}

	//【ajax】接受ajax请求 进行处理修改响应的信息
	public function dealUpdatebrand()
	{
		$goods = new BrandModel();
		$action = input('get.action');
		$goodid = input('get.id');	
		$this->setAdminLog('修改品牌');
		switch ($action) {
			case 'delete':
				$goods->destroy($goodid);
				return '删除品牌成功';
			
			case 'comme':
				$goods->save(['is_hot' => 1],['id' => $goodid]);
				return '添加推荐品牌成功';

			case 'nocomme':
				$goods->save(['is_hot' => 0],['id' => $goodid]);
				return '取消推荐品牌成功';

		}

	}

	//显示品牌的信息
	public function brandDetailed ()
	{
		$show = 10;
		if (!empty(input('get.pages'))) {
			$show = input('get.pages');
		}
		$brand = new BrandModel();
		$brandinfo = $brand->order('sort')->paginate($show);
		$count = $brand->count();
		$brandid = input('get.id');
		$onebrand = BrandModel::get($brandid);
		$this->assign(['one' => $onebrand,'brand' => $brandinfo,'count' => $count]);


		return $this->fetch();
	}


	//修改品牌的信息 展示修改品牌的模板
	public function editBrand ()
	{
		$brandid = input('get.id');
		$brand = BrandModel::get($brandid);

		$cate = Db::name('goods_cate')->field('id,name')->order('sort_order')->where('level=1')->select();
		$this->assign(['cate' => $cate,'brand' => $brand]);
		return $this->fetch();
	}


	//增加品牌的信息
	public function addBrand ()
	{
		$cate = Db::name('goods_cate')->field('id,name')->order('sort_order')->where('level=1')->select();
		$this->assign(['cate' => $cate]);
		return $this->fetch();
	}

	//接受ajax传来的上传品牌图片的消息
	public function brandUpload ()
	{
		$file = request()->file('Filedata');
		$brandid = request()->param()['brandid'];
		$info = $file->validate(['ext'=>'jpeg,jpg,png,gif'])->move('./static/uploads/brand/'.$brandid,'');
	    if($info){
	        $logo_img = WEB_PATH.'/static/uploads/brand/'.$brandid.'/'.$info->getSaveName();
	    }else{
	        return $file->getError();die;
	    }
	    //查询到数据表中的logo信息 然后删掉 注意 http://后面加上路径 是不能操作的
	    $oldImgUrl = Db::name('brand')->find($brandid)['logo'];
	    if ($oldImgUrl != $logo_img && file_exists(str_replace(WEB_PATH, '',$oldImgUrl))) {
	    	unlink('.'.str_replace(WEB_PATH, '',$oldImgUrl));
	    }
	    
		$result = Db::name('brand')->where('id',$brandid)->update(['logo' => htmlspecialchars($logo_img)]);
		return json(['path' => $logo_img,
		'width' => '200',
		'height' => '100']);
	}

	//添加商品的图片请求然后保存缓存到文件夹
	public function addBrandUpload ()
	{
		//使用变量接受file文件信息 和 post数据
		$file = request()->file('Filedata');
		$goodsid = session('admin_uid');
		//若传递过来的文件不为空则不会去修改这个文件的路径
		//验证文件的类型是不是指定的格式 然后移动文件到拼接的目录 文件的名字是原来的名字
		$info = $file->move('./static/uploads/brand/temp/'.$goodsid,'temp_goods');
	    if($info){
	        $logo_img = WEB_PATH.'/static/uploads/brand/temp/'.$goodsid.'/temp_goods.'.$info->getExtension();
	        return json([
	        	'path' => $logo_img,
	        	'width' => '200',
				'height' => '100'
				]);
	    }else{
	        return $file->getError();die;
	    }
	}

	//接受ajax上传的表单的信息 然后添加品牌
	public function dealAddBrand ()
	{
		$filepath = './static/uploads/brand/temp/'.session('admin_uid');
		$dh = opendir($filepath);
		while (($file = readdir($dh)) !== false) {
			if (strstr(basename($file),'temp_goods')) {
				$imgpath = $file;
			}
		}
		closedir($dh);
		if (empty($imgpath)) {
			return '请重新上传商品图片';die;
		}

		$brand = new BrandModel();
		$data = input('post.');
		if($data['is_hot'] == 1){
			$data['is_hot'] = 1;
		} else {
			$data['is_hot'] = 0;
		}
		$result = $brand->save($data);
		if ($result != 1) {
			return '添加品牌失败';die;
		}
		$oldname = './static/uploads/brand/temp/'.session('admin_uid').'/'.$imgpath;
		$newname = './static/uploads/brand/'.$brand->id;
		
		if (!is_dir($newname)) {
			mkdir($newname);
		}
		rename($oldname, $newname.'/'.$imgpath);
		$resultimg = $brand->save(['logo' => WEB_PATH.substr($newname,1).'/'.$imgpath],['id' =>$brand->id]);
		
		if ($resultimg == 0 || $resultimg == 1) {
			$this->setAdminLog('增加品牌'.data['name']);
			return 1;
		} else {
			return '新增商品图片失败，请重新添加';die;
		}

	}


	//接受ajax上传的表单的信息 然后修改品牌
	public function dealEditBrand ()
	{
		$brand = new BrandModel();
		$data = input('post.');
		if($data['is_hot'] == 1){
			$data['is_hot'] = 1;
		} else {
			$data['is_hot'] = 0;
		}

		$result = $brand->save($data,['id' => $data['id']]);
		if ($result == 1 || $result == 0) {
			$this->setAdminLog('修改品牌'.$data['name']);
			return 1;
		} else {
			return 0;
		}
	}

}
 
