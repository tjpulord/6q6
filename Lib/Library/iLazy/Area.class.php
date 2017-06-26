<?php 

/**
 * 地区@省市县联动菜单
 * @version  1.0.0.1
 * @author   peterfzh peterfzh@126.com
 */
namespace iLazy;

/**
 * 用户的通用类库
 */
class Area extends Base{

	/**
	 * 初始化类库
	 * @param [type] $table 表名字
	 * @param [type] $model 数据模型
	 */
	public function __construct($table='Areas',$model=array())
	{
		parent::__construct();
		$this->table = $table;
		$this->model = $model;
		$this->db    = M($this->table);
	}

	/**
	 * 获取菜单列表
	 * @param  integer $pid [description]
	 * @return [type]       [description]
	 */
	function getList($pid=0)
	{
		if($pid==""){$pid=0;}
		$rst = $this->db->where('parent_id=%d',$pid)->order('area_id asc')->select();
		return $rst;
	}

	/**
	 * 三级JS菜单
	 * @param string $value [description]
	 */
	function getAreaJs()
	{
		# code...
	}

	/**
	 * 获取所属地区
	 * @param  integer $pid [description]
	 * @return [type]       [description]
	 */
	function getArea($pid=0,$link="")
	{
		$rtn = "";
		if($pid==0){
			$rtn = "<a href='".U($link)."'>所有</a>";
		}
		else
		{
			$rst = $this->db->where('parent_id=%d',$pid)->order('area_id asc')->find();

		}
		return $rtn;
	}

	/**
	 * 显示所有菜单
	 * @return [type] [description]
	 */
	public function show()
	{
		$rtn = array();
		$rst = $this->db->where('parent_id=0')->order('sort asc')->select();
		foreach ($rst as $k => $v) {
			$new         = array();
			$list        = $this->db->where('parent_id=%d',$v['area_id'])->order('sort asc')->select();
			foreach ($v as $kk => $vv) 
			{
				$new[$kk]       = $vv;
			}
			if(!empty($list))
			{
				$new['list'] = $list;
			}
			$rtn[] = $new;
		}
		return $rtn;
	}

	/**
	 * 多条数据保存
	 * @param  [type] $array [description]
	 * @return [type]        [description]
	 */
	public function mulisave($array,$db='Proxy')
	{
		$this->db = M($db);
		$rtn = 0;
		if(is_array($array) && !empty($array))
		{
			foreach ($array as $k => $v) {
				$rst = $this->db->where('id=%d',$k)->data(array('sorder'=>$v))->save();
				if($rst){
					$rtn++;
				}
			}
		}
		return $rtn;
	}

	/**
	 * 保存菜单
	 * @param  [type] $data 数据集
	 * @param  string $key  数据索引
	 * @return [type]       [description]
	 */
	public function save($data,$key='id',$db='Areas')
	{
		$this->db = M($db);
		if(empty($data)){
			return false;
		}
		$keyw = array_key_exists($key, $data) ? $data[$key] : "";
		$keys = !empty($key) ? $key : "";
		
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
	 * 获取某一个菜单信息
	 * @param string $value [description]
	 */
	public function find($id='')
	{
		return $this->db->find($id);
	}

	/**
	 * 删除指定的
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function del($id,$db='Areas')
	{
		$this->db = M($db);
		if(empty($id)){
			return false;
		}
		$rst = $this->db->delete($id);
		return true;
	}



}