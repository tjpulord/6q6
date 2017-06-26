<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 模块儿基类
 */
class ActiveController extends Controller
{
	
	function __construct()
	{
		parent::__construct();	
		$this->writeLog();	
	}	
	/**
	 * @cc 记录系统日志
	 */
	private function  writeLog()
	{
		//记录登录日志
		$m_params = array
		(
			'module'=>'Admin',
			'controller'=>CONTROLLER_NAME,
			'action' =>ACTION_NAME,	
			'querystring' => __SELF__ ,
			'ip'=> get_client_ip(),
			'uid'=>cookie('admin_id'),
			'username'=> (cookie('admin') =='' ? '游客':cookie('admin')),
			'create_time' =>date('Y-m-d H:i:s',time()),
		);
		if(ACTION_NAME !='getclientstatus')
		{
			M('log')->add($m_params);
		}				
	}
}