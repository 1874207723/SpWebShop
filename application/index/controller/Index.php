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
use think\Request;
use think\Db;
class Index extends Base
{

    public function index()
    {
    	//查询板块分类，1,2,3级。
    	$product = [];
    	$hotProduct = [];
        $level1 = Db::table('qb_goods_cate')->field('id,name')->where('level', 1)->order('sort_order desc')->limit(10)->select();

        foreach ($level1 as $leVal) {
        	$level1Name[$leVal['id']] = $leVal['name'];
    	}

        foreach($level1 as $value) {

            $data = Db::table('qb_goods_cate')->field('id')->where('parent_id', $value['id'])->select();
            //如果二级目录存在
            if(!empty($data)) {
            	foreach($data as $value2) {
	                $data2 = Db::table('qb_goods_cate')->field('id')->where('parent_id', $value2['id'])->limit(5)->select();
	                //如果三级目录存在
	                if(!empty($data2)) {
	                	foreach($data2 as $value3) {
	                		$data3 = Db::table('qb_goods')->field('goods_name,goods_id,original_img')->where('cat_id', $value3['id'])->where('is_recommend', 1)->order('sort desc')->limit(19)->select();
	                		$data4 = Db::table('qb_goods')->field('goods_name,shop_price,goods_id,original_img')->where('cat_id', $value3['id'])->where('is_hot', 1)->order('sort desc')->limit(19)->select();
	                		//如果三级目录下商品存在
	                		if(!empty($data3)) {
	                			foreach($data3 as $value4) {
	                				$product[$value['id']][] = $value4;
	                			}
	                		}

	                		if(!empty($data4)) {

	                			foreach($data4 as $value5) {
	                				//dump($value['id']);
	                				$hotProduct[$value['id']][] = $value5;
	                			}
	                		}
	                	}
	                }
            	}
            }
        }

        //限制每个目录下的商品数量
        foreach ($product as $pdtKey => $pdtValue) {
        	$product[$pdtKey][0] = array_slice($pdtValue,0,5);
        	$product[$pdtKey][1] = array_slice($pdtValue,5,5);
        	$product[$pdtKey][2] = array_slice($pdtValue,10,5);
        	$product[$pdtKey][3] = array_slice($pdtValue,15,4);
        }

        //dump($hotProduct);
        //查询前4个目录的热销产品
        $i = 0;
        foreach ($hotProduct as $keyh => $valh) {
        	$hotProduct2[$keyh] = $valh;
        	$i++;
        	if ($i == 4) {
        		break;
        	}
        }


        //$hotProduct = array_slice($hotProduct,0,4);
        //dump($hotProduct2);
        foreach ($hotProduct2 as $htKey=>$htValue) {
        	$hotProduct2[$htKey][0] = array_slice($htValue,0,3);
        	$hotProduct2[$htKey][1] = array_slice($htValue,3,3);
        	$hotProduct2[$htKey][2] = array_slice($htValue,6,6);
        }
        //dump($hotProduct);

        //热销种类
        $hotData = Db::table('qb_goods')->field('goods_id,goods_name,original_img')->order('sales_sum desc')->limit(8)->select();

        //查新最新商品
        $lastData = Db::table('qb_goods')->field('goods_id,goods_name,original_img')->order('on_time desc')->limit(4)->select();

       	//查询最新商城公告
       	$lastArtical = Db::table('qb_article')->field('article_id,title')->where('is_open', 1)->limit(4)->order('publish_time desc')->select();

       	//查询推荐品牌
       	$lastBrand = Db::table('qb_brand')->field('id,logo,name')->where('is_hot', 1)->limit(3)->select();

       	//查询头部板块推荐。
        $hotCate = Db::table('qb_goods_cate')->where('is_hot', 1)->where('level', 'in', '2,3')->limit(8)->select();

        //得到总目录下的所有商品，$product;
        $this->assign([
        	'product' => $product,
        	'level1' => $level1,
        	'hotProduct' => $hotProduct2,
        	'level1Name' => $level1Name,
        	'hotData' => $hotData,
        	'lastData' => $lastData,
        	'lastArtical' => $lastArtical,
        	'lastBrand' => $lastBrand,
        	'hotCate' => $hotCate
        	]);


        return $this->fetch();
    }


    //接受到用户传过来的数据进行判断
  	public function chat()
  	{

		$request = Request::instance();
		if (!empty($_POST['username'])) {
			$username = $_POST['username'];
			$bognum = $_POST['jobnum'];
			$uid = $_POST['uid'];
			/*$chatinfo = Db::query("select * from ccont where (username='$username' and touid= $bognum) or (touid=$uid and uid=$bognum) order by cid asc");*/
			$chatinfo = Db::table('ccont')->where("(username='$username' and touid= $bognum) or (touid=$uid and uid=$bognum)")->order('cid asc')->select();

			//$isread = Db::exec("update ccont set isread=1 where uid=$bognum and touid=$uid");
			$isread = Db::table('ccont')->where("uid=$bognum and touid=$uid")->update(['isread' => 1]);

			$result = $chatinfo;

			//$userinfo = Db::query("select username,isonline from adminuser where jobnum=$bognum")->fetch();
			$userinfo = Db::table('adminuser')->where("jobnum=$bognum")->find();

			$result['servername'] = $userinfo['username'];
			$result['isonline'] = $userinfo['isonline'];
			if ($isread>0) {$result['isread'] = 1;} else {$result['isread'] = 0;}
			echo json_encode($result);

		} elseif (!empty($request->param('insert'))) {
			$username = $_POST['name'];
			$bognum = $_POST['jobnum'];
			$uid = $_POST['uid'];
			$chat = $_POST['chat'];
			//$chatinfo = Db::exec("insert into ccont (username,uid,chat,touid) values ('$username',$uid,'$chat',$bognum)");
			$chatinfo =Db::table('ccont')->insert(['username' => $username,'uid' => $uid,'chat' => $chat,'touid' => $bognum]);

			if ($chatinfo) {
				echo 1;
			}

		} elseif (!empty($request->param('list'))) {
			//$adminlist = Db::query('select * from adminuser where type=1');
			$adminlist = Db::table('adminuser')->where('type=1')->select();
			//$result = $adminlist->fetchAll();
			$result = $adminlist;

			foreach ($result as $key => $value) {
				if ($value['isonline'] == 1) {
					echo '<a href="javascript:;" class="list-group-item" id="online'.$value['jobnum'].'" onclick="showonlie('.$value['jobnum'].')"><img src="'.WEB_PATH.'/static/images/chart/useronline.png">'.$value['username'].'</a> ';
				} else {
					echo '<a href="javascript:;" class="list-group-item" id="online'.$value['jobnum'].'" onclick="showonlie('.$value['jobnum'].')"><img src="'.WEB_PATH.'/static/images/chart/userunline.png">'.$value['username'].'</a> ';
				}
			}

		} elseif (!empty($request->param('new'))) {
			$username = $_POST['name'];
			$uid = $_POST['uid'];
			$adminlist = Db::query('select count(uid) as count,uid from ccont where type=1 and isread=0 and touid='.$uid .' group by uid');

			//$result = $adminlist->fetchAll();
			$result = $adminlist;
			echo json_encode($result);
		}
	}
}

