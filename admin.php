<?php
error_reporting ( E_ERROR );
date_default_timezone_set ( 'PRC' );
if (version_compare ( PHP_VERSION, '5.3.0', '<' ))
	die ( 'require PHP > 5.3.0 !' );

/**
 * 微信接入验证
 * 在入口进行验证而不是放到框架里验证，主要是解决验证URL超时的问题
 */
if (! empty ( $_GET ['echostr'] ) && ! empty ( $_GET ["signature"] ) && ! empty ( $_GET ["nonce"] )) {
	$signature = $_GET ["signature"];
	$timestamp = $_GET ["timestamp"];
	$nonce = $_GET ["nonce"];
	$token = $_GET ["tkn"];
	
	$tmpArr = array (
			$token,
			$timestamp,
			$nonce 
	);
	sort ( $tmpArr, SORT_STRING );
	$tmpStr = sha1 ( implode ( $tmpArr ) );
	if ($tmpStr == $signature) {
		echo $_GET ["echostr"];
	}
	exit ();
}
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
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
//设置网页编码格式 UTF-8 or gb2312
header("Content-type: text/html;charset=utf-8");
// 检测PHP环境

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);

//应用名称
define('APP_NAME','Admin');

// 定义应用目录
define('APP_PATH',dirname(__FILE__).'/Admin/');

/**
 * 日期和缓存文件目录
 * @var unknown
 */
define("CACHE_PATH", "./Cache/Admin/");

define("RUNTIME_PATH", "./Cache/Admin/Runtime/");

/**
 * 模板目录
 * @var unknown
 */
define("TEMP_PATH","./Admin/View/");

/**
 * 配置目录
 * @var unknown
 */
define("CONF_PATH","./Admin/Config/");

// define("VENDOR_PATH",'./Extends/');

//防止双重转义 post get cookie
if (get_magic_quotes_gpc())
{
	function stripslashes_deep($value){
		$value = is_array($value) ?
		array_map('stripslashes_deep', $value) :
		stripslashes($value);
		return $value;
	}
	$_POST   = array_map('stripslashes_deep', $_POST);
	$_GET    = array_map('stripslashes_deep', $_GET);
	$_COOKIE = array_map('stripslashes_deep', $_COOKIE);
}



//引入ThinkPHP入口文件
require './Lib/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单