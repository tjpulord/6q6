<?php
namespace Home\Controller;
use Think\Controller;
use Home\Controller\ICommonController as IModel;

/**
 * @cc 业务管理系统
 * @author Administrator
 *
 */
class ModelController extends BaseController
{	
	protected $ICommon = null;
    protected $ilazy;
	function __construct()
	{
		parent::__construct();
      	$this->ilazy = new \iLazy\Temp('model');
      	$this->ilazy->init();
	}

	/**
	 * @cc 空的控制器
	 * @return [type] [description]
	 */
	function index(){
		
	}

	function listfield(){
		
	}

}