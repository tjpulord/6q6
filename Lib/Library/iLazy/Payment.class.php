<?php
namespace iLazy;
/**
 * @copyright Copyright(c) 2011 jooyea.cn
 * @file payment.php
 * @brief 支付方式 操作类
 * @author kane
 * @date 2011-01-20
 * @version 0.6
 * @note
 */

/**
 * @class Payment
 * @brief 支付方式 操作类
 */
//支付状态：支付失败
define ( "PAY_FAILED", - 1);
//支付状态：支付超时
define ( "PAY_TIMEOUT", 0);
//支付状态：支付成功
define ( "PAY_SUCCESS", 1);
//支付状态：支付取消
define ( "PAY_CANCEL", 2);
//支付状态：支付错误
define ( "PAY_ERROR", 3);
//支付状态：支付进行
define ( "PAY_PROGRESS", 4);
//支付状态：支付无效
define ( "PAY_INVALID", 5);

class Payment
{
    private static $user_id=0;

    private static $payment_id = 0;

    private static $paymentClass = null;

    public function __construct($payment_id)
    {
        self::$payment_id = $payment_id;
    }


    /**
     * @brief 创建支付类实例
     * @param $payment_id int 支付方式ID   来源：从数据库的支付方式里面读取的
     * @return 返回支付插件类对象
     */
    public static function createPaymentInstance()
    {
        //step1:判断支付类是否存在
        $paymentRow = self::getPaymentById();
        if(!isset($paymentRow))
        {
            echo json_encode(array('code'=>403, 'msg'=>'支付方式不存在'));
            die();
        }
        //step2:初始化类的信息
        if(isset($paymentRow['class_name']) && $paymentRow['class_name'])
        {

            $class_name = $paymentRow['class_name'];    //根据支付的名称在对应的文件夹内部组成全部路径
            $classPath = dirname(__FILE__)."/payments/pay_".$class_name."/".$class_name.'.class.php';
            if(file_exists($classPath))
            {
                return new \iLazy\payments\pay_weipay\weipay(self::$payment_id);
            }
            else
            {
                echo json_encode(array('code'=>404, 'msg'=>'支付接口不存在'));
                die();
            }
        }
    }


    /**
     * @brief 根据支付方式配置编号  获取该插件的详细配置信息
     * @param $payment_id int    支付方式ID
     * @param $key        string 字段
     * @return 返回支付插件类对象
     */
    public static function getPaymentById()
    {
        $paymentDB  = M('payment');
        $paymentRow = $paymentDB->where('id = '.self::$payment_id)->find();
        if($paymentRow)
        {
            return $paymentRow;
        }
    }

    /**
     * @brief 获取订单中的支付信息 M:必要信息; R表示店铺; P表示用户;
     * @param $payment_id int    支付方式ID
     * @param $type       string 信息获取方式 order:订单支付;recharge:在线充值;
     * @param $argument   mix    参数
     * @return array 支付提交信息
     */
    public static function getPaymentInfo($orderdata,$paymentRow)
    {

        //最终返回值
        $payment = $orderdata;
        //支付的设置信息
        $payment['P_APPID']       = $paymentRow['p_appid'];             //支付ID  详见Wxpay.config.php
        $payment['P_MCHID']       = $paymentRow['p_mchid'];             //支付秘钥
        $payment['P_KEY']         = $paymentRow['p_key'];               //支付账号
        $payment['P_APPSECRET']   = $paymentRow['p_appsecret'];         //支付账号
        $payment['P_PAYMENTID']   = self::$payment_id;                  //支付方式的ID
        //订单的相关信息
        // $payment['O_NUMBER']      = $orderdata['ordernum'];             //订单号码
        // $payment['O_MONEY']       = $orderdata['ordermoney'];           //订单金额
        // $payment['O_DETAILS']     = $orderdata['orderdetails'];         //订单描述
        // $payment['O_DESCRIPTION'] = $orderdata['orderdescription'];     //商品详情
        return $payment;
    }

    /**
     * 生成订单号
     * @return [type] [description]
     */
    private static function createOrderNum()
    {
        return date("YmdHis",time()).rand(1000,9999);
    }


}