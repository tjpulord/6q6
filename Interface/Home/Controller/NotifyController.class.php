<?php
namespace Home\Controller;
use Think\Controller;

class NotifyController extends Controller
{
	private $data;
	// //查询订单
	// public function Queryorder($transaction_id)
	// {
	// 	$input = new WxPayOrderQuery();
	// 	$input->SetTransaction_id($transaction_id);
	// 	$result = WxPayApi::orderQuery($input);
	// 	$this->log::DEBUG("query:" . json_encode($result));
	// 	if(array_key_exists("return_code", $result)
	// 		&& array_key_exists("result_code", $result)
	// 		&& $result["return_code"] == "SUCCESS"
	// 		&& $result["result_code"] == "SUCCESS")
	// 	{
	// 		return true;
	// 	}
	// 	return false;
	// }

	// //重写回调处理函数
	// public function NotifyProcess($data, &$msg)
	// {
	// 	$this->log::DEBUG("call back:" . json_encode($data));
	// 	$notfiyOutput = array();

	// 	if(!array_key_exists("transaction_id", $data)){
	// 		$msg = "输入参数不正确";
	// 		return false;
	// 	}
	// 	//查询订单，判断订单真实性
	// 	if(!$this->Queryorder($data["transaction_id"])){
	// 		$msg = "订单查询失败";
	// 		return false;
	// 	}
	// 	return true;
	// }

	/**
	 * 直接回调
	 * @return function [description]
	 */
	function callback()
	{
		$path = dirname(__FILE__)."\Controller20160825153713.xml";
		$path = str_replace("\\Controller\\", "\\", $path);
		$xml = file_get_contents($path, true);
		$paymentid   = $_GET['paymentid']?$_GET['paymentid']:1;
		$payment     = new \iLazy\Payment($paymentid);
		$paymentplus = $payment::createPaymentInstance();
		$result      = $paymentplus->checkxml($xml);
		if ($result) {
			echo $result['out_trade_no']."<br>";
			$orderinfo = M('order')->where(array('orderno'=>$result['out_trade_no']))->find();
			echo $orderinfo['userid']." -- ".$orderinfo['pointid'];
			$this->updaterecommendscore($orderinfo['userid'], $orderinfo['pointid']);
		}
		die();

		$datas = array_merge($_GET,$_POST);
		var_dump($_GET);
		$datas['data'] = str_replace("'", "\"", $datas['data']);
		var_dump($datas['data']);
		$data= json_decode($datas['data'], true);

		var_dump($data);
		if ($data) {
			// 开始微信支付,调用微支付类
			$paymentid   = $_GET['paymentid']?$_GET['paymentid']:1;
			$payment     = new \iLazy\Payment($paymentid);
			$paymentplus = $payment::createPaymentInstance();
			$result      = $paymentplus->fromarray($data);
			file_put_contents(dirname(__FILE__).date("YmdHis",time()).".xml", json_encode($result));
	        if ($result) {
	        	// 更新订单状态
	        	$res = $this->updateorder($result);
	        	if ($res) {
	        		echo json_encode(array('code'=>2000, 'msg'=>'ok', 'data'=>$_POST['url']));
	        	}else{
	        		echo json_encode(array('code'=>4000, 'msg'=>"验签失败"));
	        	}
	        }
		}
	}

	/**
	 * 报文回调
	 * @return [type] [description]
	 */
	function servercallback()
	{
		//获取通知的数据
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		file_put_contents(dirname(__FILE__).date("YmdHis",time()).".xml", $xml);
		// $path = dirname(__FILE__)."\Controller20160806001140.xml";
		// $path = str_replace("\\Controller\\", "\\", $path);
  //       // 开始微信支付,调用微支付类
  //       $xml = file_get_contents($path, true);
		$paymentid   = $_GET['paymentid']?$_GET['paymentid']:1;
		$payment     = new \iLazy\Payment($paymentid);
		$paymentplus = $payment::createPaymentInstance();
		$result      = $paymentplus->checkxml($xml);
        if ($result) {
  			// 接收到报文后，将订单状态修改为2确认状态
  			$orderdb = M('order');
			$orderinfo = $orderdb->data(array('status'=>2))
						->where("orderno='".$result['out_trade_no']."'")->save();
        	// 更新订单状态
        	$res = $this->updateorder($result);
        	if ($res) {
        		$paymentplus->replyxml();
        	}
        }
	}



	private function updateorder($result)
	{
		$orderdb = M('order');
		$orderinfo = $orderdb->where("orderno='".$result['out_trade_no']."'")->find();
		if ($orderinfo['pay_status'] == 1) {
			return true;
		}

		switch ($orderinfo['type']) {
			case '1':	// 购买商品
				// 开始事务
				$orderdb->startTrans();
				$saveflag = true;
				// 更新积分
				// if ($result['transaction_id']) {
				// 	$res = $this->updatescore($orderinfo['userid'], $orderinfo['pointid'],0, $orderinfo['point']);
				// 	// 二级分销，推荐人获得积分
				// 	$this->updaterecommendscore($orderinfo['userid'], $orderinfo['pointid']);
				// }
				// 更新订单信息
				$updata = array('pay_status'=>1, 'status'=>5,
								'real_amount'=>$result['total_fee']/100,
								'pay_time'=>$result['time_end'],
								'trade_no'=>$result['transaction_id']);
				$res = $orderdb->data($updata)->where("orderno='".$result['out_trade_no']."'")->save();
				$saveflag = $res?$saveflag:false;

				if ($saveflag) {
					$orderdb->commit();
					return true;
				}else{
					$orderdb->rollback();	// 数据回滚
					return false;
				}

				break;

			case "2":	// 账户充值
				$threshold = M('account_threshold');
				$invalid = $threshold->where("name='account_invalid'")->find();
				$invalid = $invalid['value'];
				$normal = $threshold->where("name='account_normal'")->find();
				$normal = $normal['value'];

				$accountinfo = $planaccountdb->where("id=".$orderinfo['planid'])->find();
				if ($accountinfo['status'] == 3) {
					$accountinfo['addintime'] = time();
				}
				$accountinfo['amount'] = $accountinfo['amount'] + $orderinfo['pay_amount'];

				if ($accountinfo['amount'] < $invalid) {
					$accountinfo['status'] = 3;
				}elseif ($accountinfo['amount'] < $normal) {
					$accountinfo['status'] = 2;
				}else{
					$accountinfo['status'] = 1;
				}
				$res = $planaccountdb->data($accountinfo)->where("id=".$orderinfo['planid'])->save();
				// 写日志
				$this->savelog($orderinfo['planid'], "计划账户充值".$orderinfo['pay_amount']."元", $orderinfo['pay_amount']);


				// 更新订单
				if ($res) {
					$updata = array('pay_status'=>1, 'status'=>5,
								'real_amount'=>$result['total_fee']/100,
								'pay_time'=>$result['time_end'],
								'trade_no'=>$result['transaction_id']);
					$res = $orderdb->data($updata)->where("orderno='".$result['out_trade_no']."'")->save();
				}
				return $res;

				break;
			case "4":	// 捐赠
				$donatedb = M('donation');
				$donatedb->startTrans();//开启事务
				$saveflag = true;
				$info = array('userid'=>$orderinfo['userid'],
							 'socialid'=>$orderinfo['planid']);
				$donainfo = $donatedb->where($info)->find();
				if ($donainfo) {
					$info['money'] = $donainfo['money'] + $orderinfo['pay_amount'];
					$res = $donatedb->data($info)->where('id='.$donainfo['id'])->save();
				}else{
					$info['money'] = $orderinfo['pay_amount'];
					$info['addtime'] = time();
					$res = $donatedb->data($info)->add();
				}
				$saveflag = $res?$saveflag:false;
				// 更新关爱表 已筹金额
				$socialdb = M('social');
				$res = $socialdb->where("id=".$orderinfo['planid'])
						->save(array('funds'=> array('exp', 'funds+'.$orderinfo['pay_amount'])));
				$saveflag = $res?$saveflag:false;
				// 更新积分
				if ($result['transaction_id']) {
					$res = $this->updatescore($orderinfo['userid'], $orderinfo['pointid'], 0, $orderinfo['point']);
				}
				// 更新订单
				if ($res) {
					$updata = array('pay_status'=>1, 'status'=>5,
								'real_amount'=>$result['total_fee']/100,
								'pay_time'=>$result['time_end'],
								'trade_no'=>$result['transaction_id']);
					$res = $orderdb->data($updata)->where("orderno='".$result['out_trade_no']."'")->save();
				}
				$saveflag = $res?$saveflag:false;
				if ($saveflag) {
					$donatedb->commit();
				}else{
					$donatedb->rollback();
					return false;
				}
				return $res;
				break;
			case "5":	// 账户充值
				$res = $this->updatescore($orderinfo['userid'], $orderinfo['pointid'], $orderinfo['pay_amount'], $orderinfo['point']);
				// $where = "userid='".$orderinfo['userid']."'";
				// $info = $walletdb->where($where)->find();
				// $info['amonut'] = $info['amount'] + $orderinfo['pay_amount'];
				// $info['score']  = $info['score'] + $orderinfo['point'];	// 更新积分
				// $res = $walletdb->data($info)->where($where)->save();

				// 更新订单
				if ($res) {
					$updata = array('pay_status'=>1, 'status'=>5,
								'real_amount'=>$result['total_fee']/100,
								'pay_time'=>$result['time_end'],
								'trade_no'=>$result['transaction_id']);
					$res = $orderdb->data($updata)->where("orderno='".$result['out_trade_no']."'")->save();
				}


				return $res;
				break;

			default:
				break;
		}
		return false;
	}


	// 计划账户日志
	private function savelog($cid, $title, $money, $flow="0")
	{
		// 写日志
		$accountlogdb = M('account_log');
		$loginfo = array('planaccountid'=>$cid, 'title'=>$title, 'trade'=>$money, 'flow'=>$flow, 'addtime'=>time());
		$rees = $accountlogdb->data($loginfo)->add();
		return $rees;
	}

	private function walletlog($userid, $title, $money, $curmoney, $flow=1){
		$fundlogdb = M('fund_log');

		$loginfo = array('userid'=>$userid, 'money'=>$money,
					'type'=>$flow, 'addtime'=>time(),
					'content'=>"您的账户于".date("Y年m月d日H:i:s, ".$title.$money."元,余额".$curmoney."元"));
		return $fundlogdb->data($loginfo)->add();
	}


	// 更新账户积分\余额
	public function updatescore($userid, $rulerid, $money=0, $score=0)
	{
		if ($money ==0 && $score == 0) {
			return true;
		}
		$walletdb = M('wallet');
		// 更新积分
		$where = "userid='".$userid."'";
		$info = $walletdb->where($where)->find();
		$info['amount'] = $info['amount'] + $money;
		$info['score']  = $info['score'] + $score;	// 更新积分
		$res = $walletdb->data($info)->where($where)->save();

		// 更新积分记录日志
		if ($score > 0) {
			$scorelog = new ScoreController();
			$scorelog->score_log($userid, $rulerid, '', $score);
		}
		// 更新钱包日志
		if ($res && $money!=0) {
			$this->walletlog($userid, "账户充值", $money, $info['amount'], 2);
		}
		return $res;
	}


	// 推荐人获得积分
	function updaterecommendscore($userid, $rulerid)
	{
		$userdb    = M('weixin_user');
		$walletdb  = M('wallet');
		$rulerdb   = M('score_ruler');
		$scorelog  = new ScoreController();
		$rulerinfo = $rulerdb->find($rulerid);

		$where = array('id'=>$userid);
		if (!$rulerinfo || strtotime($rulerinfo['end_time']<time() || strtotime($rulerinfo['start_time'])>time() || $rulerinfo['status']==0)) {
			$userdb->data("referenceflag=0")->where($where)->save();
			return ;
		}
		$userinfo = $userdb->where($where)->find();
		if ($userinfo && $userinfo['referenceid'] && $userinfo['referenceflag']) {
			$walletdb->where(array('userid'=>$userinfo['referenceid']))
					->save(array('score'=> array('exp', 'score+'.$rulerinfo['recommend1'])));
			$username = $userinfo['name']?$userinfo['name']:$userinfo['nickname'];
			$logtxt = "您推荐".$username."购买互助计划，获得".$rulerinfo['recommend1']."积分。";
			$scorelog->score_log($userinfo['referenceid'], $rulerid, '', $rulerinfo['recommend1'],$logtxt);
			$userdb->data("referenceflag=0")->where($where)->save();
			// 二级推荐人获得积分
			$where['id'] = $userinfo['referenceid'];
			$userinfo = $userdb->where($where)->find();
			if ($userinfo && $userinfo['referenceid']) {
				$walletdb->where(array('userid'=>$userinfo['referenceid']))
						->save(array('score'=> array('exp', 'score+'.$rulerinfo['recommend2'])));
				$username = $userinfo['name']?$userinfo['name']:$userinfo['nickname'];
				$logtxt = "您推荐会员的下线会员购买互助计划，获得".$rulerinfo['recommend2']."积分。";
				$scorelog->score_log($userinfo['referenceid'], $rulerid, '', $rulerinfo['recommend2'],$logtxt);
			}
		}
	}


	// 钱包支付
    public function paybywallet()
    {
    	$data = array_merge($_GET,$_POST);
        $orderno = $data['orderno'];
        if (!$orderno) {
            echo json_encode(array('code'=>'4000', 'msg'=>"缺少订单参数"));
            die();
        }

		$orderdb = M('order');
		// $orderinfo = $orderdb->data(array('status'=>2, 'pay_type'=>2))
		// 			->where("orderno='".$result['out_trade_no']."'")->save();
		$orderinfo = $orderdb->where("orderno='".$orderno."'")->find();

		if ($orderinfo['pay_status'] == 1) {
			return true;
		}
		// 组合支付结果
		$result = array('out_trade_no'=>$orderno,
						'total_fee' => ceil($orderinfo['pay_amount']*100),
						'transaction_id'=>"",
						'time_end'  => date("YmdHis", time()));

		$res = $this->updateorder($result);
		if ($res) {
			$orderdb->data(array('pay_type'=>2))->where("orderno='".$orderno."'")->save();

			// 更新钱包余额
			$walletdb = M('wallet');
			$where = array('userid'=>$orderinfo['userid']);
			$winfo = $walletdb->where($where)->find();
			$winfo['account'] = $winfo['account'] - $orderinfo['pay_amount'];
			$res = $walletdb->data($winfo)->where($where)->save();
			if ($res) {
				$this->walletlog($orderinfo['userid'], $orderinfo['details']."钱包支付", $orderinfo['pay_amount'], $winfo['amount']);
			}

			//关闭微支付订单
			echo json_encode(array('code'=>'2000', 'msg'=>"ok"));
		}else{
			echo json_encode(array('code'=>'4000', 'msg'=>"支付失败"));
		}
    }
}