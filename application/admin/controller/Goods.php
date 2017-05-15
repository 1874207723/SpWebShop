<?php
//  Project name Q-Buy
//  Created by window on 17/5/14.
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
use app\admin\model\Goods as GoodsModel;
class Goods extends Base
{
	//展示修改商品的模板 然后获取指定商品的信息  和 品牌的信息
	public function editGoods ()
	{
		$goodsid = input('get.id');
		$goodsinfo = GoodsModel::get($goodsid);
		$shipping2 = explode(',', $goodsinfo->shipping_area_ids);
		$wuliu = Db::name('shipping')->field('shipping_id as id ,shipping_name as name')->select();
		$cat = Db::name('brand')->field('cat_name,cat_id')->group('cat_name')->select();
		$cate = Db::name('goods_cate')->field('id,name')->order('sort_order')->where('level=1')->select();
		$this->assign(['good' => $goodsinfo,'cat' => $cat,'cate'=>$cate,'wuliu' => $wuliu,'shipping' => $shipping2]);
		return $this->fetch();
	}	

	//展示添加商品的页面的模板
	public function addGoods ()
	{
		$wuliu = Db::name('shipping')->field('shipping_id as id ,shipping_name as name')->select();
		$cat = Db::name('brand')->field('cat_name,cat_id')->group('cat_name')->select();
		$this->assign(['cat' => $cat,'wuliu' => $wuliu]);
		return $this->fetch();
	}



	//【ajax】处理修改后的商品数据
	public function dealEditGoods ()
	{
		$goodsModel = new GoodsModel;
		$goods = input('post.');
		
		if (isset($goods['shipping_area_ids'])) {
			$goods['shipping_area_ids'] = implode(',' , $goods['shipping_area_ids']);
		}
		if (empty($goods['brand_id'])) {
			unset($goods['brand_id']);
		}
		$goods['goods_content'] = htmlspecialchars($goods['goods_content']);
		$result = $goodsModel->save($goods,$goods['goods_id']);
		if ($result == 1 || $result == 0) {
			return 1;
		}
		return 0;
	}

	//【ajax】获取表单数据进行添加商品
	public function dealAddGoods ()
	{
		$filepath = './static/uploads/goods/temp/'.session('admin_uid');
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

		$goodsModel = new GoodsModel;
		$goods = input('post.');
		if (isset($goods['shipping_area_ids'])) {
			$goods['shipping_area_ids'] = implode(',' , $goods['shipping_area_ids']);
		}
		$goods['on_time'] = time();

		$result = $goodsModel->save($goods);
		
		$oldname = './static/uploads/goods/temp/'.session('admin_uid').'/'.$imgpath;
		$newname = './static/uploads/goods/zhu/'.$goodsModel->goods_id;
		if (!is_dir($newname)) {
			mkdir($newname);
		}
		rename($oldname, $newname.'/'.$imgpath);
		$resultimg = $goodsModel->save(['original_img' => WEB_PATH.substr($newname,1).'/'.$imgpath],['goods_id' =>$goodsModel->goods_id]);
		if ($resultimg == 0 || $resultimg == 1) {
			return 1;
		} else {
			return '新增商品图片失败，请重新添加';die;
		}
	}


	//【ajax】接受ajax发来的参数 然后获取板块对应的品牌名称
	public function getBrandByCat ()
	{
		$catname = input('post.id');
		$brand = Db::name('brand')->where('cat_name="'.$catname.'"')->field('name,id')->select();
		return json($brand);		
	}

	//【ajax】接受ajax发来的参数 然后获取板块对应的品牌名称
	public function getCateById ()
	{
		$cateid = input('post.id');
		$brand = Db::name('goods_cate')->where('parent_id="'.$cateid.'"')->field('name,id')->select();
		return json($brand);		
	}

	//接受ajax传过来的action 改变商品的请求动作 然后执行
	public function dealUpdateShop()
	{
		$goods = new GoodsModel();
		$action = input('get.action');
		$goodid = input('get.id');	
		switch ($action) {

			case 'news':
				$goods->save(['is_new' => 1],['goods_id' => $goodid]);
				return '添加新品成功';

			case 'nonews':
				$goods->save(['is_new' => 0],['goods_id' => $goodid]);
				return '取消新品成功';

			case 'comme':
				$goods->save(['is_recommend' => 1],['goods_id' => $goodid]);
				return '添加推荐商品成功';

			case 'nocomme':
				$goods->save(['is_recommend' => 0],['goods_id' => $goodid]);
				return '取消推荐商品成功';

			case 'hot':
				$goods->save(['is_hot' => 1],['goods_id' => $goodid]);
				return '添加热门商品成功';

			case 'nohot':
				$goods->save(['is_hot' => 0],['goods_id' => $goodid]);
				return '取消热门商品成功';

			case 'sale':
				$goods->save(['is_on_sale' => 1,'on_time' => time()],['goods_id' => $goodid]);
				return '上架商品成功';

			case 'nosale':
				$goods->save(['is_on_sale' => 0],['goods_id' => $goodid]);
				return '取消上架商品成功';

			case 'delete':
				$goods->destroy($goodid);
				return '删除商品成功';
		}
	}

	//接受长传的图片
	public function uploader()
	{
		$goodsid = request()->param()['goodsid'];
		//使用变量接受file文件信息 和 post数据
		$file = request()->file('file');
		//若传递过来的文件不为空则不会去修改这个文件的路径
		//验证文件的类型是不是指定的格式 然后移动文件到拼接的目录 文件的名字是原来的名字
		$datestr = date('Ymd');
			$info = $file->validate(['ext'=>'bmp,jpeg,jpg,png,gif'])->move('./static/uploads/goods/zhu/'.$goodsid,'');
		    if($info){
		        $logo_img = WEB_PATH.'/static/uploads/goods/zhu/'.$goodsid.'/'.$info->getSaveName();
		    }else{
		        return $file->getError();die;
		    }
		    //查询到数据表中的logo信息 然后删掉 注意 http://后面加上路径 是不能操作的
		    $oldImgUrl = Db::name('goods')->find($goodsid)['original_img'];
		    if ($oldImgUrl != $logo_img && file_exists(str_replace(WEB_PATH, '',$oldImgUrl))) {
		    	unlink('.'.str_replace(WEB_PATH, '',$oldImgUrl));
		    }
	    
		$result = Db::name('goods')->where('goods_id',$goodsid)->update(['original_img' => htmlspecialchars($logo_img)]);
	    return $result;die;
	}

	//在添加时上传的文件 然后移动到缓存目录下
	public function addTempImg()
	{
		
		$goodsid = session('admin_uid');
		if (!empty(input('post.temp'))) {
			$filepath = './static/uploads/goods/temp/'.$goodsid;
			$dh = opendir($filepath);
			while (($file = readdir($dh)) !== false) {
				if (strstr(basename($file),'temp_goods')) {
					$imgpath = $file;
				}
			}
			closedir($dh);
			$imgpath = WEB_PATH.'/static/uploads/goods/temp/'.$goodsid.'/'.$imgpath;
			return ['img' => $imgpath]; 
			//WEB_PATH.'/static/uploads/goods/temp/'.$goodsid.'/temp_goods.'.$info->getExtension();
		} else {
			//使用变量接受file文件信息 和 post数据
			$file = request()->file('file');
			//若传递过来的文件不为空则不会去修改这个文件的路径
			//验证文件的类型是不是指定的格式 然后移动文件到拼接的目录 文件的名字是原来的名字
			$info = $file->move('./static/uploads/goods/temp/'.$goodsid,'temp_goods');
		    if($info){
		        $logo_img = WEB_PATH.'/static/uploads/goods/temp/'.$goodsid.'/temp_goods.'.$info->getExtension();
		        return $logo_img;
		    }else{
		        return $file->getError();die;
		    }
		}  
	}

	//【ajax】获得图片的主图 
	public function getoriginal() 
	{
		$id = input('post.id');
		$result = Db::name('goods')->field('original_img')->find($id)['original_img'];
		return json(['img' => $result]);
	}

}
 
