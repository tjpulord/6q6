<?php
namespace Home\Controller;
use Think\Controller;

class BaseController extends Controller {

	public $db;
	public $data;
    /**
	 * 初始化加载
	 */
	function __construct()
	{
        parent::__construct();
		$this->data = array_merge($_GET,$_POST);
	}


    /**
     * 返回json
     */
    function getJson($m,$d="ok",$c=2000)
    {
        echo json_encode(array(
                'msg'   => $m,
                'data'  => $d,
                'code'  => $c,
            ));
        die();
    }

    // 失败返回json
    function getError($m,$d="error")
    {
        echo json_encode(array(
                'msg'   => $m,
                'data'  => $d,
                'code'  => 4000,
            ));
        die();
    }




    // 判断参数是否齐全
    public function checkParam()
    {
        $num   = func_num_args();
        $plist = func_get_args();
        for ($i=0; $i < $num; $i++) {
            if (!array_key_exists($plist[$i], $this->data)) {
                return $plist[$i];
            }
        }
        return '';
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