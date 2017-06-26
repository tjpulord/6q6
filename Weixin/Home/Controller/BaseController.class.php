<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 基础控制类
 */
class BaseController extends WeixinController{

	public $userinfo;
    public $redirect_uri;
	/**
	 * 基础类构造函数
	 */
	function __construct()
    {
    	parent::__construct();
     //    $this->redirect_uri = urlencode('http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]);
     //    $this->assign('_URI',$this->redirect_uri);
    	// if ($this->_USER->login == 0) {
     //        redirect('http://'.$_SERVER['SERVER_NAME']."/weixin.php/home/Register/signin?url=$this->redirect_uri");
     //    }
    }

}