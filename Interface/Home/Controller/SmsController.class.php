<?php
namespace Home\Controller;
use Think\Controller;

class SmsController extends Controller{
	private $smsdb;
	private $smsdata;
	function __construct()
	{
        parent::__construct();

        $this->smsdb = M('sms_template');
		$systemdb = M('system_setting');
		$this->smsdata = $systemdb->where('id=1')->find();
	}

	/**
	 * 发送短信
	 * @return [type] [description]
	 */
	function smsSend(){
		$pms = array_merge($_POST,$_GET);
		$mobile = "";
		if (array_key_exists('mobile', $pms) && $pms['mobile']) {
			$mobile = $pms['mobile'];
			$_SESSION['mobile'] = $pms['mobile'];
		}else{
			$this->results(4000, '', "请输入手机号码");
		}

		if (array_key_exists("type", $pms) && $pms['type']) {

			$smstemplate = $this->smsdb->where(array('cateid'=>$pms['type']))->find();
			if ($smstemplate) {
				if ($pms['type'] == "注册") {	// 发送注册短息
					$this->registSms($mobile, $smstemplate['id']);
				}else{
					$data = $pms['data'];

					if ($data) {//json字符串转array
						$data = json_decode($data);
					}
					$res = $this->SendSMS($mobile, $data, $smstemplate['id']);
					if ($res && $res === true) {

						$this->results(2000, $data, "发送成功");
					}else{
						$this->results(4000,'', '发送失败:'.$res);
					}
				}
			}else{
				$this->results(4000,'','找不到短信模板');
			}
		}else{
			$this->results(4000, '', "缺少短信类型参数");
		}
	}


	/**
	 * 短信模板查询
	 * @param string $templateid [description]
	 */
	public function getTemplate()
	{
		$templateid = $_GET['templateid'];
		$rest = new BasesmsController($this->smsdata['sms_hostip'],
									  $this->smsdata['sms_port'],
									  $this->smsdata['sms_version']);
		$rest->setAccount($this->smsdata['sms_sid'],$this->smsdata['sms_token']);
		$rest->setAppId($this->smsdata['sms_appid']);

		$tmpdata = $rest->QuerySMSTemplate($templateid);

		$tmplist = json_decode(json_encode($tmpdata),ture);
		if ($tmplist['statusCode'] == "000000") {
			if ($tmplist['totolCount'] == 1) {
				$ret = $this->updateTemplate($tmplist['TemplateSMS']);
			}else{
				// 删除不在列表中的模板数据
				$keeplist = '(';
				foreach ($tmplist['TemplateSMS'] as $key => $value) {
					$keeplist .= $value['id'].",";
				}
				$keeplist = substr($keeplist, 0, strlen($keeplist)-1);
				$keeplist .= ")";
				$this->smsdb->where('id not in '.$keeplist)->delete();

				foreach ($tmplist['TemplateSMS'] as $key => $value) {
					$ret = $this->updateTemplate($value);
					if (!$ret) {
						$this->results(4000, $tmpdata, '更新数据库失败');
					}
				}
			}
			if ($ret) {
				$this->results(2000, $tmpdata, 'ok');
			}else{
				$this->results(4000, $tmpdata, '更新数据库失败');
			}
		}else{
			$this->results(4000, '', '获取短信模板失败');
		}
	}


	private function updateTemplate($data, $delflag=false)
	{
		if ($delflag) {	// 删除自己以为的所有数据
			$this->smsdb->where('id!="'.$data['id'].'"')->delete();
		}
		$data_if = $this->smsdb->where('id="%s"', $data['id'])->find();
		// echo " sql: ".$this->smsdb->_sql();
		// echo "if: $data_if";
		if ($data_if) {
			$ret = $this->smsdb->data($data)->where(array('id'=>$data['id']))->save();
			// echo $this->smsdb->_sql();
			$ret=true;
		}else{
			$ret = $this->smsdb->data($data)->add();
		}
		return $ret;
	}


	// 发送注册短信息
	private function registSms($mobile, $tempId)
	{
		$userdb = M('weixin_user');
		$user_if = $userdb->where('phone=%s', $mobile)->find();
		// if ($user_if) {
		// 	$this->results(4000, '', '该手机号码已注册');
		// }

		$m_code = mt_rand(100000, 999999);
		$code   = array($m_code,'10');
		$res = $this->SendSMS($mobile, $code, $tempId);
		if ($res && $res === true) {
			$this->results(2000, $m_code, '发送成功');

		}else{
			$this->results(4000, '', $res);
		}
	}


	/**
	 * 发送模板短信
	 * @param to 手机号码集合,用英文逗号分开
	 * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
	 * @param $tempId 模板Id
	 * @param 例:$this->SendSMS('18622452683',array('345678'),C('SMS_TEMPID'));
	 */
	private function SendSMS($to,$datas,$tempId)
	{
		$rest = new BasesmsController($this->smsdata['sms_hostip'],
									  $this->smsdata['sms_port'],
									  $this->smsdata['sms_version']);
		$rest->setAccount($this->smsdata['sms_sid'],$this->smsdata['sms_token']);
		$rest->setAppId($this->smsdata['sms_appid']);
		// 发送模板短信
		$result = $rest->sendTemplateSMS($to,$datas,$tempId);
		// echo "result:".$result;
		if($result == NULL ) {
			return false;
		}
		if($result->statusCode!=0)
		{
			return $result->statusMsg;
		}else{
			//短信记录
			$this->smslog($to,$datas,$tempId);
			return true;
		}
	}


	// 记录发送短信
	private function smslog($to,$datas,$tempId)
	{
		$code = $datas[0];
		$logdb   = M('record_log');	//	短信发送轨迹记录
		$smstemp = $this->smsdb->where('id=%s', $tempId)->find();
		$content = $smstemp['content'];
		foreach ($datas as $key => $value) {
			$it = $key+1;
			$content = str_replace("{".$it."}", $value, $content);
		}

		$userdb = M('weixin_user');
		$userif = $userdb->where('phone=%s', $to)->find();
		$slog = array('touser'=>$userif['name'],
				'mobile'=>$to,
				'type'=>1,
				'code'=>$code,
				'content'=>$content,
				'type'=>$smstemp['cateid'],
				'addtime'=>time(),
				'tempuser'=>$_SESSION['admin_id']
				);
		$res = $logdb->data($slog)->add();
		/*echo $res;*/
	}

	private function results($code, $data, $msg)
	{
		echo json_encode(array("code"=>$code, "msg"=>$msg, "data"=>$data));
		die();
	}

	public function checksms(){
		$find = M('record_log')->where(array('code'=>$_POST['code'],
					 'mobile'=>$_POST['mobile']))->order('addtime desc')->find();
		if(!$find){
			echo json_encode(0);
		}elseif ($find['addtime'] +600 > time()){
			echo json_encode(1);
		} else {
			echo json_encode(2);
		}
	}
}