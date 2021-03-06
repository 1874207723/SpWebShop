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

namespace app\mobile\controller;
use think\Request;
use think\Db;
class Index extends Base
{

    public function index()
    {
    	$newgoods = Db::name('goods')->where('is_new=1 and is_on_sale')->order('sort ,goods_id')->limit(6)->select();
    	$commegoods = Db::name('goods')->where('is_recommend=1 and is_on_sale')->order('sort ,goods_id')->limit(4)->select();
    	$hotgoods = Db::name('goods')->where('is_hot=1 and is_on_sale')->order('sort ,goods_id')->limit(4)->select();
    	$round = Db::name('goods')->where('is_on_sale=1')->order('rand()')->limit(3)->select();
        $this->assign(['round' => $round,'newgoods' => $newgoods,'commegoods' => $commegoods,'hotgoods' => $hotgoods]);
        return $this->fetch();
    }



}

