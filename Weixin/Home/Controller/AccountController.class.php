<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 已加入计划账户控制类
 */
class AccountController extends BaseController {

    private $adb;
    private $db_log;
    private $pdb;
	/**
	 * 构造函数
	 */
	function __construct()
    {
    	parent::__construct();
    	$this->adb = M('plan_account');
        $this->db_log = M('account_log');
        $this->pdb = M('plan');
        $this->mdb = M('mutual_money');
        $this->signPackage();
    }


    // 已加入计划账户列表展示
    public function accountlist()
    {
        $accounts = $this->adb->join('as a left join td_plan as p on a.planid=p.id left join td_relationship as r on a.cardnumber=r.cardnum and r.userid="'.$_SESSION['userinfo']['id'].'"')->group('a.id')
            ->where(array('a.userid'=>$_SESSION['userinfo']['id']))
            ->field('a.*, p.title as title, r.name as uname ,p.top_money as tmoney,p.day_money as dmoney' )
            ->order('status=2 desc, status asc,addintime desc')->select();
        $res = M('account_threshold')->select();
        foreach ($accounts as $key1 => $value1) {
            $days = diffday($value1['addintime']);
            $accounts[$key1]['day'] = $days;
            $va = getclaim($value1['planid'], $value1['cardnumber'], $value1['addintime']);
            if ($va) {
                $accounts[$key1]['dmoney'] = $va[1];
                $accounts[$key1]['tmoney'] = $va[2];
            }else{
                $accounts[$key1]['dmoney'] = $accounts[$key1]['tmoney'] = '';
            }
        }
        $linjie = array();
        foreach ($res as $key => $value) {
            $linjie[$value['name']] = $value['value'];
        }
        $accounts2 = array();
        foreach ($accounts as $key => $value) {
            $accounts2[$value['status']][] =  $value;
        }
        $this->assign('accounts',$accounts);
        $this->assign('linjie',$linjie);
        $this->assign('accounts2',$accounts2);
    	$this->display();
    }

    public function  recharge()
    {
        if (isset($_GET['id']) && is_numeric($_GET['id'])){
            $account = $this->adb->join('as a left join td_plan as p on a.planid=p.id left join td_relationship as r on a.cardnumber=r.cardnum')
                ->where(array('a.userid'=>$_SESSION['userinfo']['id'], 'a.id'=>$_GET['id']))
                ->field('a.*, p.title as title, r.name as uname')
                ->order('status=2 desc, status asc,addintime desc')->find();
            if ($account) {
                $this->assign('account',$account);
                $this->display();
            } else {
                 echo '<script>alert("此账户不可充值");history.go(-1)</script>';
                 exit;
            }
        }else {
            echo '<script>alert("参数错误！");history.go(-1)</script>';
            exit;
        }
    }

    /**
     * 删除失效账户
     */
    /*public function del()
    {
        $res = $this->adb->where(
                array('id'=>$_POST['id'], 'userid'=>$_SESSION['userinfo']['id'], 'status'=>3)
            )->save(array('isdel'=>1));

        if ($res) {
            echo json_encode(1);
        } else {
            echo json_encode(0);
        }
    }*/


    // 已加入计划账户流水
    public function history()
    {
        if (isset($_GET['id']) && is_numeric($_GET['id'])){
            $id = $_GET['id'];
            $logs = $this->db_log->where(array('planaccountid'=>$id))->order('addtime desc')->select();
            $zhichu = $this->db_log->where(array('planaccountid'=>$id, 'flow'=>1))
                ->field('sum(trade) as sum,count(1) as count')->order('addtime desc')->select();
            // var_dump($zhichu);exit;
            $logs1 = array();
            foreach ($logs as $key => $value) {
                $logs1[$value['flow']][] = $value;
            }
            $accounts = $this->adb->where(array('id'=>$id))->find();
            $this->assign('zhichu',$zhichu);
            $this->assign('account',$accounts);
            $this->assign('logs1',$logs1);
            $this->assign('logs',$logs);
            $this->display();
        } else {
            echo '<script>alert("参数错误");history.go(-1)</script>';
            exit;
        }

    }


    // 已加入计划列表
    public function planlist()
    {
        $accounts = $this->adb->join('as a left join td_plan as p on a.planid=p.id')
            ->group('a.id')->where(array('a.userid'=>$_SESSION['userinfo']['id']))
            ->field('a.*, p.title as title, a.username as uname')
            ->order('status=2 desc, status asc,addintime desc')->select();
        foreach ($accounts as $key1 => $value1) {
            $days = diffday($value1['addintime']);
            $accounts[$key1]['day'] = $days;
            $va = getclaim($value1['planid'], $value1['cardnumber'], $value1['addintime']);
            if ($va) {
                $accounts[$key1]['dmoney'] = $va[1];
                $accounts[$key1]['tmoney'] = $va[2];
            }else{
                $accounts[$key1]['dmoney'] = $accounts[$key1]['tmoney'] = '';
            }
        }
        $this->assign('accounts',$accounts);
        $this->display();
    }


    // 已加入计划详情展示页面
    public function planview()
    {
        if (isset($_GET['id']) && is_numeric($_GET['id'])){
            $id = $_GET['id'];

            $accounts = $this->adb->join('as a left join td_plan as p on a.planid=p.id')
                ->group('a.id')->where(array('a.id'=>$id))->field('a.*, p.title as title,p.description,p.thumb')->select();
            // $id = $accounts[0]['cardnumber'];//18位身份证号
            // $accounts[0]['baozhangqi'] =
            // $baozhangqi = getObservation($accounts[0]['rulerid'],$id);
            $baozhangqi = getObservation($accounts[0]['planid'],$accounts[0]['cardnumber'], $accounts[0]['addintime']);

            if ($accounts) {
                $fenshu = $this->adb->where(array('planid'=>$accounts[0]['planid']))->count();
                $mutual = $this->mdb->where(array('planid'=>$accounts[0]['planid'],'status'=>2))->field('sum(appropriation) as sum,count(*) as count')->select();

                $accounts[0]['baozhangqi'] =  $baozhangqi;
                $share['title'] = $accounts[0]['title'];
                $share['thumb'] = $accounts[0]['thumb'];
                $share['url'] = 'http://'.$_SERVER['HTTP_HOST']. U('plan/planview',array('pid'=>$accounts[0]['planid']));
                $share['description'] = $accounts[0]['description'];
                $this->assign('share',$share);
                $this->assign('account',$accounts[0]);
                $this->assign('fenshu',$fenshu);
                $this->assign('mutual',$mutual);
                $this->display();
            } else {
                echo '<script>alert("找不到此计划");</script>';
                exit;
            }
        } else {
            echo '<script>alert("参数错误");history.go(-1)</script>';
            exit;
        }
    }


    // 互助金申请页面
    public function mutualmoney()
    {
        if (isset($_GET['cardnumber'])){
            //检查是否有可申请的账户
            //
            $res = $this->adb->where(array('userid'=>$_SESSION['userinfo']['id'], 'planid'=>$_GET['pid'],'cardnumber'=>$_GET['cardnumber'], 'status'=>array('neq',3)))->find();
            $res2 = $this->mdb->where(array('userid'=>$_SESSION['userinfo']['id'], 'planid'=>$_GET['pid'], 'cardnumber'=>$_GET['cardnumber']))->find();
            // var_dump($res)
            if ($res){
                //提交信息
                if ($_POST) {
                    $data = $_POST;
                    $data['userid'] = $_SESSION['userinfo']['id'];
                    $data['addtime'] = time();
                    $data['status'] = 0;
                    if ($res2) {
                        $res = $this->mdb
                            ->where(array('userid'=>$_SESSION['userinfo']['id'], 'planid'=>$_GET['pid'], 'cardnumber'=>$_GET['cardnumber']))
                            ->filter('strip_tags')->save($data);
                    }else{
                        $res = $this->mdb
                            ->filter('strip_tags')->add($data);
                    }

                    if ($res) {
                        echo '<script>alert("提交成功，请等待审核！");</script>';
                        redirect(U('account/planlist'));
                    } else {
                        echo '<script>alert("提交失败");</script>';
                        redirect(U('account/planlist'));
                    }
                } else {//显示页面
                    // 客服电话
                    $kefu = M('system_setting')->where(array('key'=>"rexiandianhua"))->field('value')->find();
                    $this->assign('kefu',$kefu);
                    $this->assign('account',$res);
                    if ($res2) {
                        $this->assign('mutual',$res2);
                        if (isset($_GET['id'])) {
                            $this->display('mutualmoney');
                        } else{
                            $this->display('mutualstatus');
                        }
                    } else {
                        $this->display('mutualmoney');
                    }

                }
            }else{
                echo '<script>alert("此账户已失效！");history.go(-1)</script>';
            }
        } else {
            echo '<script>alert("参数错误");history.go(-1)</script>';
        }
    }

    /**
     * 规则详情页面
     */
    public function payruler()
    {
        if (isset($_GET['id']) && is_numeric($_GET['id'])){
            $ruler = $this->pdb->field('rulercontent')->find($_GET['id']);
            $content = $ruler['rulercontent'];
            $this->assign('title',"计划规则");
            $this->assign('content',$content);
            $this->display('article');
        } else {
            echo '<script>alert("参数错误");history.go(-1)</script>';
        }
    }


    /**
     * 其他详情页面
     */
    public function other()
    {
        if (isset($_GET['id']) && is_numeric($_GET['id'])){
            $article = M('article')->field('content')->find(52);
            $content = $article['content'];
            $this->assign('title',"平台公约");
            $this->assign('content',$content);
            $this->display('article');
        } else {
            echo '<script>alert("参数错误");history.go(-1)</script>';
            exit;
        }
    }

    /**
     * 常见问题页面
     */
    public function faq()
    {
        if (isset($_GET['id']) && is_numeric($_GET['id'])){
            $faq = $this->pdb->field('faq')->find($_GET['id']);
            $content = $faq['faq'];
            $this->assign('title',"常见问题");
            $this->assign('content',$content);
            $this->display('article');
        } else {
            echo '<script>alert("参数错误");history.go(-1)</script>';
            exit;
        }
    }

    /**
     * 常见问题页面
     */
    public function yueinfo()
    {
        if (isset($_GET['planid'])){
            $yue = $this->pdb->field('yueinfo')->find($_GET['planid']);
            $content = $yue['yueinfo'];
            $this->assign('title',"余额说明");
            $this->assign('content',$content);
            $this->display('article');
            // if ($content) {
            //     echo json_encode(array('code'=>2000, 'info'=>$content));
            // }else{
            //     echo json_encode(array('code'=>4000, 'err'=>''));;
            // }
        } else {
            // echo json_encode(array('code'=>4000, 'err'=>'参数错误'));;
            echo '<script>alert("参数错误");history.go(-1)</script>';
            // exit;
        }
    }
}