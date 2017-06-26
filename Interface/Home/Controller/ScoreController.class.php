<?php
namespace Home\Controller;
use Think\Controller;
class ScoreController extends BaseController {

    private $scoredb;   // 积分规则表
    private $logdb;     // 积分记录表
    private $goodsdb;   // 积分商品表
    private $accountdb; // 积分账户表

    function __construct()
    {
        parent::__construct();

        $this->scoredb   = M('score_ruler');
        $this->logdb     = M('score_log');
        $this->goodsdb   = M('score_goods');
        $this->accountdb = M('wallet');
    }

    /**
     * 筛选积分规则，增加积分
     */
    public function add()
    {
        // 判断接口必要参数
        $missed = $this->checkParam('userid', 'actionid');
        if ($missed) {
            $this->getError('缺少参数:'.$missed);
        }

        $userid   = $this->data['userid'];
        $actionid = $this->data['actionid'];

        $ruler_if = $this->scoredb->where(array('action'=>$actionid, 'status'=>1))->find();
        if (!$ruler_if) {
            $this->getError('积分规则不存在');
        }

        $endtime = strtotime($ruler_if['end_time']);
        if ($endtime<time() || strtotime($ruler_if['start_time'])>time()) {
            $this->getError('积分规则未生效/已失效');
        }

        // 判断积分是否达到上限
        if ($ruler_if['toplimit_period']) {
            // 积分周期内积分总值
            $scorelist = $this->logdb
                        ->where("userid='".$userid."' and rulerid=".$ruler_if['id']." and flow=0 and UNIX_TIMESTAMP(DATE_SUB(DATE(CURDATE()),INTERVAL ".$ruler_if['toplimit_period']." DAY))<addtime")
                        ->field("SUM(score) as sum_score")
                        ->select();
            $sum_score = 0;
            if ($scorelist) {
                $sum_score = $scorelist[0]['sum_score'];
            }
            if ($sum_score > ($ruler_if['toplimit']-$ruler_if['score'])) {  // 超出该积分规则上限
                $this->getError('已达该积分规则上限');
            }
        }elseif ($ruler_if['toplimit']) {
            $scorelist = $this->logdb
                        ->where("userid='".$userid."' and rulerid=".$ruler_if['id'])
                        ->select();
            if ($scorelist) {
                $this->getError('积分规则已使用，积分不再增加');
            }
        }

        // 增加积分
        $sql = "UPDATE td_wallet SET score = score+".$ruler_if['score']." WHERE userid='".$userid."'";
        $wres = $this->accountdb->execute($sql);
        // 记录积分日志
        $lres = $this->score_log($userid, $ruler_if['id'],0, $ruler_if['score']);

        if ($wres) {
            $this->getJson("获得:".$ruler_if['score']."积分");
        }else{
            $this->getError("积分获取失败");
        }
    }


    /**
     * 积分兑换
     * @return [type] [description]
     */
    public function payScore()
    {
        // 判断接口必要参数
        $missed = $this->checkParam('userid', 'goodsid', 'score');
        if ($missed) {
            $this->getError('缺少参数:'.$missed);
        }

        $userid  = $this->data['userid'];
        $goodsid = $this->data['goodsid'];
        $score   = $this->data['score'];

        $goods_info = $this->goodsdb->where('id='.$goodsid)->find();
        if ($goods_info) {
            // 减少积分
            $scoreinfo = $this->accountdb->where("userid='".$userid."'")->find();
            if ($scoreinfo['score'] < $score) {
                $this->getError('账户积分不足, 不能兑换');
            }
            $scoreinfo['score'] = $scoreinfo['score'] - $score;
            $scoreinfo['amount'] = $scoreinfo['amount']+ round($score/$goods_info['score'],2);
            // $sql = "UPDATE td_wallet SET score = score-".$score.", amount=amount+".$score/$goods_info['score']." WHERE userid='".$userid."'";
            $res = $this->accountdb->data($scoreinfo)->where("userid='".$userid."'")->save();
            // 钱包日志
            $wres = $this->walletlog($userid, round($score/$goods_info['score'],2), $scoreinfo['amount']);
            // 记录日志
            $lres = $this->score_log($userid, '', $goodsid, $score);
            if ($res) {
                $this->getJson('积分兑换成功');
            }else{
                $this->getError("积分兑换失败");
            }
        }else{
            $this->getError('未找到积分商品信息');
        }
    }


    // 积分兑换记录
    public function score_log($userid, $rulerid='', $goodsid='', $score=0, $desc="")
    {
        $lastlog = $this->logdb->where("userid='".$userid."'")->order('id desc')->find();
        $loginfo            = array();
        $loginfo['userid']  = $userid;
        $loginfo['rulerid'] = $rulerid;
        $loginfo['goodsid'] = $goodsid;
        $loginfo['addtime'] = time();

        if ($rulerid) {
            $ruler_info = $this->scoredb->where('id='.$rulerid)->find();

            $loginfo['score']     = $score;
            $loginfo['cur_score'] = $lastlog['cur_score'] + $score;
            $loginfo['acc_score'] = $lastlog['acc_score'] + $score;
            $loginfo['flow']      = 0;
            if ($desc) {
                $loginfo['content'] = $desc;
            }else{
                $loginfo['content'] = "您的账户于".date('Y年m月d日,H点i分').'进行'
                                    .$ruler_info['name']."操作,获得".$score
                                    ."积分,当前账户积分：".$loginfo['cur_score'];
            }

        }elseif ($goodsid) {
            $goods_info = $this->goodsdb->where('id='.$goodsid)->find();
            $loginfo['flow'] = 1;
            $loginfo['score']     = "-".$score;
            $loginfo['cur_score'] = $lastlog['cur_score'] - $score;
            $loginfo['acc_score'] = $lastlog['acc_score'];
            if ($desc) {
                $loginfo['content'] = $desc;
            }else{
                $loginfo['content'] = "您的账户于".date('Y年m月d日,H点i分').'使用积分进行'
                                    .$goods_info['name']."兑换,消费".$score."积分,当前账户积分："
                                    .$loginfo['cur_score'];
            }
        }else{
            return false;
        }
        // die();
        return $this->logdb->data($loginfo)->add();
    }


    /**
     * 钱包日志
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function walletlog($userid, $money, $account)
    {
        $wlog = M('fund_log');
        $content = "您的账户于".date('Y年m月d日,H点i分').'使用积分进行'
                                    .$goods_info['name']."兑换,收入".$money."元,余额".$account."元";

        $info = array('userid'=>$userid, 'money'=>$money, 'type'=>2, 'content'=>$content, 'addtime'=>time());
        $res = $wlog->data($info)->add();
    }
}