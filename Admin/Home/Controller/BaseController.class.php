<?php
namespace Home\Controller;
use Think\Controller;

class BaseController extends ActiveController {

	public $data, $postdata;
	public $g_params = null;

	public $userinfo = array();		 //员工的信息，全局变量

	public $systemMenu = array();   //全局化系统权限
	/**
	 * 初始化判断
	 */
    function __construct()
    {
    	parent::__construct();
    	$this->data = $_GET;
        $this->postdata = $_POST;
    	$this->g_params = array_merge($_POST,$_GET);
    	if($this->g_params['token'] != C('TOKEN'))
    	{
    		$this->getUserInfo();
        	$this->admin_statuc();
    	}
    }

    /**
     * 获取用户信息
     * @return [type] [description]
     */
    private function getUserInfo()
    {
        $uinfo = C('USERINFO');
        if(empty($uinfo))
        {
            $m_db = M('admin_user');
            $this->userinfo['userinfo'] = $m_db->find(session('admin_id'));
            if($this->userinfo['userinfo']['ismanager']==1)
            {
                $this->userinfo['manager'] = true;
            }
            else
            {
                $m_db = M('grade_group');
                $this->userinfo['groupinfo'] = $m_db->where(array('id'=>$this->userinfo['userinfo']['group_id']))->find();
                // $this->userinfo['groupinfo'] = $m_db->where('id=%d',$this->userinfo['userinfo']['group_id'])->find();
                $m_db = M('system_menu');
                $this->systemMenu = $m_db->select();
            }
            C('DATA_CACHE_TIME',60);
            C("USERINFO",$this->userinfo);
        }
        else
        {
            $this->userinfo = C('USERINFO');
        }
        if($this->userinfo['userinfo']['is_del'] == 1)
        {
        	session(null);
        	cookie(null);
        	$this->error("你的账户已被系统锁定，请及时联系系统管理员");
        }
    }

    /**
     * 魔术写法
     */
    function __set($key ,$value)
    {
    	$this->$key = $value;
    }

    /**
     * 返回json
     */
    function getJson($m,$d="ok",$c=2000)
    {
    	echo json_encode(array(
    			'msg'	=> $m,
    			'data'	=> $d,
    			'code'	=> $c,
    		));
    	die();
    }

    /**
     * 在线状态检查 初始化常用数据
     * @return [type] [description]
     */
    private function admin_statuc()
    {
        // var_dump(C('USERINFO'));
        if(session('admin_id') && $this->userinfo['manager'])
        {
            // echo "总管理员不需要权限判断";
        }
        else
        {
            $userinfo  = $this->userinfo['userinfo'];
            $groupinfo = $this->userinfo['groupinfo'];
            if(empty($groupinfo['flag']) && session('admin_id') )
            {
            	cookie(null);
            	session(null);
                $this->error("您没有操作权限,请联系管理员");
            }
            else
            {
                $flaglist  = json_decode($groupinfo['flag'],true);
                $mCtrl  = array(
	                    'm' => MODULE_NAME,
	                    'a' => ACTION_NAME,
	                    'c' => CONTROLLER_NAME,
               		);
                $m_Ctrl = strtolower($mCtrl['c']);
                $m_Ctrl_Array = array();
                foreach ($this->systemMenu as $k => $v)
                {
                    $tmp = explode("/", $v['url']);
                	$m_Ctrl_Array[] = strtolower($tmp[0]);
                }
                if(!in_array($m_Ctrl, $m_Ctrl_Array))
                {
                	return ;
                }

                $m_userMenu = json_decode($this->userinfo['groupinfo']['flag'],true);
                foreach ($m_userMenu as $k => $v)
                {
                    $tmp = explode("_", $k);
                	if( $m_Ctrl == strtolower($tmp[0]))
                	{
                		return;
                	}
                }
                $this->closeAlert("您没有操作权限,请联系系统管理员!");
                return;
            }
        }
    }

    /**
     * 关闭并且提示
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    function closeAlert($str){
        header("Content-type: text/html;charset=utf-8");
        $this->assign('data',$str);
        $this->display('Public/alert');
        die();
    }

    /**
     * 获取管理员ID
     * @return [type] [description]
     */
    function getAdminId()
    {
    	return cookie("admin_id");
    }

    /**
     * 获取@管理员信息
     * @return [type] [description]
     */
    function getAdminInfo()
    {
        $model_db = M('admin');
        $model_if = $model_db->find(self::getAdminId());
        return $model_if;
    }

    function setjson($code,$msg=null,$data=null)
    {
    	return json_encode(array('code'=>$code, 'msg'=>$msg, 'data'=>$data));
    }


    function __R($wd,$url,$time=3,$moban){
        //layout(null);
        $this->assign('message',$wd);
        $this->assign('jumpUrl',$url);
        $this->assign('waitSecond',$time);
        $this->display($moban);
        die();
    }

     //列出控制器
    function list_controller()
    {
        $planPath = dirname(__FILE__);
        $planList = array();
        $rtns     = array();
        $dirRes   = opendir($planPath);
        while($dir = readdir($dirRes))
        {
            if(!in_array($dir,C('SAFE_CLASS')))
            {
                $class_name = basename($dir,'.class.php');
                $clss = "\Home\Controller\\$class_name";
                $class      = new $clss;
                $methods    = get_class_methods($class);
                if(!empty($methods))
                {
                    $funs       = array();
                    foreach ($methods as $k => $fun) {
                        $func = new \ReflectionMethod($class,$fun);
                        if(!in_array($func->class,array('Think\Controller')) && !in_array($fun,C('SAFE_FUNS')))
                        {
                            $tmp = $func->getDocComment();
                            $tmp = $this->getDes($tmp);
                            $funs[] = array('name'=>$fun,'details'=>$tmp);
                        }
                    }
                    $clss = new \ReflectionClass($class);
                    $tmps = $clss->getDocComment();
                    $tmps = $this->getDes($tmps);
                    if(!empty($funs))
                    {
                        $class_name = basename($class_name,'Controller');
                        $rtns[] = array('name'=>$class_name,'des'=>$tmps,'func'=>$funs);
                    }
                }
            }
        }

        return $rtns;
    }

    /**
     * 获取类别描述
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    function getDes($tmp)
    {
        $flag  = preg_match_all('/@cc(.*?)\n/',$tmp,$tmp);
        $tmp   = trim($tmp[1][0]);
        $tmp   = $tmp !='' ? $tmp:'无';
        return $tmp;
    }

    /**
     * 获取JS的列表
     * @return [type] [description]
     */
    function getAreaJs()
    {
        $ilazy = new \iLazy\Area();
        $list = $ilazy->getList($this->data['pid']);
        // var_dump($list);
        if(!array_key_exists('pid',$this->data))
        {
            echo "var province=".json_encode($list).";";
        }
        else
        {
            echo json_encode($list);

        }

    }

}