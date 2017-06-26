<?php 

/**
 * 监控系统的操作类
 * @version  汇新版本
 * @author   peterfzh peterfzh@126.com
 */
namespace iLazy;

/**
 * 用户的通用类库
 */
class Monitor extends Base{

	/**
	 * 初始化类库
	 * @param [type] $table 表名字
	 * @param [type] $model 数据模型
	 */
	public function __construct($table='Monitor',$model=array())
	{
		parent::__construct();
		$this->table = $table;
		$this->model = $model;
		$this->db    = M($this->table);
	}


	/**
	 * 显示监控状态
	 * @return [type] [description]
	 */
	public function show()
	{
		$rst = $this->db->select();
		return $rst;
	}

	/**
	 * 上报监控状态
	 * @param  [type] $key  索引值
	 * @param  [type] $data 数据
	 * @return [type]       返回状态
	 */
	public function resport($data,$key='mname')
	{
		if(empty($data)){
			return false;
		}
		$keyw = array_key_exists($key, $data) ? $data[$key] : "";
		$keys = !empty($key) ? $key : "";
		if(!array_key_exists("status",$data))
		{
			$data['status'] = "lost";
		}
		else
		{
			switch ($data['status']) {
				case 'False':
					$data['status'] = 'free';
					break;
				case 'True':
					$data['status'] = 'busy';
					break;
			}
		}
		$data['lasttime'] = time();
		$data['lastip']   = $this->get_real_ip();
		if(array_key_exists("pro", $data))
		{
			$data['taskcount'] = $data['pro'];
		}
		if($keys){
			$sch = $this->db->where("$keys='%s'",$keyw)->find();
			if($sch){
				$rst = $this->db->where("$keys='%s'",$keyw)->save($data);
			}
			else
			{
				$rst = $this->db->data($data)->add();
			}
		}
		else{
			$rst = $this->db->data($data)->add();
		}
		return true;
	}

	/**
	 * 删除指定的监控信息
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function del($id)
	{
		if(!empty($id)){
			return false;
		}
		$rst = $this->db->where($id)->delete();
		return true;
	}



}