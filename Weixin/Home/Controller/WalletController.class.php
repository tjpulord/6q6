<?php
namespace Home\Controller;
use Think\Controller;


/**
 * 我的钱包控制类
 */
class WalletController extends BaseController {

    //钱包积分数据库
    private $sdb;
    //文章数据库
    private $adb;
    private $sgdb;

	/**
	 * 构造函数
	 */
	function __construct()
    {
    	parent::__construct();
    	$this->sdb = M('wallet');
        // $this->adb = M('article');
        // $this->sgdb = M('score_goods');
        // $this->signPackage();
    }

    // 志愿者申请介绍页面
    public function index()
    {

    	$this->display();
    }

    // 我的钱包流水页面
    public function history()
    {
    	$res = M('fund_log')->where('userid="%s"', $_SESSION['userinfo']['id'])->order('addtime desc')->limit(15)->select();
        $this->assign('res',$res);
    	$this->display();
    }
    // ajax加载
    public function ajaxAcount()
    {
        if (isset($_POST['page']) && is_numeric($_POST['page'])) {
            $page = $_POST['page'];
            $start = $page * 15 -1;
            $res = M('fund_log')->where('userid="%s"', $_SESSION['userinfo']['id'])->order('addtime desc')->limit($start, 15)->select();
            echo json_encode($res);
        } else {
            echo json_encode(array());
        }
    }


    // 我的积分页面
    public function score()
    {
        $res = $this->sdb->find($_SESSION['userinfo']['id']);
        $this->assign('wallet',$res);
    	$this->display();
    }

    // 积分明细页面
    public function score_history()
    {
    	$this->display('history');
    }

    /**
     * 钱包充值
     * @return [type] [description]
     */
    public function recharge()
    {
        $res = $this->sdb->find($_SESSION['userinfo']['id']);
        $this->assign('wallet',$res);
        $this->display();
    }

    // 积分兑换
    public function exchange()
    {
        $res = $this->sdb->find($_SESSION['userinfo']['id']);
        $ruler = $this->sgdb->find(1);
        // var_dump($res);exit;

        $this->assign('ruler',$ruler);
        $this->assign('wallet',$res);
    	$this->display();
    }

    // 积分兑换输入兑换分数
    public function inputscore()
    {
        $res = $this->sdb->find($_SESSION['userinfo']['id']);
        $this->assign('wallet',$res);
        $this->display();
    }


    // 积分规则以及积分兑换说明页面
    public function guide()
    {
        $article = $this->adb->find(49);
        $this->assign('ruler', $article);
    	$this->display();
    }

    /**
     * 如何兑换积分
     */
    public function instruction()
    {
        $article = $this->sgdb->find(1);
        $this->assign('ruler', $article);
        $this->display();
    }
}