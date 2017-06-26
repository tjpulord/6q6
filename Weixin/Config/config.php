<?php
return array(
	//'配置项'=>'配置值'
	
	//加载扩展配置文件
	'LOAD_EXT_CONFIG'      => 'db_conf, safe_function_conf',
	'APP_AUTOLOAD_LAYER'   => 'Controller,Model',
	// 'AUTOLOAD_NAMESPACE'   => array(    
	// 	'Lib'     => APP_PATH.'Class',
	// ),
	//开启路由功能
	'URL_ROUTER_ON'        => true,
	'URL_MODEL'            => 2,
	// 'URL_ROUTE_RULES'=>array(
	//     'Product'               => '',
	// ),
	
	//当URL_CASE_INSENSITIVE设置为true的时候表示URL地址不区分大小写，这个也是框架在部署模式下面的默认设置
	'URL_CASE_INSENSITIVE' => true,
	
	//用于开启数据库调试模式，开启后即可记录SQL日志
	'DB_DEBUG'             => true,
	
	//保存路径
	'COOKIE_PATH'          => '/' , 
	'COOKIE_PREFIX'        => 'hx_', //cookie的前缀
	'COOKIE_EXPIRE'        => 36000, //cookie的生存时间
	
	//是否开启session
	'SESSION_AUTO_START'   => true, 
	
	//是否开启COOKIE
	'COOKIE_AUTO_START'    => true,
	
	// 显示页面Trace信息
	'SHOW_PAGE_TRACE'      => false,
	
	// 开启布局模板功能，默认不开启
	'LAYOUT_ON'            => false, 
	
	// 可以不配置，默认为layout
	'LAYOUT_NAME'          => '',		
	
	'TMPL_L_DELIM'         => '{{',            // 模板引擎普通标签开始标记
	
	'TMPL_R_DELIM'         => '}}',            // 模板引擎普通标签结束标记		
	
	'HTML_FILE_SUFFIX'     => '',
	'TOKEN'                => 'baodinghuixinbaoxian', //免登陆识别标签
	'UPLOAD_PATH'		   => $_SERVER["ROOT_DOCUMENT"],

	'ILAZY_MODEL_PATH'	   => 'Ilazy',			//默认模版配置器
	'USERINFO'			   => array(),				//用户信息
);