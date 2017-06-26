<?php
 namespace Home\Controller;
 use Think\Controller;
 use Home\Service\JSSDK;

 class WeixinController extends CommonController {
    public $_USER;
    private $weixin;
    public $redirect_uri;
    public $dissdb;
    function __construct()
    {
      	parent::__construct();
        $this->redirect_uri = urlencode('http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]);
      	$m_weixin     = M('weiconfig');
        // $this->dissdb = M('page_setting');

        // $this->isdisabledpage();
      	// $this->weixin = $m_weixin->find();
       //  $userinfo = $this->userinfo($this->weixin['wx_appid'], $this->weixin['wx_appsct']);
       //  if ($userinfo['zoneid'] == 0) {
       //      redirect('index.php?s=/home/MyCenter/myinfo');
       //  }
        $this->getmemmberinfo();
    }


    function getmemmberinfo(){
        $userinfo = cookie("WUSER");
        if (empty($userinfo)) {
            redirect('index.php?s=/home/Register/signin');
            die();
        }

        $this->_USER = json_decode(json_encode($userinfo), true);
    }

    /*
    *获取用户微信信息
    *if
    *       cookie中存在用户信息 直接返回 $userinfo
    *else
    *       网页授权获取用户信息
    *       将用户微信信息存入cookie
    *       返回用户信息
    */
    private function userinfo($appid, $secret){
      $userinfo = cookie('WUSER');
      if(empty($userinfo))
      {
          $userinfo = $this->oauth($appid, $secret);
          session('weinfo',$userinfo);
      }
      if ($_GET['invphone']){
          $_SESSION['invphone'] = $_GET['invphone'];
      }
      $m_user = M('user');
      $user = $m_user
            ->where(array('openid' =>$userinfo['openid']))
            ->find();
      if ($user && $user['mobile']) {
        if ($user['header'] == "" || strpos($user['header'], 'http') == 0 ) {
          $user['header'] = $userinfo['headimgurl'];
        }
        if ($user['nickname'] != $userinfo['nickname']) {
            $user['nickname'] = $userinfo['nickname'];
        }
        // 保存userinfo
        $m_user->data($user)->where(array('openid' =>$userinfo['openid']))->save();

      } else {
        redirect('index.php?s=/home/Register/signin');
      }
      $this->_USER = json_decode(json_encode($user), true);
      cookie('WUSER',$user);
      session('userinfo',$user);
      return $user;
    }


    /**
     * [signPackage jssdk]
     * @return [type] [description]
     */
    protected function signPackage(){
        $jssdk = new JSSDK($this->weixin['wx_appid'], $this->weixin['wx_appsct']);
        $signPackage = $jssdk->GetSignPackage();
        $access_token = $jssdk->getAccessToken();
        $this->assign('signPackage',$signPackage);
        return $signPackage;
    }

    /*
    * @微信授权登录获取用户信息
    * *return userinfo
     */
   	private function oauth($appid, $secret) {
   		if (isset($_GET['code'])){
   			$code = $_GET['code'];
   			$access = $this->getAccesstoken($appid, $secret, $code);
   			$access_res = json_decode($access);
   			if (isset($access_res->errcode)){
   				$this->i_redirect($appid);
   				exit;
   			}
   			$userinfo = $this->getUserinfo($access_res->access_token, $access_res->openid);
		    return json_decode($userinfo,true);
    	}else{
    		$this->i_redirect($appid);
    	}
   	}

   	/*
   	*跳转页面获取code
   	 */
   	private function i_redirect ($appid){
   		$redirect_uri = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
	   	$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect';
	   	redirect($url);
	   	exit;
   	}

   	/*
   	*获取用户信息
   	 */
   	private function getUserinfo($access_token, $openid){
   		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid";
   		return $this->httpGet($url);
   	}
   	/*
   	*获取用户access_token
   	*@return $access_token
   	 */
   	private function  getAccesstoken($appid, $secret, $code) {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
        $res = $this->httpGet($url);
        return $res;
   	}


    /**
     * 判断页面功能是否启动
     * @return [type]         [description]
     */
    private function isdisabledpage(){
        $urlstr = urldecode($this->redirect_uri);
        $pos = strpos($urlstr, 'tkn');
        if ($pos) {
          $urlstr = substr($urlstr, 0, $pos);
        }

        $page = $this->dissdb->where(array('url'=>$urlstr))->find();
        if ($page && $page['status'] == 2) {
            redirect("weixin.php?s=/home/Invitation/index.html");
        }
    }

   	/*
   	*curl get 获取return $res
   	 */
   	private function httpGet($url) {
	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt($curl, CURLOPT_URL, $url);

	    $res = curl_exec($curl);
	    curl_close($curl);

	    return $res;
	}
}
