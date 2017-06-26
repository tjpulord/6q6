<?php
namespace Home\Controller;
use Think\Controller;
use Home\Service\Wechat;
/**
 * 基础控制类
 */
class RegisterController extends Controller{

    private $userdb;
	/**
	 * 基础类构造函数
	 */
	function __construct()
    {
    	parent::__construct();
        $this->userdb = M('user');
        // $this->signPackage();
    }

    /**
     * 登录
     * @return [type] [description]
     */
    public function  signin()
    {
        $url = isset($_GET['url']) ? $_GET['url'] : "/index.php?s=/home/MyCenter/index.html";
        // redirect('http://'.$_SERVER['SERVER_NAME']."/weixin.php/home/Register/login?url=$url");
        if($_POST['phone']){
            $userinfo = $this->userdb->where(array('mobile'=>$_POST['phone']))->find();
            if ($userinfo) {
                // $userinfo['login'] = 1;
                cookie('WUSER',$userinfo);
                // session('userinfo', $userinfo);
                redirect($url);
            } else {
                // echo '<script>alert("无此用户，请检查手机号是否正确");history.go(-1)</script>';
                $weinfo = session('weinfo');
                $userdata = array('mobile'=>$_POST['phone'],'openid'=>$weinfo['openid'], 'nickname'=>$weinfo['nickname'], 'header'=>$weinfo['headimgurl']);
                $res = $this->userdb
                        ->data($userdata)
                        ->add();
                cookie('WUSER', $userdata);
                redirect('index.php?s=/home/MyCenter/myinfo');
            }
        } else {
            $this->display();
        }
    }


    /**
     * 登录
     * @return [type] [description]
     */
    public function  invitation()
    {
        // if (!$this->_USER->login){
            $this->signPackage();

            $this->assign('art', M('article')->find(64));
            $res = M('system_setting')->where('`key` in ("share_inv_title","share_invitation","share_inv_img")')->select();
            $setting =array();
            foreach ($res as $key => $value) {
                $setting[$value['key']] = $value['value'];
            }
            $this->assign('setting',$setting);
            // $this->assign('login',$this->_USER->login)
            $this->display();
        // } else {
        //     echo '<script>alert("此微信号已绑定，请使用新的微信号注册");history.go(-1)</script>';
        //     die();
        // }
    }

    /**
     * 重置密码
     * @return [type] [description]
     */
    public function resetpwd()
    {
        if($_POST['phone']){
            $url = $_GET['url']? $_GET['url']:"/weixin.php?s=/home/Register/signin.html";
            $userinfo = $this->userdb->where(array('phone'=>$_POST['phone']))->save(array('password'=>md5($_POST['password'])));
            if ($userinfo || $this->userdb->where(array('phone'=>$_POST['phone'],'password'=>md5($_POST['password'])))->find()) {
                echo '<script>alert("修改密码成功");</script>';
                redirect($url);
            } else {
                $this->error("无此用户，请注册！","/weixin.php?s=/home/Register/login.html");
            }
        } else {
            $this->display();
        }
    }

    /**
     * 注册
     * @return [type] [description]
     */
    public function login()
    {
        $url = $_GET['url']? $_GET['url']:urldecode("/weixin.php?s=/home/MyCenter/index.html");
        if (!$this->_USER->login){
            if($_POST['phone']){
                $usercount = $this->userdb->count() + 1000001;
                $userinfo['id'] = 'TD'.substr(date("Y"),-2).date("m").substr($usercount, -6);
                $userinfo['addtime'] = time();
                $userinfo['updatetime'] = time();
                $userinfo['phone'] = $_POST['phone'];
                $userinfo['password'] = md5($_POST['password']);
                $userinfo['openid'] = $this->_USER->openid;
                $userinfo['issubscribe'] = $this->getscribed($this->_USER->openid);
                $find = $this->userdb->where(array('phone'=>$userinfo['phone']))->find();
                if ($find){
                    // echo '<script>alert("此手机号已注册");history.go(-1)</script>';
                    // 修改用户信息
                    $res = $this->userdb->where(array('phone'=>$_POST['phone']))
                        ->data(array('openid'=>$this->_USER->openid, 'nickname'=>$this->_USER->nickname, 'header'=>$this->_USER->headimgurl, 'issubscribe'=>$userinfo['issubscribe']))
                        ->save();
                    if ($res) {
                        redirect($url);
                    }else{
                        echo '<script>alert("注册失败，请重试");history.go(-1)</script>';
                    }
                    die();
                }

                //user模型事务开启
                $this->userdb->startTrans();
                //wallet开启事务
                // $wdb->startTrans();
                $data['userid'] = $userinfo['id'];
                $wdb = M('wallet');
                if ($_SESSION['invphone']){
                    $invuser = $this->userdb->where(array('phone'=>$_SESSION['invphone']))->find();
                    M('invite_log')->add(array('userid'=>$invuser['id'],'inviteid'=>$userinfo['id'],'invite_time'=>date('Y-m-d')));
                    $this->addscore($invuser['id'],7);
                    $userinfo['referenceid'] = $invuser['id'];
                    $url = "/weixin.php?s=/home/Register/addcontact.html";
                }
                $res = $this->userdb->data($userinfo)->filter('strip_tags')->add();
                $res2 = $wdb->data($data)->add();
                if ($res && $res2) {
                    $this->userdb->commit();
                    $this->addscore($userinfo['id'],9);
                    // $wdb->commit();
                    redirect($url);
                } else {
                    $this->userdb->rollback();
                    $wdb->rollback();
                    echo '<script>alert("注册失败，请重试");history.go(-1)</script>';
                    die();
                }
            } else {
                $this->display();
            }
        } else {
            // echo '<script>alert("此微信号已绑定，请使用新的微信号注册");history.go(-1)</script>';
            // die();
            // if ($_SESSION['invphone']) {
            //     $url = "/weixin.php?s=/home/Register/addcontact.html";
            // }
            redirect($url);
        }
    }


    /**
     * 关注微信公众号
     * @return [type] [description]
     */
    public function addcontact()
    {
        $erweima = M('system_setting')->where(array('key'=>"guanzhu_img"))->find();
        $this->assign('data',$erweima);
        $this->display();
    }

    // 完善个人信息
    public function complete()
    {
        $userinfo = $_POST['info'];
        $url = $_GET['url']? $_GET['url']:"/weixin.php?s=/home/MyCenter/index.html";
        if ($userinfo) {
            $userinfo['updatetime'] = time();
            $res = $this->userdb->where(array("id"=>$_SESSION['userinfo']['id']))->filter('strip_tags')->save($userinfo);
            if ($res || $this->userdb->where(array("id"=>$_SESSION['userinfo']['id']))->find()) {
                session('name',$userinfo['name']);
                redirect($url);
            }
        }

        $this->display();
    }

    public function checkuser()
    {
        if($this->userdb->where(array("phone" => $_POST['mobile']))->find()){
            echo json_encode(1);
        }else{
            echo json_encode(0);
        }
    }
    private function addscore($userid, $actionid=9)
    {
        $post_data = array();
        $post_data['actionid'] = $actionid;
        $post_data['userid'] = $userid;
        $post_data['submit'] = "submit";
        $url = 'http://'.$_SERVER['HTTP_HOST']. '/interface.php?s=/home/score/add';
        // $o="";
        // foreach ($post_data as $k=>$v)
        // {
        //     $o.= "$k=".urlencode($v)."&";
        // }
        // $post_data=substr($o,0,-1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $result = curl_exec($ch);
        return json_decode($result);
        // var_dump($result);exit;
    }


    /**
     * 判断用户是否订阅公众号
     * @param  [type] $openid [用户微信openid]
     * @return [type]         [description]
     */
    function getscribed($openid)
    {
        $tempdb = M('weixin_user_temp');
        $userstatus = $tempdb->where(array('openid'=>$openid))->find();
        if ($userstatus) {

            return $userstatus['issubscribe'];
        }
        return 0;
    }


    /**
     * 列出城市列表
     * @return [type] [description]
     */
    public function citylist()
    {
        $areadb = M('areas');
        $hotcity = $areadb->where(array('status'=>2))
                    ->limit(6)->order('id asc')
                    ->select();

        $citylist = $areadb->where(array('status'=>1))->order('id desc')->select();

        $this->assign('hotcity', $hotcity);
        $this->assign('citylist', $citylist);

        $this->display('chengshixuanze');
    }


    function xuanzexiaoqu(){
        $zoneid = $_GET['zid'];
        $cityid = $_GET['cid'];
        $search = $_GET['key'];
        $zonedb = M('community');
        $areadb = M('areas');

        $searchstr = "1";
        if ($cityid) {
            $searchstr .= " and city=$cityid";
            $city = $areadb->where(array('id'=>$cityid))->find();
            $this->assign('city', $city);
        }
        if ($search) {
            $searchstr .= " and fullname like '%".$search."%'";
        }
        $zonelist = $zonedb->where($searchstr)->limit(20)->order('id asc')->select();

        $this->assign('zonelist', $zonelist);
        $this->display();
    }
}