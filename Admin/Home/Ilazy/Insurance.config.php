<?php 
	return array(
			'table'	=> 'insurance',
			'contrl'	=> array(
					'add'	=> array(
							'lable'		=> '添加保险公司',
							'ctrl'		=> 'insurance/itemview',
							'icon'		=> '&#xe600;',
						),
				),
			'listctrl'	=> array(
					'edit'	=> array(
							'lable'		=> '编辑',
							'ctrl'		=> 'insurance/itemview',
							'icon'		=> '&#xe6df;',
							'js'		=> 'article_edit',
						),
					'del'	=> array(
							'lable'		=> '删除',
							'ctrl'		=> 'insurance/itemdel',
							'icon'		=> '&#xe6e2;',
							'js'		=> 'article_del',
						),
				),
			'listtitle'	=> '<i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 业务管理系统 <span class="c-gray en">&gt;</span> 保险公司列表',
			'front'	=> array(
				'area'	=>  array(
					'type'       => 'area',										//类型
					'name'       => '所属省份<BR><BR>所属城市<BR><BR>所属区县',									//名称
					'tip'        => '',											//提示
					'value'      => '',											//默认值
					'password'   => false,										//是否为密码
					'length'     => 50,											//长度
					'css'        => 'input-text',								//css样式
					'range'      => array(1,50),								//数值范围
					'mustbe'     => true,										//是否必填字段
					'search'     => false,										//是否为搜索功能
					'display'    => true,										//前台显示
					'listshow'   => false,										//前台列表显示
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
				'ins_name'	=>  array(
					'type'       => 'text',										//类型
					'name'       => '保险公司',									//名称
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
				'shortname'	=> array(
						'name'     => '保险公司简写',
						'listshow' => true,
						'display'  => true,
						'css'      => 'input-text',
						'mustbe'   => true,
					),
				'enname'	=> array(
						'name'     => '英文全称',
						'listshow' => true,
						'display'  => true,
						'mustbe'     => true,
						'css'      => 'input-text',
					),
			),
		);
 ?>