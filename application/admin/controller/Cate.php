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
use app\admin\model\Goods;
class Cate extends Base
{
	

	public function shoplist ()
	{
		return $this->fetch();
	}


	//接受ajax传过来的 请求板块数据的请求 返回jason数据
	public function getCateData () 
	{
		$zNodes[] = ['id' =>1, 'pId' => 0, 'name' => "Q-Buy Online", 'open' => true,'icon' => '"http://www.wntp.com/static/admin/Widget/zTree/css/zTreeStyle/img/diy/diy3.png"'];
		$result = Db::name('goods_cate')->field('id,parent_id,name,parent_id_path')->select();

		foreach ($result as $key => $v) {
			$zNodes[] = ['id' => $v['id'],'pId' => $v['parent_id'],'name' => $v['name'], 'iconOpen' =>'"'.WEB_PATH.'/static/admin/Widget/zTree/css/zTreeStyle/img/diy/diy1_1.png"', 
				'iconClose' => '"'.WEB_PATH.'/static/admin/Widget/zTree/css/zTreeStyle/img/diy/diy1_2.png"'];
		}
		return json_encode($zNodes,JSON_UNESCAPED_UNICODE);
	}

	//接受ajax传过来的 分类的id信息 获得商品列表 
	public function getGoodsList ()
	{
		$goods = new Goods();
		$id = input('get.id');
		$result = $goods->field('goods_id,cat_id,goods_sn,store_count,goods_name,market_price,shop_price,is_recommend,is_on_sale,is_hot,is_new,on_time,last_update')->where('cat_id='.$id )->order('sort','asc')->paginate(25);
		$user = Goods::find(1);
		return json($result);
	}

	//展示板块列表
	public function catelist ()
	{
		return $this->fetch();
	}

	public function getCateList ()
	{
		$cateid = input('get.id');
		$cateinfo = Db::name('goods_cate')->where('id='.$cateid)->whereOr('parent_id='.$cateid)->select();
		return json($cateinfo);
	}

	public function editcate ()
	{
		$cateid = input('get.id');
		$cate = Db::name('goods_cate')->where('id='.$cateid)->find();

		$this->assign(['cate' => $cate]);
		return $this->fetch();
	}

	//接受ajax传来的上传品牌图片的消息
	public function cateupload ()
	{
		$file = request()->file('Filedata');
		$cateid = request()->param()['brandid'];
		$info = $file->validate(['ext'=>'jpeg,jpg,png,gif'])->move('./static/uploads/cate/'.$cateid,'');
	    if($info){
	        $logo_img = WEB_PATH.'/static/uploads/cate/'.$cateid.'/'.$info->getSaveName();
	    }else{
	        return $file->getError();die;
	    }
	    //查询到数据表中的logo信息 然后删掉 注意 http://后面加上路径 是不能操作的
	    $oldImgUrl = Db::name('goods_cate')->find($cateid)['image'];
	    if ($oldImgUrl != $logo_img && file_exists(str_replace(WEB_PATH, '',$oldImgUrl))) {
	    	unlink('.'.str_replace(WEB_PATH, '',$oldImgUrl));
	    }
	    
		$result = Db::name('goods_cate')->where('id',$cateid)->update(['image' => htmlspecialchars($logo_img)]);

		return json(['path' => $logo_img,
		'width' => '200',
		'height' => '100']);
	}

	public function dealEditCate() {

		$data = input('post.');
		if($data['is_hot'] == 1){
			$data['is_hot'] = 1;
		} else {
			$data['is_hot'] = 0;
		}

		$result = Db::name('goods_cate')->update($data,['id' => $data['id']]);
		if ($result == 1 || $result == 0) {
			return 1;
		} else {
			return 0;
		}
	}


	public function addCate ()
	{
		$cate = Db::name('goods_cate')->field('id,name')->order('sort_order')->where('level=1')->select();
		$this->assign(['cate' => $cate]);
		return $this->fetch();
	}

	public function getCateById ()
	{
		$cateid = input('post.id');
		$reuslt = Db::name('goods_cate')->where('parent_id='.$cateid)->select();
		return json($reuslt);
	}

	//添加商品的图片请求然后保存缓存到文件夹
	public function addCateUpload ()
	{
		//使用变量接受file文件信息 和 post数据
		$file = request()->file('Filedata');
		$goodsid = session('admin_uid');
		//若传递过来的文件不为空则不会去修改这个文件的路径
		//验证文件的类型是不是指定的格式 然后移动文件到拼接的目录 文件的名字是原来的名字
		$info = $file->move('./static/uploads/cate/temp/'.$goodsid,'temp_goods');
	    if($info){
	        $logo_img = WEB_PATH.'/static/uploads/cate/temp/'.$goodsid.'/temp_goods.'.$info->getExtension();
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
	public function dealAddCate ()
	{
		$filepath = './static/uploads/cate/temp/'.session('admin_uid');
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

		$data = input('post.');
		if($data['is_hot'] == 1){
			$data['is_hot'] = 1;
		} else {
			$data['is_hot'] = 0;
		}
		if ($data['yiji'] == 0) {
			$data['parent_id'] = 0;
			$data['level'] = 1;
			$parent_id_path = '0_';
		} elseif(empty($data['erji'])) {
			$data['parent_id'] = $data['yiji'];
			$data['level'] = 2;
			$parent_id_path = '0_'.$data['parent_id'].'_';
		} else {
			$data['parent_id'] = $data['erji'];
			$data['level'] = 3;
			$yiji = $data['yiji'];
			$parent_id_path = '0_'.$yiji.'_'.$data['parent_id'];
			unset($data['erji']);
		}
		unset($data['yiji']);
		$result = Db::name('goods_cate')->insertGetId($data);
		
		$oldname = './static/uploads/cate/temp/'.session('admin_uid').'/'.$imgpath;
		$newname = './static/uploads/cate/'.$result;
		
		if (!is_dir($newname)) {
			mkdir($newname);
		}
		rename($oldname, $newname.'/'.$imgpath);
		$resultimg = Db::name('goods_cate')->where(['id' => $result])->update(['image' => WEB_PATH.substr($newname,1).'/'.$imgpath,'parent_id_path' => $parent_id_path.$result]);
		
		if ($resultimg == 0 || $resultimg == 1) {
			return 1;
		} else {
			return '新增分类图片失败，请重新添加';die;
		}

	}


}
 
