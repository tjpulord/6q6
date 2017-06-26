<?php
namespace Home\Controller;
use Think\Controller;
use Home\Controller\IModelController as IModel;

/**
 * 管理员权限管理模块儿
 *
 */
class GradeGroupController extends BaseController
{
	private $IM =null;
	function __construct()
	{
		parent::__construct();
		$this->IM = M('grade_group');
	}
	/**
	 * 职位信息列表
	 */
	public  function listview()
	{
		$this->imodel = new IModel("grade_group");
    	$this->imodel->order = "id asc";
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


		if(array_key_exists('id', $this->g_params) && $this->g_params['id'] !=null)
		{
			$this->flagedit();
			$data = $this->IM->where(array('id'=>$this->g_params['id']))->find();
			$data['flag'] = json_decode($data['flag'],true);
		}else
		{
			$this->flagedit();
		}
		//系统菜单列表
		$rtn = array();
		$this->db = M('SystemMenu');
		$rst = $this->db->where('parentid=0 and isuse=1')->order('sorder asc')->select();
		foreach ($rst as $k => $v)
		{
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
		$this->assign('list',$rtn);
		$this->assign('data',$data);
		$this->display();
	}
	/**
	 * @cc 权限信息
	 * @author peterfzh@126.com
	 */
	function flagedit()
	{
		if(array_key_exists("flaglist", $this->postdata))
		{
			$this->postdata['flag'] = json_encode($this->postdata['flaglist']);
			unset($this->postdata['flaglist']);
			$data = $this->IM->where(array('id'=>$this->postdata['id']))->find();
			if($data)
			{
				$rst =  $this->IM->where(array('id'=>$this->postdata['id']))->data($this->postdata)->save();
				$this->assign('save',$rst);
			}else
			{
				$rst =  $this->IM->add($this->postdata);
				$this->assign('save',$rst);
			}
		}
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
			$param['password'] = md5($param['password']);
		}
		if(array_key_exists("id", $param) && $param['id'])
		{
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

			//$param['create_time']  = date('Y-m-d H:i:s',time());
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
		$param    = $this->data;
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