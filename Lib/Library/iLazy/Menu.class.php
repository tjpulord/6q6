<?php 

/**
 * 自定义菜单管理
 * @version  1.0.0.1
 * @author   peterfzh peterfzh@126.com
 */
namespace iLazy;

/**
 * 用户的通用类库
 */
class Menu extends Base
{

	public $g_userinfo = array(); //用户信息
	/**
	 * 初始化类库
	 * @param [type] $table 表名字
	 * @param [type] $model 数据模型
	 */
	public function __construct($table='SystemMenu',$model=array())
	{
		parent::__construct();
		$this->table = $table;
		$this->model = $model;		    
		$this->db    = M($this->table);
	}


	/**
	 * 显示所有菜单
	 * @return [type] [description]
	 */
	public function show()
	{
		$rtn = array();
		$rst = $this->db->where('parentid=0 and isuse=1')->order('sorder asc')->select();
		foreach ($rst as $k => $v) {
			$new         = array();
			$list        = $this->db->where('parentid=%d and isuse=1',$v['id'])->order('sorder asc')->select();
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
	 * 根据用户权限显示用户所有菜单
	 * @return multitype:multitype:unknown
	 */
	public function ShowUserMenu()
	{
		$m_Menu = json_decode($this->g_userinfo['groupinfo']['flag'], true);
		$m_use_ctrl = array();
		foreach ($m_Menu as $key=>$value)
		{
			$tmp = explode("_", $key);
			$m_use_ctrl[] = strtolower($tmp[0]."/".$tmp[1]);
		}
		$rtn = array();
		$rst = $this->db->where('parentid=0 and isuse=1')->order('sorder asc')->select();
		foreach ($rst as $k => $v) 
		{
			$new         = array();
			$list        = $this->db->where('parentid=%d and isuse=1',$v['id'])->order('sorder asc')->select();			
			if(!empty($list))
			{			
				foreach ($list as $kkk=>$vvv)
				{
					if(!empty($vvv['url']) && in_array(strtolower($vvv['url']), $m_use_ctrl))	
					{						
						foreach ($v as $kk => $vv)
						{
							$new[$kk]       = $vv;
						}
						$new['list'][] = $vvv;
					}				
				}
			}
			if(!empty($new))
			{
				$rtn[] = $new;
			}
		 }
		return $rtn;
	}
	/**
	 * 多条数据保存
	 * @param  [type] $array [description]
	 * @return [type]        [description]
	 */
	public function mulisave($array)
	{
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
	public function save($data,$key='id')
	{
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
	public function del($id)
	{
		if(empty($id)){
			return false;
		}
		$rst = $this->db->delete($id);
		return true;
	}



}