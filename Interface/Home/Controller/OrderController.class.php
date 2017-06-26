<?php
namespace Home\Controller;
use Think\Controller;

class OrderController extends BaseController {
	private $orderdb, $goodsdb;
	/**
	 * 初始化加载
	 */
	function __construct()
	{
		parent::__construct();
		$this->orderdb = M('order');
		$this->goodsdb  = M('goods');
	}

	/**
	 * @cc 加入计划下单接口
	 * @return [type] [description]
	 */
	function createorder()
	{
		$missed = $this->checkParam('userid', 'goodsid', 'number');
		if ($missed) {
			$this->getError('缺少参数:'.$missed);
		}

		$userid  = $this->data['userid'];
		$goodsid = $this->data['goodsid'];
		$number  = $this->data['number'];
		//判断该用户是否有未支付订单
		$order_if = $this->orderdb
					->where(array('userid'=>$userid, 'goodsid'=>$goodsid, 'number'=>$number, 'pay_status'=>0, 'status'=>1))
					->find();
		if ($order_if) {
			$this->getJson('返回未支付订单', $order_if);
		}

		// 生成新订单
		// 作废该用户该计划下的其他未支付订单
		$this->orderdb->startTrans();	//开启事务模式
		$where = array('userid'=>$userid, 'goodsid'=>$goodsid, 'pay_status'=>0, 'status'=>1);
		if ($this->orderdb->where($where)->select()) {
			$zf = $this->orderdb->data(array('status'=>3))
						->where($where)
						->save();
		}else{
			$zf = true;
		}
		$xd = false;
		$order = $this->data;
		// 订单号
		$order['orderno']     = "sy_".date('mdHi').substr(md5(uniqid(mt_rand(), true)), 24);
		$order['create_time'] = date('Y-m-d H:i:s', time());

		// 计算订单金额
		$goods = $this->goodsdb->where(array('id'=>$goodsid))->find();
		if ($goods) {
			$order['price']        = $goods['price'];
			$order['number']       = $number;
			$order['goods_amount'] = $number * $goods['price'];
			$order['fare']         = $goods['post_fee'];
			$order['pay_amount']   = $order['goods_amount'] + $goods['post_fee'];
			// 订单描述内容
			$order['detail']       = "购买商品：".$goods['name']."(".$goods['description'].")";
		}else{
			$this->orderdb->rollback();
			$this->getError('找不到商品信息');
		}

		// 计算积分
		// $rulerid = 0;
		// $order['point'] = $this->getScore(1, $userid, $rulerid);
		// $order['pointid'] = $rulerid;
		// 设置订单类型、支付状态、订单状态等
		$order['pay_status'] = 0;
		$order['status']     = 1;
		// 存储订单信息
		$xd = $this->orderdb->data($order)->add();

		// 提交 or 回滚
		if ($zf && $xd) {
			$this->orderdb->commit();
			$this->getJson('下单成功', $order);
		}else{
			$this->orderdb->rollback();
			$this->getError('下单失败');
		}

	}


	/**
	 * 删除订单
	 * @return [type] [description]
	 */
	function delorder(){
		$missed = $this->checkParam('userid', 'orderno');
		if ($missed) {
			$this->getError('缺少参数:'.$missed);
		}
		$res = $this->orderdb
					->where(array('userid'=>$this->data['userid'], 'orderno'=>$this->data['orderno']))
					->data(array('if_del'=>1))
					->save();

		// echo "$res - sql:".$this->orderdb->getLastSql();
		if ($res) {
			$this->getJson("用户删除订单成功");
		}else{
			$this->getError('订单删除失败');
		}
	}


	/**
	 * 获取订单状态
	 * @return [type] [description]
	 */
	public function getstatus()
	{
		if (!$this->data['orderno']) {
			$this->getError('缺少订单参数');
		}

		$orderno = $this->data['orderno'];
		$res = $this->orderdb->where(array('orderno'=>$orderno))->find();

		if ($res && $res['status']) {
			$this->getJson('ok', $res['status']);
		}else{
			$this->getError('获取订单失败');
		}
	}

	/**
	 * 更新订单状态
	 * @return [type] [description]
	 */
	public function updateorder()
	{
		$missed = $this->checkParam('status', 'orderno');
		if ($missed) {
			$this->getError('缺少参数:'.$missed);
		}
		$userinfo = cookie('WUSER');
		$orderno  = $this->data['orderno'];
		$status = $this->data['status'];

		$where = array('userid'=>$userinfo['id'], 'orderno'=>$orderno);
		$orderinfo = $this->orderdb
					->where($where)
					->find();
		if ($orderinfo && $orderinfo['status']!=$status) {
			$res = $this->orderdb->where($where)
					->data(array('status'=>$status))
					->save();
			if ($res) {
				$this->getJson('ok');
			}else{
				$this->getError('订单状态更新失败');
			}
		}else{
			$this->getError('订单已更新');
		}
	}


	/**
	 * 更新订单状态, 收货
	 * @return [type] [description]
	 */
	public function receivegoods()
	{
		if (!$this->data['orderno']) {
			$this->error('缺少订单参数:');
		}
		$userinfo = cookie('WUSER');
		$orderno  = $this->data['orderno'];
		$status   = 5;

		$where = array('orderno'=>$orderno, 'status'=>$status);
		$orderinfo = $this->orderdb
					->where($where)
					->find();
		if ($orderinfo && $orderinfo['orderno']) {

			$this->orderdb->startTrans();	// 开启
			$res = $this->orderdb->where($where)
					->data(array('status'=>6))
					->save();
			$ress = false;
			if ($res) {
				$walletdb = M('wallet');
				$winfo = $walletdb->where(array('userid'=>$userinfo['id']))
						->find();

				$ress = $walletdb->where(array('userid'=>$userinfo['id']))
							->data(array('freeze'=>$winfo['freeze']+$orderinfo['pay_amount']))
							->save();
			}

			if ($res && $ress) {
				$this->orderdb->commit();
				$this->getJson('ok');
			}else{
				$this->orderdb->rollback();
				$this->getError('订单状态更新失败');
			}
		}else{
			$this->getError('订单已更新');
		}
	}

	/**
	 * 根据actionid， 获取积分
	 * @param  [type] $actionid [description]
	 * @param  [type] $userid   [description]
	 * @param  [int]  &$ret;	变量引用，返回积分规则id
	 * @return [type]           [description]
	 */
	public function getscore($actionid, $userid, &$ret)
	{
		$ruler_if = $this->scoredb->where(array('action'=>$actionid, 'status'=>1))->find();
        if (!$ruler_if) {
            return 0;
        }

        $endtime = strtotime($ruler_if['end_time']);
        $starttime= strtotime($ruler_if['start_time']);
        if ($endtime<time() || $starttime >time()) {
            return 0;
        }

        // 判断积分是否达到上限
        $scorelogdb = M('score_log');
        if ($ruler_if['toplimit_period']) {
            // 积分周期内积分总值
            $scorelist = $scorelogdb
                        ->where("userid='".$userid."' and rulerid=".$ruler_if['id']." and flow=0 and UNIX_TIMESTAMP(DATE_SUB(DATE(CURDATE()),INTERVAL ".$ruler_if['toplimit_period']." DAY))")
                        ->field("SUM(score) as sum_score")
                        ->select();
            $sum_score = 0;
            if ($scorelist) {
                $sum_score = $scorelist[0]['sum_score'];
            }
            if ($sum_score > ($ruler_if['toplimit']-$ruler_if['score'])) {  // 超出该积分规则上限
                return 0;
            }
        }elseif ($ruler_if['toplimit']) {
            $scorelist = $scorelogdb
                        ->where("userid='".$userid."' and rulerid=".$ruler_if['id'])
                        ->select();
            if ($scorelist) {
                return 0;
            }
        }
        $ret = $ruler_if['id'];
        return $ruler_if['score'];

	}

}