<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 个人中心控制类
 */
class MyCenterController extends WeixinController {

    private $udb;
    private $adb;
	/**
	 * 构造函数
	 */
	function __construct()
    {
    	parent::__construct();
    	$this->udb = M('user');
        // $this->adb = M('plan_account');
        // $this->assign('user',$this->_USER);
    }


    // 个人中心页面
    public function index()
    {

        $this->assign('user',$this->_USER);
    	$this->display();
    }


    // 个人信息修改展示页面
    public function myinfo()
    {
        if (!$this->_USER['id']) {
            redirect('http://'.$_SERVER['SERVER_NAME']."/index.php/home/Register/signin?url=$this->redirect_uri");
        }

        if ($_POST){
            $data = $_POST;
            $data['auth'] = 1;
            $this->udb->where(array('openid'=>$this->_USER['openid']))
                    ->filter('strip_tags')->save($data);
            $this->_USER = $this->udb
                            ->where(array('openid'=>$this->_USER['openid']))
                            ->find();
            cookie('WUSER',$this->_USER);
            // $_SESSION['userinfo'] = $this->udb
            //                         ->where(array('openid'=>$this->_USER['openid']))
            //                         ->find();
            $this->assign('script','<script>alert("信息修改成功");location.href="'.U('myCenter/index').'";</script>');
        }

        if ($_GET['zid']) {
            $zonedb = M('community');
            $zoneinfo = $zonedb->where('id='.$_GET['zid'])->find();
            $this->assign('zone',$zoneinfo);
        }

        $imagedb = M('image_data');
        $imagelist = $imagedb->where(array('userid'=>$this->_USER['id'], 'position'=>2))->order('id asc')->select();
        $this->assign('imagelist', $imagelist);

        $this->assign('user',$this->_USER);
        $this->display();
    }

    // 我要邀请页面
    public function invitation()
    {
        if (!$this->_USER['login']) {
            redirect('http://'.$_SERVER['SERVER_NAME']."/weixin.php/home/Register/signin?url=$this->redirect_uri");
        }
        $sdb = M('score_ruler');
        $inv = $sdb->where(array('actionid'=>7))->find();
        $inv1 = $sdb->where(array('actionid'=>9,'status'=>1))->find();
        if ($inv){
            $this->assign('inv',$inv);
            // $this->assign('inv2',$inv2);
            $adb = M('article');
            $data = $adb->find(61);
            if ($data) {
                $this->assign('invat_ruler',strip_tags($data['content']));
            }
            $res = M('system_setting')->where('`key` in ("share_inv_title","share_invitation","share_inv_img", "invatation_img")')->select();
            $setting =array();
            foreach ($res as $key => $value) {
                $setting[$value['key']] = $value['value'];
            }
            $this->assign('setting',$setting);
            $this->display();
        } else {
            echo '<script>alert("暂未添加邀请活动，请等待");history.go(-1)</script>';
            exit;
        }
        // $share['title'] = $accounts[0]['title'];
        // $share['thumb'] = $accounts[0]['thumb'];
        // $share['url'] = 'http://'.$_SERVER['HTTP_HOST']. U('plan/planview',array('pid'=>$accounts[0]['planid']));
        // $share['description'] = $accounts[0]['description'];
        // $this->assign('share',$share);
        // $this->display();
    }
    // 哟请好友规则
    public function invruler()
    {
        if (!$this->_USER['login']) {
            redirect('http://'.$_SERVER['SERVER_NAME']."/weixin.php/home/Register/signin?url=$this->redirect_uri");
        }
        $adb = M('article');
        $data = $adb->find(61);
        if ($data){
            $this->assign('data',$data);
            $this->display();
        } else {
            echo '<script>alert("暂未添加邀请规则，请等待");history.go(-1)</script>';
            exit;
        }
        // $share['title'] = $accounts[0]['title'];
        // $share['thumb'] = $accounts[0]['thumb'];
        // $share['url'] = 'http://'.$_SERVER['HTTP_HOST']. U('plan/planview',array('pid'=>$accounts[0]['planid']));
        // $share['description'] = $accounts[0]['description'];
        // $this->assign('share',$share);
        // $this->display();
    }


    // 我要邀请页面
    public function complete()
    {
        if (!$this->_USER['id']) {
            redirect('http://'.$_SERVER['SERVER_NAME']."/weixin.php/home/Register/signin?url=$this->redirect_uri");
        }
        if(!$_POST){
            $this->display();
        } else {
            $data['userid'] = $this->_USER['id'];
            $data['phone'] = $this->_USER['phone'];
            $data['content'] = $_POST['content'];
            $data['addtime'] = time();
            M("feedback")->data($data)->filter('strip_tags')->add();
            echo '<script>alert("感谢您的反馈");history.go(-2)</script>';
            exit;
        }
    }


    /**
     * 删除图片
     * @return [type] [description]
     */
    function delimage(){
        if (!$this->_USER['id']) {
            redirect('http://'.$_SERVER['SERVER_NAME']."/weixin.php/home/Register/signin?url=$this->redirect_uri");
        }
        $imageurl = $_POST['image'];
        if($imageurl) {
            $imagedb = M('image_data');
            $imagedata = array('userid'=>$this->_USER['id'],
                            'image'=>$imageurl,
                            'position'=>2);
            $res = $imagedb->where($imagedata)->delete();
            echo json_encode(array('code'=>$res));
        }else{
            echo json_encode(array('code'=>0));
        }
    }

    /**
     * 保存照片
     * @return [type] [description]
     */
    public function saveimage()
    {
        if (!$this->_USER['id']) {
            redirect('http://'.$_SERVER['SERVER_NAME']."/weixin.php/home/Register/signin?url=$this->redirect_uri");
        }
        $imageurl = $_POST['image'];
        if($imageurl) {
            $imagedb = M('image_data');
            $imagedata = array('userid'=>$this->_USER['id'],
                            'image'=>$imageurl,
                            'position'=>2);
            $res = $imagedb->data($imagedata)->add();
            echo json_encode(array('code'=>$res));
        }else{
            echo json_encode(array('code'=>0));
        }
    }

}