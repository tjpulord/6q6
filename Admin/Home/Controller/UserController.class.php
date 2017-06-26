<?php
namespace Home\Controller;
use Think\Controller;

/**
 * @cc 用户管理模块
 */
class UserController extends BaseController {

    function __construct()
    {
    	parent::__construct();
    }
    /**
     * [signin description]
     * @return [type] [description]
     */
    function signin()
    {
    	$this->display();
    }

    function getcode(){
    	$config =    array(
    	'fontSize'    =>    30,    // 验证码字体大小
    	'length'      =>    4,
    	// 验证码位数
    	'useNoise'    =>    true,
    	// 关闭验证码杂点
    	);
    	$Verify = new \Think\Verify($config);
    	$Verify->entry(1);
    	var_dump($Verify);
    }

    /**
     * [signin_act description]
     * @return [type] [description]
     */
    function signin_act()
    {
    	// $verify = new \Think\Verify();
    	// if(!$verify->check($this->data['verifys'], 1))
    	// {
    	// 	echo $this->getJson("验证码不正确","",4000);
    	// }
    	session(null);
    	cookie(null);
    	$model_db = M('admin_user');
    	$model_rs = $model_db
    			  ->where('username="%s" and password="%s"',$this->postdata['username'],md5($this->postdata['password']))
    			  ->find();
    	if($model_rs)
    	{
    		$dataArray = array(
					'last_ip'   => get_client_ip(),
					'last_time' => date("Y-m-d H:i:s",time()),
    			);
    		$is_success = $model_db->where("id=%d",$model_rs['id'])->save($dataArray);
    		if($is_success)
    		{
    			if($model_rs['is_del'] == 1)
    			{
    				echo $this->getJson("你的账户已被系统锁定，请及时联系系统管理员","",4000);
    				return ;
    			}
                session('admin_id',$model_rs['id']);
                cookie('admin_id',$model_rs['id']);
                cookie('admin',$model_rs['username']);
    			echo $this->getJson("登陆成功",U('Index/index'),2000);
    		}
    		else
    		{
    			echo $this->getJson("系统故障，请及时联系系统管理员","",4000);
    		}
    	}
    	else
    	{
    		echo $this->getJson("用户名或密码不正确","",4000);
    	}
    }

    /**
     * 退出@动作
     * @return [type] [description]
     */
    function logout()
    {
        cookie(null);
        session(null);
        $this->success("退出成功",U('User/Signin'));
    }

    /**
     * 获取所有用户的所有信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getusersjs()
    {
        echo "var commoncitys=new Array();\n";
        echo "var citys=new Array();\n";
        $m_db  = M('admin_user');
        $m_rs1 = $m_db->where('level_id=1')->field(array('id','name','mobile'))->select();
        $m_rs2 = $m_db->field(array('id','name','mobile'))->select();
        $i     = 0;
        foreach ($m_rs1 as $k => $v) {
            echo "commoncitys[$i]=new Array('".$v["level_id"]."','".$v["id"].'-'.$v["name"]."','".$v["mobile"]."','".$v["group_id"]."');\n";
            $i++;
        }
        $i     = 0;
        foreach ($m_rs2 as $k => $v) {
            echo "citys[$i]=new Array('".$v["level_id"]."','".$v["id"].'-'.$v["name"]."','".$v["mobile"]."','".$v["group_id"]."');\n";
            $i++;
        }
    }
}