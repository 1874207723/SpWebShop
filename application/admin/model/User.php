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


/**
* 用户账号类
*/
namespace app\admin\model;
use think\Model;
use traits\model\SoftDelete;

class User extends Model
{
	use SoftDelete;
	protected $deleteTime = 'delete_time';

	protected $type = [
        'reg_time'  =>  'timestamp:Y/m/d H:i:s',
        'last_login'  =>  'timestamp:Y/m/d H:i:s',
        'delete_time'  =>  'timestamp:Y/m/d H:i:s'
    ];

	protected $auto = [
		//'last_update'
	]; 

	/*protected function setLastUpdateAttr()
    {
        return time();
    }*/

    public function getSexxAttr($value,$data)
    {
        $status = [0=>'保密',1=>'男',2=>'女'];
        return $status[$data['sex']];
    }
    public function getLastIpAttr($value)
    {
        return long2ip($value);
    }


}