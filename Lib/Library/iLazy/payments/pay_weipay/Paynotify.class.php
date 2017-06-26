<?php
namespace iLazy\payments\pay_weipay;

require_once 'lib/WxPay.log.php';
require_once 'lib/WxPay.Notify.php';
require_once "lib/WxPay.Api.php";

class Paynotify extends WxPayNotify
{

	public $log;

	function __construct()
    {
        parent::__construct();
        $cloghandler = new CLogFileHandler('weipay.log');
        $this->log = new Log($cloghandler);
    }
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();

		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}
}