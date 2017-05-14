<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    
    'view_replace_str'       => [
        '__PUBLIC__' => rtrim(WEB_PATH,'/').'',  //web_path  => http://www.wntp.com
        '__STATIC__' => rtrim(WEB_PATH,'/').'/static', //web_path  => http://www.wntp.com/static
        '__ADMIN__' => rtrim(WEB_PATH,'/').'/static/admin', //web_path  => http://www.wntp.com/static
    ],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => str_replace('\\','/',THINK_PATH) . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => str_replace('\\','/',THINK_PATH) . 'tpl' . DS . 'dispatch_jump.tpl',
    
];
