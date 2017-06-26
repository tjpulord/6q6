<?php
 namespace Home\Controller;
 use Think\Controller;

 class CommonController extends Controller {
    function __construct()
    {
      	parent::__construct();
    }

    function success($wd='操作成功',$url='',$time=2,$goback=0)
    {
        $this->assign("title",$wd);
        $this->assign('waitSecond',$time);
        $this->assign('jumpUrl',$url);
        $this->assign('icon','weui_icon_success');
        $this->assign('goback',$goback);
        $this->display("Wpublic:transfer");
    }
   
    function error($wd='操作失败',$url='',$time=2,$goback=1)
    {
        $this->assign("title",$wd);
        $this->assign('waitSecond',$time);
        $this->assign('jumpUrl',$url);
        $this->assign('icon','weui_icon_warn');
        $this->assign('goback',$goback);
        $this->display("Wpublic:transfer");
    }
 }


