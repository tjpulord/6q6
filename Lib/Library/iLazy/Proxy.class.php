<?php 

/**
 * 分支结构管理
 * @version  1.0.0.1
 * @author   peterfzh peterfzh@126.com
 */
namespace iLazy;

/**
 * 用户的通用类库
 */
class Proxy extends Base{

	/**
	 * 初始化类库
	 * @param [type] $table 表名字
	 * @param [type] $model 数据模型
	 */
	public function __construct($table='Proxy',$model=array())
	{
		parent::__construct();
		$this->table = $table;
		$this->model = $model;
		$this->db    = M($this->table);
	}

	/**
	 * 分支机构级别
	 * @return [type] [description]
	 */
	public function level_show()
	{
		return $this->_M('ProxyLevel')
					->_getlist()
					->_getData();
	}

	/**
	 * 用户分组显示
	 * @return [type] [description]
	 */
	public function usergroup_page()
	{
		$result =  $this->_M('SalesmanGroup')
						->_set('ispage',true)
						->_set('fields',array('*','(select pro_name from ins_proxy where ins_proxy.id=fzid)'=>'pro_name'))
						->_getlist()
						->_getData();
		return $result;
	}


	/**
	 * 显示所有菜单
	 * @return [type] [description]
	 */
	public function show($db='Proxy',$py=1)
	{
		$this->db = M($db);
		$rst = $this->db->where('is_del=0')->order('pro_name asc')->select();
		$rtn = array();
		foreach ($rst as $key => $value) {
			$arr       = $value;
			if($py)
			{
				$arr['pro_name'] = strtoupper($this->encode($arr['pro_name'])).",".$arr['pro_name'];
			}
			$rtn[$key] = $arr;
		}
		return $rtn;
	}

	/**
	 * 多条数据保存
	 * @param  [type] $array [description]
	 * @return [type]        [description]
	 */
	public function proxyLevelmulisave($array)
	{
		return $this->_M('ProxyLevel')->_data($array)->_sorder();
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
	 * 保存机构级别
	 * @param  [type]  $d   [description]
	 * @param  string  $k   [description]
	 * @param  integer $new [description]
	 * @return [type]       [description]
	 */
	public function leveledit($d,$k='id',$new=0)
	{
		if(!$new)
		{
			$result = $this->_M('ProxyLevel')
				->_data($d)
				->_pri($k)
				->_save();
		}
		else
		{
			$result = $this->_M('ProxyLevel')
				->_data($d)
				->_add();
		}
		return $result;
	}

	/**
	 * 保存业务员信息
	 * @param  [type]  $d   [description]
	 * @param  string  $k   [description]
	 * @param  integer $new [description]
	 * @return [type]       [description]
	 */
	public function salesmanedit($d,$k='id',$new=0)
	{
		if(!$new)
		{
			$result = $this->_M('Salesman')
				->_data($d)
				->_pri($k)
				->_save();
		}
		else
		{
			$result = $this->_M('Salesman')
				->_data($d)
				->_add();
		}
		return $result;
	}

	/**
	 * 保存菜单
	 * @param  [type] $data 数据集
	 * @param  string $key  数据索引
	 * @return [type]       [description]
	 */
	public function save($data,$key='id',$db='Proxy')
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
	public function level_find($id='')
	{
		return $this->_M('ProxyLevel')->_find($id)->_getData();
	}

	/**
	 * 获取某一个菜单信息
	 * @param string $value [description]
	 */
	public function group_find($id='')
	{
		return $this->_M('SalesmanGroup')->_find($id)->_getData();
	}

	/**
	 * @cc 自定义方法
	 * @author peterfzh@126.com
	 */
	function leveldel($id){
		if(empty($id)){
			return false;
		}
		$rst = $this->_M('ProxyLevel')->_del($id);
		return $rst;
	}

	/**
	 * 删除指定的
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function del($id,$db='Proxy')
	{
		$this->db = M($db);
		if(empty($id)){
			return false;
		}
		$rst = $this->db->delete($id);
		return true;
	}



}