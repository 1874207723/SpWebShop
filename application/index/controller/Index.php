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

