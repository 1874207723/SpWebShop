<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

use think\Db;




function get_place_name ($id)
{
	return Db::name('region')->where('id='.$id)->find()['name'];
}

//给用户推送系统消息
function send_user_message ($msg,$uid)
{
    Db::name('message')->insert([
        'message' => $msg,
        'user_id' => $uid,
        'send_time' => time(),
        'is_read' => 0
        ]);
}

//根据用户的id找到用户的名字
function get_username_by_id ($uid)
{
    if (!empty($uid)) {
        return Db::name('user')->where('user_id='.$uid)->find()['username'];        
    } else {
        return 'null -> common';
    }
    
}

/**
 * 获取用户信息
 * @param $user_id_or_name  用户id 邮箱 手机 第三方id
 * @param int $type  类型 0 user_id查找 1 邮箱查找 2 手机查找 3 第三方唯一标识查找
 * @param string $oauth  第三方来源
 * @return mixed
 */
function get_user_info($user_id_or_name,$type = 0,$oauth=''){
    $map = array();
    if($type == 0)
        $map['user_id'] = $user_id_or_name;
    if($type == 1)
        $map['email'] = $user_id_or_name;
    if($type == 2)
        $map['mobile'] = $user_id_or_name;
    if($type == 3){
        $map['openid'] = $user_id_or_name;
        $map['oauth'] = $oauth;
    }
    if($type == 4){
    	$map['unionid'] = $user_id_or_name;
    	$map['oauth'] = $oauth;
    }
    $user = Db::name('user')->where($map)->find();
    return $user;
}

/**
 * 获取商品信息
 * @param $user_id_or_name  商品id 邮箱 手机 第三方id
 * @param int $type  类型 0 user_id查找 1 邮箱查找 2 手机查找 3 第三方唯一标识查找
 * @param string $oauth  第三方来源
 * @return mixed
 */
function get_goods_info($goods_id,$field = 'goods_name'){

    $user = Db::name('goods')->where('goods_id='.$goods_id)->find()[$field];
    return $user;
}

/* *  商品缩略图 给于标签调用 拿出商品表的 original_img 原始图来裁切出来的
 * @param type $goods_id  商品id
 * @param type $width     生成缩略图的宽度
 * @param type $height    生成缩略图的高度
 */
function goods_thum_images($goods_id,$width,$height){

     if(empty($goods_id))
		 return '';
    //判断缩略图是否存在
    $path = "public/upload/goods/thumb/$goods_id/";
    $goods_thumb_name ="goods_thumb_{$goods_id}_{$width}_{$height}";
  
    // 这个商品 已经生成过这个比例的图片就直接返回了
    if(file_exists($path.$goods_thumb_name.'.jpg'))  return '/'.$path.$goods_thumb_name.'.jpg'; 
    if(file_exists($path.$goods_thumb_name.'.jpeg')) return '/'.$path.$goods_thumb_name.'.jpeg'; 
    if(file_exists($path.$goods_thumb_name.'.gif'))  return '/'.$path.$goods_thumb_name.'.gif'; 
    if(file_exists($path.$goods_thumb_name.'.png'))  return '/'.$path.$goods_thumb_name.'.png'; 
        
    $original_img = M('Goods')->where("goods_id", $goods_id)->getField('original_img');
    if(empty($original_img)) return '';
    
    $original_img = '.'.$original_img; // 相对路径
    if(!file_exists($original_img)) return '';

    //$image = new \think\Image();
    $image = \think\Image::open($original_img);
        
    $goods_thumb_name = $goods_thumb_name. '.'.$image->type();
    //生成缩略图
    if(!is_dir($path)) 
        mkdir($path,0777,true);
    
    //参考文章 http://www.mb5u.com/biancheng/php/php_84533.html  改动参考 http://www.thinkphp.cn/topic/13542.html
    $image->thumb($width, $height,2)->save($path.$goods_thumb_name,NULL,100); //按照原图的比例生成一个最大为$width*$height的缩略图并保存
    
    //图片水印处理
    $water = tpCache('water');
    if($water['is_mark']==1){
    	$imgresource = './'.$path.$goods_thumb_name;
    	if($width>$water['mark_width'] && $height>$water['mark_height']){
    		if($water['mark_type'] == 'img'){
    			$image->open($imgresource)->water(".".$water['mark_img'],$water['sel'],$water['mark_degree'])->save($imgresource);
    		}else{
    		    //检查字体文件是否存在
    			if(file_exists('./zhjt.ttf')){
    				$image->open($imgresource)->text($water['mark_txt'],'./zhjt.ttf',20,'#000000',$water['sel'])->save($imgresource);
    			}
    		}
    	}
    }
    return '/'.$path.$goods_thumb_name;
}