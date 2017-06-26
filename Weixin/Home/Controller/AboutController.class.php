<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 关于展示控制类
 */
class AboutController extends WeixinController {

    private $systemdb;
    function __construct()
    {
        parent::__construct();
        // $this->systemdb = M('system_setting');
        // $this->signPackage();
    }
	// 关于我们页面
    public function about()
    {
        
        $this->display('about_us');
    }


    // 联系我们页面
    public function contactus()
    {
        
    	$this->display('contact_us');
    }
}