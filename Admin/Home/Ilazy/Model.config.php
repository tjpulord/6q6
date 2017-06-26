<?php
	return array(
		'table'	=> 'model',
		'order' => array('sorder'=>'desc'),
		'contrl'	=> array(
				'add'	=> array(
						'lable'		=> '添加数据模型',
						'ctrl'		=> 'model/itemview',
						'icon'		=> '&#xe600;',
						'js'		=> 'article_add'
					),
				'quick'	=> array(
						'lable'		=> '一键生成模板类',
						'ctrl'		=> '',
						'icon'		=> '&#xe600;',
						'js'		=> 'onekey'
					),
			),
		'listctrl'	=> array(
				'modify'	=> array(
						'lable'		=> '字段管理',
						'ctrl'		=> 'ModelField/listview',
						'icon'		=> '&#xe63c;',
						'js'		=> 'jump_other',
					),
				'edit'	=> array(
						'lable'		=> '编辑',
						'ctrl'		=> 'model/itemview',
						'icon'		=> '&#xe6df;',
						'js'		=> 'article_edit',
					),
				'del'	=> array(
						'lable'		=> '删除',
						'ctrl'		=> 'model/itemdel',
						'icon'		=> '&#xe6e2;',
						'js'		=> 'article_del',
					),
			),
		'listtitle'	=> '<i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 系统设置管理 <span class="c-gray en">&gt;</span> 数据模型管理',
		'front'	=> array(
			'id'	=>  array(
				'type'       => 'text',										//类型
				'name'       => 'ID',
				'listshow'   => true,										//前台列表显示
				),
			'name'	=>  array(
				'type'       => 'text',										//类型
				'name'       => '模型名称',									//名称
				'tip'        => '',											//提示
				'value'      => '',											//默认值
				'password'   => false,										//是否为密码
				'length'     => 50,											//长度
				'css'        => 'input-text',								//css样式
				'range'      => array(1,50),								//数值范围
				'mustbe'     => true,										//是否必填字段
				'search'     => true,										//是否为搜索功能
				'display'    => true,										//前台显示
				'listshow'   => true,										//前台列表显示
				'match'      => '',											//正则验证
				'width'      => '200px',									//宽度
				'height'     => '',											//高度
				'notpost'    => '',											//错误提示
				'editor'     => '',											//编辑器样式 {Namol,Short}
				'options'    => array(),									//选项内容
				'optionout'  => 'name,value',								//输出格式值还是名称
				'optiontype' => 'select,radio,checkbox',					//选项类型
				'imgsize'    => 20,											//上传大小 单位：M
				'imgtype'    => 'jpg|gif|jpeg|png|bmp',						//图片格式
				'imgmuitl'   => true,										//多图上传
				'imgcount'   => 10,											//附件个数
				'intsize'    => 2,											//小数后几位
				'datetype'   => 'Y-m-d H:i:s',								//日期格式
				'areapanel'  => 'province|city|country',					//省市县联动
				'smsenable'  => false,										//短信验证码
				'union'      => array('ins_admin'=>'username'),				//关联的那个表的字段
				'ispublic'   => false,										//是否为公用字段
				'publicstr'  => '<input type=text>',						//公用字段内容
			),
			'description'	=> array(
					'name'     => '模型描述',
					'listshow' => true,
					'display'  => true,
					'css'      => 'input-text',
					'mustbe'   => true,
				),
			'tablename'	=> array(
					'name'     => '数据表名',
					'listshow' => true,
					'display'  => true,
					'mustbe'     => true,
					'css'      => 'input-text',
				),
			'disabled'  => array(
					'type'       => 'select',
					'name'       => '当前状态',
					'listshow'   => true,
					'display'    => true,
					'mustbe'     => true,
					'css'        => 'input-text',
					'function'   => array('isfalse','if'=>array(0=>'√',1=>'×')),
					'options'    => array('config','FTONT_STATUS'),
					'optiontype' => 'select',					//选项类型
				),
			'setting'  => array(
					'type'       => 'select',
					'name'       => '是否允许覆盖',
					'listshow'   => true,
					'display'    => true,
					'mustbe'     => true,
					'css'        => 'input-text',
					'function'   => array('isfalse','if'=>array(0=>'√',1=>'×')),
					'options'    => array('config','FTONT_STATUS'),
					'optiontype' => 'select',					//选项类型
				),
		),
	);
 ?>