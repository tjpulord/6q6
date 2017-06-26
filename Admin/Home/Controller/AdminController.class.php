<?php
namespace Home\Controller;
use Think\Controller;
use Home\Controller\IModelController as IModel;

/**
 * @cc 管理员管理模块儿
 */
class AdminController extends BaseController
{
	private $IM =null;
	private $m_db = "user";

	function __construct()
	{
		parent::__construct();
		$m_rest = M('grade_group')->select();
		$this->assign("grade_group",$m_rest);
		$this->IM = M($this->m_db);
	}
	public function listview()
	{
		$this->imodel = new IModel($this->m_db);
		$this->imodel->order = "id desc";
		$this->imodel->params = $this->g_params;
		$this->imodel->pagesize = $count;
		$this->imodel->datafill();
	}
	/**
	 * @编辑列表
	 * @return [type] [description]
	 */
	function itemedit()
	{
		$model_if =  $this->IM
			->where(array('id'=>$this->g_params['id']))
			->find();
		$this->assign('datas',$model_if);
		$this->display();
	}
	/**
	 *@列表编辑保存
	 * @return [type] [description]
	 */
	function itemedit_act($check_key=null, $url=null, $msg=null)
	{
		$param    = array_merge($_POST,$_GET);
		if($param['password'])
		{
			$param['password']   = md5($param['password']);
		}else
		{
			$param['password']   = md5('888888');
		}
		if(array_key_exists("id", $param) && $param['id'])
		{
			$param['update_time']  = date('Y-m-d H:i:s',time());
			$result = $this->IM
				->where('id=%d',$param['id'])
				->save($param);
		}
		else
		{
			if(!empty($check_key))
			{
				$result = $this->IM
					->where($check_key)
					->find();
				if(count($result)>0)
				{
					$this->error($msg, $url,10);
				}
			}
			$param['update_time']  = date('Y-m-d H:i:s',time());
			$param['create_time']  = date('Y-m-d H:i:s',time());
			$result = $this->IM
			->data($param)
			->add();
		}
		$this->display();
	}
	/**
	 * @列表删除保存
	 * @return [type] [description]
	 */
	function del_act()
	{
		$param    = $this->g_params;
		if(array_key_exists("id", $param) && $param['id'])
		{
			$result = $this->IM
				->where('id=%d',$param['id'])
				->delete();
			echo $this->setjson(2000,'success');
		}else
		{
			echo $this->setjson(4000,'faild');
		}
	}
}
