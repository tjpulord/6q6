<?php
namespace Home\Controller;
use Think\Controller;
class PayController extends BaseController {

    private $orderdb;   // 订单表
    function __construct()
    {
        parent::__construct();
        $this->orderdb = M('order');
    }

    /**
     * 订单支付接口
     */
    public function payorder()
    {
        $missed = $this->checkParam('orderid');
        if ($missed) {
            $this->getError('缺少参数:'.$missed);
        }

        $orderid   = $this->data['orderid'];
        // 订单信息
        $orderinfo = $this->orderdb->where(array('orderno'=>$orderid, 'status'=>1))->find();
        // var_dump($this->orderdb);
        // echo "string".$this->orderdb->getLastSql();
        // die();
        if (!$orderinfo) {
            $this->error('', '/index.php?s=/home/MyCenter/index.html', 1, 0);
            // $this->getError('找不到订单信息');
            die();
        }

        $this->assign('orderinfo',$orderinfo);
        // 支付方式为钱包支付，则跳转钱包支付页面
        if ($orderinfo['pay_type'] == 2) {
            $this->display('wallet_pay');
            return;
        }

        // 开始微信支付,调用微支付类
        // $paymentid = $this->data['paymentid']?$this->data['paymentid']:1;
        // $payment     = new \iLazy\Payment($paymentid);
        // $paymentplus = $payment::createPaymentInstance();
        // $paymentRow  = $payment::getPaymentById();
        // $sendData    = array();

        // //订单类型，1：计划购买；2：计划充值；3：计划扣款；4：捐赠；5：账户充值
        // switch ($orderinfo['type']) {
        //     case '1':
        //     case '2':
        //     case '4':
        //     case '5':
        //         $data        = array();//订单的全部信息
        //         $data['O_NUMBER']      = $orderid;
        //         $data['O_MONEY']       = $orderinfo['payable_amount'];
        //         $data['O_DETAILS']     = $orderinfo['details'];
        //         $data['attach']        = $userid;
        //         $paymentInfo = $payment::getPaymentInfo($data,$paymentRow);
        //         $sendData    = $paymentplus->getSendData($paymentInfo);
        //         break;
        //     case '3':
        //         // $data        = array();//订单的全部信息
        //         // $data['O_NUMBER']      = $orderid;
        //         // $data['O_MONEY']       = $orderinfo['total'];
        //         // $data['O_DETAILS']     = $orderinfo['details'];

        //         // $paymentInfo = $payment::getPaymentInfo($data,$paymentRow);
        //         // $sendData    = $paymentplus->getSendData($paymentInfo);
        //         $this->display('koukuan');
        //         return;
        //         break;
        //     default:
        //         # code...
        //         break;
        // }

        if(!empty($sendData))
        {
            $this->assign('sendData',$sendData);
        }
        else
        {
            //没有任何的支付信息
        }
        $this->paybywallet();
        $this->display('pay');
    }


    // 扫码支付
    public function navtiepay()
    {

        $missed = $this->checkParam('userid', 'orderid');
        if ($missed) {
            $this->getError('缺少参数:'.$missed);
        }

        $userid    = $this->data['userid'];
        $orderid   = $this->data['orderid'];
        // 订单信息
        $orderinfo = $this->orderdb->where(array('userid'=>$userid, 'order_no'=>$orderid, 'status'=>1))->find();
        // var_dump($this->orderdb);
        // echo "string".$this->orderdb->getLastSql();
        // die();
        if (!$orderinfo) {
            $this->getError('找不到订单信息');
        }


        // 开始微信支付,调用微信扫码支付
        $paymentid   = $this->data['paymentid']?$this->data['paymentid']:1;
        $payment     = new \iLazy\Payment($paymentid);
        $paymentplus = $payment::createPaymentInstance();
        $paymentRow  = $payment::getPaymentById();
        $sendData    = array();

        if ($orderinfo['type'] != 1) {
            $this->getError('订单类型错误:'.$orderinfo['type']);
        }


        $data        = array();//订单的全部信息
        $data['O_NUMBER']      = $orderid;
        $data['O_MONEY']       = $orderinfo['payable_amount'];
        $data['O_DETAILS']     = $orderinfo['details'];
        $data['O_PRODUCT']     = $orderinfo['planid'];
        $data['attach']        = $userid;
        $paymentInfo = $payment::getPaymentInfo($data,$paymentRow);
        // var_dump($orderinfo);
        $code_url    = $paymentplus->getNativeData($paymentInfo);

        $this->getJson('ok', $code_url);
    }



    // 钱包支付
    public function paybywallet()
    {
        $walletdb = M('wallet');
        $userid = $this->data['userid'];
        $info = $walletdb->where(array('userid'=>$userid))->find();
        $this->assign('amount',$info['account']);
    }
}