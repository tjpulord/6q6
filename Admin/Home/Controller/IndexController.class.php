<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends BaseController {

    /**
     * @cc 程序主入口
     * @return [type] [description]
     */
    public function index(){
        if(session("admin_id") == null)
    	{
    		$this->redirect('User/Signin');
    	}
        $mitr = new \iLazy\Menu();      
        $mitr->g_userinfo = C('USERINFO');
        if($mitr->g_userinfo['userinfo']['ismanager'])
        {
        	$list = $mitr->show();
        }else 
        {
        	$list = $mitr->ShowUserMenu();
        }       
        $this->assign('list',$list);
    	$this->display();
    }

    /**
     * @cc 欢迎界面
     * @return [type] [description]
     */
    public function welcome(){
    	$this->display();
    }
}