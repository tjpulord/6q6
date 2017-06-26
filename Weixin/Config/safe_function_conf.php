<?php
return array(
	//私有保护函数
	'SAFE_FUNS'      => array(
			'__construct',							//初始化函数
			'__set',								//魔法术写法
			'getJson',								//数组json格式化
			'__R',									//正确跳转返回值
			'setjson',								//设置json数据
			'getAreaJs',							//获取地址选项为：公共函数
			'datafill',								//数据填充
			'getAdminId',							//获取管理员id
			'getAdminInfo',							//获取管理员信息
			'admin_statuc',							//获取管理员状态
			'list_controller',						//获取控制列表
			'getDes',								//获取方法描述
			'_listview',							//列表
			'_detailedinfoToJson',					//详细
			'_itemedit',							//编辑
			'_itemedit_act',						//保存
			'_del_act',								//删除
			'_update_act',							//上传
			'_view',								//单条数据显示
		),
	'SAFE_CLASS'	=> array(
			'.',									//当前目录
			'..',									//父辈目录
			'.svn',									//svn
			'index.html',							//静态文件
			'BaseController.class.php',				//基础类包
			'ActiveController.class.php',			//激活父辈类库
			'IModelController.class.php',			//数据模型类
			'InsuranceController.class.php',		//控制器
			'ICommonController.class.php',			//公用数据库操作文件
			'IndexController.class.php',			//公共入口文件
			'WeixinController.class.php',			//微信控制器类库
			'ExcelController.class.php',			//excel上传到处借口
			'ProxypolicyController.class.php',		//汇总保单管理系统
			'RobotController.class.php',			//机器人管理
			'UserController.class.php',				//共有用户入口类
			'YanZhaoController.class.php',			//燕赵数据控制区
			'ZhongGuoRMController.class.php',		//中国人保
			'ZhongHuaController.class.php',			//中华保险
		),
	'FTONT_STATUS'	=> array(
			'0'	=> '√',
			'1'	=> '×',
		),
	
);