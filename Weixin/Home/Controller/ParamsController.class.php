<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 基础控制类
 */
class ParamsController extends Controller{

	public $userinfo;
    public $redirect_uri;
	/**
	 * 基础类构造函数
	 */
	function __construct()
    {
    	parent::__construct();
        $this->redirect_uri = urlencode('http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]);
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

}