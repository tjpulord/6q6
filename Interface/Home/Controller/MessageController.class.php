<?php
namespace Home\Controller;
use Think\Controller;
use Home\Service\Wechat;

class MessageController extends BaseController {

	private $msgdb;
	/**
	 * 初始化加载
	 */
	function __construct()
	{
		parent::__construct();
		$this->msgdb = M('message');
	}

	/**
	 * 发送站内消息
	 * @return [type] [description]
	 */
	function sendmsg()
	{
		$missed = $this->checkParam('userid', 'content');
		if ($missed) {
			$this->getError('缺少参数:'.$missed);
		}

		$msginfo = array('userid'  => $this->data['userid'],
						 'title'   => $this->data['title'],
			             'content' => $this->data['content'],
			             'addtime' => time());

		$res = $this->msgdb->data($msginfo)->add();

		if ($res) {
			$this->getJson('ok');
		}else{
			$this->getError('发送失败', $msginfo);
		}
	}

	// 用户已读消息
	function readmsg()
	{
		$userid = $this->data['userid'];
		$msgid  = $this->data['msgid'];
		if ($userid) {
			if ($msgid) {	// 存在msgid，设置单条消息已读，否则设置该用户下所有消息已读
				$res = $this->msgdb->data(array('isread'=>1))
							->where(array('userid'=>$userid, 'id'=>$msgid))
							->save();
				if ($res) {
					$this->getJson("已标记 $msgid 已读");
				}else{
					$this->getError("标记 $msgid 已读失败");
				}
			}
			else
			{
				$res = $this->msgdb->data(array('isread'=>1))
							->where(array('userid'=>$userid))
							->save();
				if ($res) {
					$this->getJson("已标记该会员全部消息已读");
				}else{
					$this->getError("标记该会员全部消息已读失败");
				}
			}
		}else{
			$this->getError('没有会员id');
		}
	}

	// 用户删除消息
	function delmsg()
	{
		$missed = $this->checkParam('userid', 'msgid');
		if ($missed) {
			$this->getError('缺少参数:'.$missed);
		}

		$userid = $this->data['userid'];
		$msgid  = $this->data['msgid'];
		$res = $this->msgdb->data(array('isdel'=>1))
							->where(array('userid'=>$userid, 'id'=>$msgid))
							->save();
		if ($res) {
			$this->getJson("已标记 $msgid 删除");
		}else{
			$this->getError("标记 $msgid 删除失败");
		}
	}


	/**
	 * @cc 发送微信消息
	 * @return [type] [description]
	 */
	public function weimsg()
	{
		$missed = $this->checkParam('openid', 'status');
		if ($missed) {
			$this->getError('缺少参数:'.$missed);
		}

		$wxcfg_db  = M('weiconfig');
		$weixin_config = $wxcfg_db->order('id desc')->find();
        if($weixin_config){
            $options = array(
                   'token'          => $weixin_config['wx_token'], //填写你设定的key
                   'encodingaeskey' => $weixin_config['wx_appAESKey'], //填写加密用的EncodingAESKey
                   'appid'          => $weixin_config['wx_appid'], //填写高级调用功能的app id
                   'appsecret'      => $weixin_config['wx_appsct'] //填写高级调用功能的密钥
            );
			$weObj   = new Wechat($options);
        }else{
        	$this->getError("微信公共平台信息错误");
        }

		// $tousers = str_replace(array('[',']','\'',' '), array('','','',''),$tousers);
		// $tos = explode(",", $tousers);
        $postdata = $this->data;
		$status = $postdata['status'];
		$openid = $postdata['openid'];

		// $res = $weObj->setTMIndustry("9", "41");
		// $result = $weObj->getTMIndustry();
		// $result = $weObj->getTMList();
		$colorary = array("#173177","#992211","#FF3366", "#666666", "#999999");
		$content = array('first'=>array("value"=>$postdata['first'],"color"=>$colorary[$status]),
						'keyword1'=>array("value"=>$postdata['username'],"color"=>$colorary[0]),
						'keyword2'=>array("value"=>$postdata["planname"],"color"=>$colorary[0]),
						'keyword3'=>array("value"=>$postdata['money']."元","color"=>$colorary[0]),
						'keyword4'=>array("value"=>$postdata["umber"],"color"=>$colorary[0]),
						'keyword5'=>array("value"=>$postdata["allmoney"],"color"=>$colorary[0]),
						'remark'=>array("value"=>"感谢您的参与，点击详情查看本期扣款内容","color"=>$colorary[0]));
		$msgdata = array("touser"=>$postdata['openid'],
						"template_id"=>"6qgWU--8wZhjgyxDrUZmj3B4cXhOXPTxmbRGUYsLRhY",
						'url'=>"www.tongdanghuzhu.com/weixin.php?s=/home/Account/accountlist.html",
						"data"=>$content);
		$result = $weObj->sendTemplateMessage($msgdata);
		if ($result) {
			// var_dump($result);
			$this->getJson('ok', $result);
		}
		else{

			$this->getError("通知消息发送失败:code=".$weObj->errCode, $weObj->errMsg);
		}
	}
}