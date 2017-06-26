<?php 

/**
 * 用户的操作类，类库积累的Version：2016-5-13版
 */
namespace iLazy;
// use iLazy\Base;

/**
 * 用户的通用类库
 */
class User extends Base{

	/**
	 * 初始化类库
	 * @param [type] $table 表名字
	 * @param [type] $model 数据模型
	 */
	public function __construct($table='User',$model=array())
	{
		parent::__construct();
		$this->table = $table;
		$this->model = $model;
		$this->db    = M($this->table);
	}


	/**
	 * 用户登陆
	 * @param [type]  $uid    用户名
	 * @param [type]  $pwd    密  码
	 * @param integer $status 账户状态
	 * @param [type]  $log    日志信息
	 */
	public function Signin($uid,$pwd,$status=array(),$log=array())
	{
		if(!$this->empty($uid) || !$this->empty($pwd)){
			return false;
		}
		$sch['username'] = $uid;
		$sch['password'] = $pwd;
		if(!empty($status) && is_array($status)){
			foreach ($status as $k => $v) {
				$sch[$k] = $v;
			}
		}
		$rst = $this->db->where($sch)->find();
		if(!empty($rst)){
			return false;
		}
		return $rst;
	}



}