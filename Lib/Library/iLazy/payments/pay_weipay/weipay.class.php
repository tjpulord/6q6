<?php
namespace iLazy\payments\pay_weipay;

require_once "lib/WxPay.Api.php";
require_once "lib/WxPay.JsApiPay.php";
require_once 'lib/WxPay.log.php';
require_once 'lib/WxPay.Data.php';


use iLazy\paymentPlugin;
/**
 * @copyright Copyright(c) 2011 ljiayou.com
 * @file weipay.php
 * @brief 微支付
 * @author kane
 * @date 2015-12-02
 * @version 2015-12-02
 * @note
 */
/**
 * @class tenpay
 * @brief 微支付
 */
class weipay extends paymentPlugin
{
	//支付插件名称
    public $name = '微支付';

	/**
	 * @see paymentplugin::getSubmitUrl()
	 */
	public function getSubmitUrl()
	{
		return '支付提交的地址，有form提交动作时候有用';
	}

	/**
	 * @see paymentplugin::notifyStop()
	 */
	public function notifyStop()
	{
		$xml = '<xml>';
        $xml .= '<return_code><![CDATA[SUCCESS]]></return_code>';
        $xml .= '<return_msg><![CDATA[OK]]></return_msg>';
        $xml .= '</xml>';
		echo $xml;
	}

	/**
	 * 主动返回
	 * @see paymentplugin::callback()
	 */
	public function callback($callbackData,&$paymentId,&$money,&$message,&$orderNo)
	{

		$orderNo = $callbackData['order_no'];
		$money   = $callbackData['total_fee']/100;
		if(isset($callbackData['transaction_id'])){
            $this->recordTradeNo($orderNo,$callbackData['transaction_id']);
        }
		$message = isset($callbackData['pay_info']) ? $callbackData['pay_info'] : '';
		return true;
	}

	/**
	 * Server报文回调
	 * @see paymentplugin::serverCallback()
	 */
	public function serverCallback($callbackData,&$paymentId,&$money,&$message,&$orderNo)
	{
		$orderNo = $callbackData['out_trade_no'];
		$money   = $callbackData['total_fee']/100;
		if(isset($callbackData['transaction_id'])){
            $this->recordTradeNo($orderNo,$callbackData['transaction_id']);
        }
		$message = isset($callbackData['pay_info']) ? $callbackData['pay_info'] : '';
		return true;
	}

	/**
	 * @see paymentplugin::getSendData()
	 */
	public function getSendData($payment)
	{

        $sessioninput   = session("order") ? session("order") : array() ;
        if(array_key_exists("order_no", $sessioninput) && $sessioninput['order_no']== $payment['O_NUMBER'])
        {
           $return = $sessioninput;
        }
        else
        {
            $tools  = new \JsApiPay($payment);
            $openId = $tools->GetOpenid();
            $return = array();
            // $price  = ceil($payment['O_MONEY']);    // 支付 分
            $price  = ceil($payment['O_MONEY'] * 100);
            $return['total_fee']  = $payment['O_MONEY'];
            $return['order_no']   = $payment['O_NUMBER'];
            $return['return_url'] = $this->callbackUrl;
            $return['notify_url'] = $this->serverCallbackUrl;
            $return['attach']     = $payment['attach'];
            //②、统一下单
            $input = new \WxPayUnifiedOrder();
            //商户信息
            $input->SetAppid($payment['P_APPID']);          //支付ID
            $input->SetMch_id($payment['P_MCHID']);         //支付账号
            //产品信息
            $input->SetBody($payment['O_DETAILS']);
            $input->SetAttach($payment['attach']);
            $input->SetOut_trade_no($payment['O_NUMBER']);
            $input->SetTotal_fee($price);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 3600*24));
            //通知地址
            $notify_url = $this->serverCallbackUrl;
            $input->SetNotify_url($notify_url);
            //支付方式
            $input->SetTrade_type("JSAPI");
            //用户的ID
            $input->SetOpenid($openId);
            session("order",$input->getValue());
            $wxpayapi = new \WxPayApi($payment);
            $order = $wxpayapi::unifiedOrder($input);
            $jsApiParameters = $tools->GetJsApiParameters($order);
            $orderdata = json_decode($order,true);
            $return['jsApiParameters'] = $jsApiParameters;
            $return['notify_url']      = $this->callbackUrl;
            $return['prepay_id']       = str_replace("prepay_id=", "", $orderdata['package']);
            $return['order_name']      = $payment['O_DETAILS'];
            $return['order_no']        = $payment['O_NUMBER'];
            session("order",$return);
        }
		return $return;
	}


    /**
     * 扫码支付
     * @return [type] [description]
     */
    public function getNativeData($payment)
    {
    	// $price  = ceil($payment['O_MONEY']);	// 支付 分
		$price  = ceil($payment['O_MONEY'] * 100);
        //②、统一下单
        $input = new \WxPayUnifiedOrder();
        //商户信息
        $input->SetAppid($payment['P_APPID']);          //支付ID
        $input->SetMch_id($payment['P_MCHID']);         //支付账号
        //产品信息
        $input->SetBody($payment['O_DETAILS']);
        $input->SetAttach($payment['attach']);
        $input->SetOut_trade_no($payment['O_NUMBER']);
        $input->SetProduct_id($payment['O_PRODUCT']);
        $input->SetTotal_fee($price);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 3600*24));
        //通知地址
        $notify_url = $this->serverCallbackUrl;
        $input->SetNotify_url($notify_url);
        //支付方式
        $input->SetTrade_type("NATIVE");

        // 获取预支付id
        // 生成直接支付url，支付url有效期为2小时,模式二
        $wxpayapi = new \WxPayApi($payment);
        $order = $wxpayapi::unifiedOrder($input);
        return $order['code_url'];
    }

	/**
	 * 验证server返回的支付报文
	 * @param  [type] $xml [description]
	 * @return [type]      [description]
	 */
    public function checkxml($xml)
    {
        try {
			$result = \WxPayResults::Init($xml, true);
		} catch (WxPayException $e){
			$msg = $e->errorMessage();
			return false;
		}
		return $result;
    }

    // 验证server支付完成后的数据
    public function fromarray($value=array())
    {
    	try{
    		$payResult = new \WxPayResults();
	    	$result = $payResult::InitFromArray($value);
    	} catch (WxPayException $e){
			$msg = $e->errorMessage();
			return false;
		}
		return $result->GetValues();
    }

    /**
     * 处理支付报文后，回复server
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function replyxml($code='SUCCESS', $msg="OK")
    {
    	$reply = new \WxPayNotifyReply();
    	$reply->SetReturn_code($code);
    	$reply->SetReturn_msg($msg);
    	$this->notifyStop();

    }
}