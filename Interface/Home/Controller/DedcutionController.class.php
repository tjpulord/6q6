<?php
namespace Home\Controller;
use Think\Controller;

/**
 * @cc 互助计划批量扣款控制器类
 */
class DedcutionController {

    private $taskdb;    // 计划任务表
    private $logdb;     // 扣款日志表
    private $orderdb;   // 订单表
    private $userdb;    // 会员表
    private $plan_accountdb;    // 计划账户表

    const IDEAL   = 1;  // 空闲状态
    const RUNNING = 2;  // 工作状态

    public static $STATUS = IEDAL;

    function __construct()
    {
        $this->orderdb = M('order');
        $this->taskdb = M('plan_task');
        $this->userdb = M('weixin_user');
        $this->plan_accountdb = M('plan_account');
    }

    // 执行计划任务
    public function action()
    {
        if (DedcutionController::$STATUS == DedcutionController::RUNNING) {
            echo "programming is running";
            return false;
        }

        // 查找第一个未处理的计划任务
        $taskinfo = $this->taskdb->where('status=1')->order('id asc')->find();
        // var_dump($this->taskdb);
        if(!$taskinfo){ // 没有未处理订单，结束程序
            echo "programming end";
            return true;
        }
        // 修改计划任务的状态及订单状态
        // $taskinfo['status'] = 2;
        // $res = $this->taskdb->data($taskinfo)->where('id='.$taskinfo['id'])->save();


        // 修改程序运行状态
        DedcutionController::$STATUS = DedcutionController::RUNNING;

        // 开始处理数据
        $account_search = 'planid="'.$taskinfo['planid'].'" and addintime<'.strtotime($taskinfo['enddate']);
        $allcount = $this->plan_accountdb
                        ->where($account_search)
                        ->count();      // 总处理条数
        // echo "sql:".$this->plan_accountdb->getLastSql();
        // var_dump($this->plan_accountdb);
        $page = ceil($allcount/10000);
        echo "allcount:$allcount, page:$page";
        for ($i=0; $i < $page; $i++) {
            $touserlist = array();
            $accountlist = $this->plan_accountdb->where($account_search)
                                ->page($page,'10000')->select();
            foreach ($accountlist as $ak => $av) {
                // 获取会员信息
                $userinfo = $this->userdb->where('userid="'.$av['userid'].'"')->find();
                // 扣除计划账户互助款
                echo "cardnumber:".$av['cardnumber']."name:".$av['name']."<br>";
                $touserlist[$ak] = $userinfo['openid'];
                // 记录计划账户流水日志
                // 记录微信消息群发列表 touser
                sleep(1);
            }
            // 发送微信消息
        }
        // 记录
        echo "record";
        //
        DedcutionController::$STATUS = DedcutionController::IDEAL;
    }
}



$dtme = new DedcutionController();
$dtme->action();
