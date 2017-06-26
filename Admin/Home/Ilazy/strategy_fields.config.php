<?php
	return array(
			'table' => 'strategy_fields',
			'contrl'	=> array(
					'add'	=> array(
							'lable'		=> '添加新字段',
							'ctrl'		=> 'StrategyField/itemview',
							'icon'		=> '&#xe600;',
						),
				),
			'listctrl'	=> array(
					'edit'	=> array(
							'lable'		=> '编辑',
							'ctrl'		=> 'StrategyField/itemview',
							'icon'		=> '&#xe6df;',
							'js'		=> 'article_edit',
						),
					'del'	=> array(
							'lable'		=> '删除',
							'ctrl'		=> 'StrategyField/itemdel',
							'icon'		=> '&#xe6e2;',
							'js'		=> 'article_del',
						),
				),
			'front'	=> array(
				'name'	=>  array(
					'type'       => 'text',										//类型
					'name'       => '名称',										//名称
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
					'width'      => '',											//宽度
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
				'field'	=>  array(
					'type'       => 'text',										//类型
					'name'       => '数据库字段名',								//名称
					'tip'        => '',											//提示
					'value'      => '',											//默认值
					'mustbe'     => true,										//是否必填字段
					'display'    => true,										//前台显示
					'listshow'   => true,										//前台列表显示
				),
				'type'	=>  array(
					'type'       => 'text',										//类型
					'name'       => '字段类型',									//名称
					'tip'        => '',											//提示
					'mustbe'     => true,										//是否必填字段
					'value'      => '',											//默认值
					'display'    => true,										//前台显示
					'listshow'   => true,										//前台列表显示
				),

				'note'	=>  array(
					'type'       => 'text',										//类型
					'name'       => '备注',										//名称
					'tip'        => '',											//提示
					'mustbe'     => false,										//是否必填字段
					'value'      => '',											//默认值
					'display'    => true,										//前台显示
					'listshow'   => false,										//前台列表显示
				),
			),
		);
 ?>