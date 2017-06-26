<?php
namespace Home\Controller;
use Think\Controller;
use Home\Controller\IModelController as IModel;

/**
 * @cc 通用模块
 * @author Administrator
 *
 */
class ICommonController extends BaseController
{
	protected $tlb = NULL;
	public $imodel = NULL;
	protected $IM = null;
	/**
	 * 模块儿初始化
	 */
	function __construct($ltb)
	{
 		parent::__construct();
		$this->tlb = $ltb;		
		$this->IM = M($ltb);		
	}
	/**
	 *  @保单列表
	 * @return [type] [description]
	 */
 	 function _listview($field=null, $join=null, $where=null,$count=12,$order =null,$sum_key =null)
    {
		$this->imodel = new IModel($this->tlb); 
		$start  = $this->g_params['logmin'];
		$endtm  = $this->g_params['logmax'];
		//$kw     = I('post.kw');
		if(!empty($join))
		{
			$this->imodel->join = $join;
		}
		if(!empty($field))
		{
			$this->imodel->field = $field;
		}

		
		
		if($this->g_params['is_del'] !=null && $where !=null)
		{
				$this->imodel->search = $where. " is_del =".$this->g_params['is_del'];
		}elseif($this->g_params['is_del'] ==null &&  $where !=null) 
		{
				$this->imodel->search = $where."  is_del = 0";
		}	
		elseif($this->g_params['is_del'] ==null &&  $where !=null)
		{
			$this->imodel->search = " is_del = 0";
		}
		
		if(!empty($this->g_params[$kw]))	
		{
			$this->imodel->search .=" and $kw like '%$this->g_params[$kw]%'";
		}	
    	if($start)
    	{
    		$this->imodel->search .= " and create_time >= '$start 00:00:00'";
    	}
    	if($endtm)
    	{
    		$this->imodel->search .= " and create_time <= '$endtm 23:59:59'";
    	}
    	if($order!=null)
    	{
    		$this->imodel->order = $order;
    	}
    	if($sum_key!=null)
    	{
    		$this->imodel->sum_key = $sum_key;
    	}
    	//echo $this->imodel->search;
    	$this->imodel->params = $this->g_params;
    	$this->imodel->pagesize = $count;
    	$this->imodel->datafill();    	
    }
    /**
     * 单条数据展示
     */
    function _view()
    {
    	$model_if = $this->IM->where(array('id'=>$this->g_params['id']))->find();
    	$this->assign('data',$model_if);
		$this->display();
    }
    /**
     * 查询保单详细信息
     */
	function _detailedinfoToJson()
	{
		$m_data = array();
		//保单基本信息
		$model_if = $this->IM->where(array('id'=>$this->g_params['id']))->find();
		switch ($model_if['ins_new_old'])
		{
			case '':
				$model_if['ins_new_old'] = "新保";
				break;
			case 0:
				$model_if['ins_new_old'] = "新保";
				break;
			case 1:
				$model_if['ins_new_old'] = "续保 ";
				break;	
			case 2:
				$model_if['ins_new_old'] = "转保 ";
				break;
			case 3:
				$model_if['ins_new_old'] = "共保 ";
				break;
			case 4:
				$model_if['ins_new_old'] = "临分 ";
				break;
		}
		$ins_type_name = C('ZHH_PRODUCT');	
		$ins_type_name = $ins_type_name[$model_if['ins_type']];
		$m_data['ins_type_name'] = $ins_type_name;
		
		$m_objID  = $model_if['ins_policy_number'];
		$m_Veh_frame_number = $model_if['veh_frame_number'];		
		//保单基本信息
		$m_data['policybaseinfo'] = $model_if;
		
		//保单详情
		$M_DB = M('policyinfo');
		$ins_policyinfo = $M_DB->where(array('ins_Policy_number'=>$m_objID))->select();
		$m_data['policyinfo'] = $ins_policyinfo;
		//dump($ins_policyinfo);
		
		//车辆信息
		$M_DB = M('carinfo');
		$carInfo = $M_DB->where(array('Veh_frame_number'=>$m_Veh_frame_number))->find();
		$m_data['carinfo'] = $carInfo;
		//dump($this->g_params['id']);
				
		//保单附加信息
		$M_DB = M('additional_risk');
		$additional_risk = $M_DB->where(array('ins_Policy_number'=>$m_objID))->select();
		$m_data['additional_risk'] = $additional_risk;	

		//理赔信息
		$M_DB = M('claim_information');
		$claim_information = $M_DB->where(array('Veh_frame_number'=>$m_Veh_frame_number))->select();
		$m_data['claim_information'] = $claim_information;
		
		return $m_data;
	}
	/**
	 * @编辑列表
	 * @return [type] [description]
	 */
	function _itemedit()
	{
// 		$model_db = M('proxy');
		$model_if = $this->IM
			->where(array('id'=>$this->g_params['id']))
			->find();
		$this->assign('data',$model_if);
		$this->display();
	}
	
	/**
	 *@列表编辑保存
	 * @return [type] [description]
	 */
	function _itemedit_act($check_key=null, $url=null, $msg=null)
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
			
			$param['create_time']  = date('Y-m-d H:i:s',time());
			$result = $this->IM
			->data($param)
			->add();
		}
		// $this->success('编辑成功！',U('Notice/listview'));
		// die();
		$this->display();
	}
	/**
	 * @列表删除保存
	 * @return [type] [description]
	 */
	function _del_act()
	{
		$param    = $this->data;
// 		$model_db = M('proxy');
		if(array_key_exists("id", $param) && $param['id'])
		{
			$param['create_time']  = date('Y-m-d H:i:s',time());
			$result = $this->IM
				->where('id=%d',$param['id'])
				->save($param);
			echo $this->setjson(2000,'success');
		}else
		{
			echo $this->setjson(4000,'faild');
		}
	}
	
	/**
	 * 修改数据
	 */
	function _update_act()
	{
		$param    = array_merge($_POST,$_GET);
		if(array_key_exists("id", $param) && $param['id'])
		{
			$param['create_time']  = date('Y-m-d H:i:s',time());
			$result = $this->IM
				->where('id=%d',$param['id'])
				->save($param);
			return  $this->setjson(2000,'success');
		}else
		{
			return $this->setjson(4000,'faild');
		}
	}
}