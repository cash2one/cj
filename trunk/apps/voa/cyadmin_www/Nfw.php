<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
	die('require PHP > 5.3.0 !');
}

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', True);
// 为了兼容Nginx下的U方法
define('__APP__', '');

// 绑定 Action 方法名称
// define('BIND_ACTION', 'execute');
// 绑定模块到当前文件
//define('BIND_MODULE', 'UcRpc');

// 框架目录
define('THINK_PATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/thinkphp/ThinkPHP/');
// 定义应用目录
define('APP_PATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/thinkphp/CyAdmin/');

// 引入ThinkPHP入口文件
require THINK_PATH . 'ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单
