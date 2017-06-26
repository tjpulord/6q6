<?php
namespace Home\Controller;
use Think\Controller;

/**
  * wechat php test
  */

//define your token
define("TOKEN", "e41f8362499dd6d8082cb495f0e29b58");
$wechatObj = new IndexController();

if (isset($_GET['echostr'])) {
    $wechatObj->valid();
}else{
    $wechatObj->responseMsg();
}

class IndexController extends Controller
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        if ($echoStr) {
            if($this->checkSignature()){
                echo $echoStr;
                exit;
            }
        }else{
            $this->responseMsg();
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        // file_put_contents(dirname(__FILE__)."test.xml", $postStr);
        // $path = dirname(__FILE__)."\Controller20160812145838.xml";
        // $path = str_replace("\\Controller\\", "\\", $path);
        // $postStr = file_get_contents($path, true);
        // echo "$path";
        //extract post data
        if (!empty($postStr)){
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
               the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
            switch ($RX_TYPE) {
                case 'event':
                    $result = $this->receiveEvent($postObj);
                    break;

                default:
                    # code...
                    break;
            }
            echo $result;

            // $this->updateuser($postObj);
            exit;


        }else {
            echo "";
            exit;
        }
    }


    // 更新用户关注状态
    public function updateuser($info)
    {
        $dbuser = M('user');
        $dbusertempdb = M('weixin_user_temp');
        $oid = (string)($info->FromUserName);
        $usinfo = $dbuser->where(array('openid'=>$oid))->find();
        $tempuser = $dbusertempdb->where(array('openid'=>$oid))->find();
        if ($info->Event == "subscribe") {
            if ($usinfo) {
                $dbuser->where(array('openid'=>$oid))->data(array('issubscribe'=>1))->save();
            }
            if ($tempuser) {
                $dbusertempdb->data(array('openid'=>$oid, 'issubscribe'=>1))
                    ->where(array('openid'=>$oid))->save();
            }
            else{
                // 添加到用户临时表中
                $dbusertempdb->data(array('openid'=>$oid, 'issubscribe'=>1))->add();
            }
        }elseif ($info->Event == "unsubscribe") {
            $dbuser->where(array('openid'=>$oid))->data(array('issubscribe'=>0))->save();
            $dbusertempdb->where(array('openid'=>$oid))->data(array('issubscribe'=>0))->save();
        }
    }


    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }



    private function receiveEvent($value='')
    {
        $content = "欢迎关注我的公众号";
        switch ($value->Event) {
            case 'subscribe':
                $weconfigdb = M('weiconfig');
                $info = $weconfigdb->where('id=1')->find();
                if ($info) {
                    $content = $info['gzwd'];
                }
                break;

            default:
                # code...
                break;
        }
        return $this->trasmitText($value, $content);
    }


    private function trasmitText($postObj, $content)
    {
        $xmlTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                <FuncFlag>0</FuncFlag>
                </xml>";
        $result = sprintf($xmlTpl, $postObj->FromUserName, $postObj->ToUserName, time(), "text", $content);
        return $result;
    }
}