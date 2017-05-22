<?php
/**
 * @Author: Marte
 * @Date:   2017-05-22 09:05:30
 * @Last Modified by:   Marte
 * @Last Modified time: 2017-05-22 09:10:40
 */
namespace app\index\controller;
use think\Db;

class order extends Base
{
    public function checkorder()
    {
        return $this->fetch();
    }
}