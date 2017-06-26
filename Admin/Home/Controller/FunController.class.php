<?php
namespace Home\Controller;
use Think\Controller;
use Home\Controller\ICommonController as IModel;
use Home\Service\Wechat;

/**
 * @cc 微信管理功能
 */
class FunController extends BaseController{
    public $data = array();
    private $token;
    function __construct(){
        parent::__construct();
        $wxcfg_db  = M('weiconfig');
        $wxcfg_jg  = $wxcfg_db->find();
        if($wxcfg_jg){
            $this->token = $wxcfg_jg['wx_token'];
        }
    }

    /**
     * @cc 获取微信菜单
     * @return [type] [description]
     */
    function getMenu()
    {
        //import('@.ORG.wechat');
        $weixin_cfgdb  = M('weiconfig');
        $weixin_config = $weixin_cfgdb->order('id desc')->find();
        if($weixin_config){
            $options = array(
                   'token'          => $weixin_config['wx_token'], //填写你设定的key
                   'encodingaeskey' => $weixin_config['wx_appAESKey'], //填写加密用的EncodingAESKey
                   'appid'          => $weixin_config['wx_appid'], //填写高级调用功能的app id
                   'appsecret'      => $weixin_config['wx_appsct'] //填写高级调用功能的密钥
            );
            $weObj   = new Wechat($options);
            $menu = $weObj->getMenu();
            //var_dump($menu);
        }
    }
    /**
     * @cc 生成微信菜单
     * @return [type] [description]
     */
    function create_menu(){
       // import('@.ORG.wechat');
        $menu    = M('weimenu')->where("mlevel=0")->order('morder asc')->limit(3)->select();
        $newmenu = array();
        foreach ($menu as $k => $v) {
            //创建子菜单
            $sonmenu = array('name'  => $v['mname']);
            $sonmenu['type'] = $v['mtype'];
            if($v['mtype']=="view"){
                $sonmenu['url'] = $v['murl'].'/tkn/'.$this->token;
            }else{
                $sonmenu['key'] = "V10_".$v['id'];//$v['mkey'];
            }
            //读取二级菜单
            $sonrst  = M('weimenu')->where('mlevel=%d',$v['id'])->order('morder asc')->limit(5)->select();
            if(count($sonrst)){
                $nsonmenu = array();
                foreach ($sonrst as $sk => $sv) {
                    $nsonmenus = array('name'  => $sv['mname']);
                    $nsonmenus['type'] = $sv['mtype'];
                    if($sv['mtype']=="view"){
                        if($sv['msucai']=="0"){
                            $nsonmenus['url'] = $sv['murl'].'/tkn/'.$this->token;
                        }else{
                            $nsonmenus['url'] = 'http://'.$_SERVER['HTTP_HOST'].U('Front/newshow',array('pid'=>$sv['msucai'],'token'=>$this->token));
                        }
                    }
                    elseif($sv['mtype']=="click"){
                        $nsonmenus['key'] = "V10_".$sv['id'];
                    }
                    else{
                        if($sv['msucai']!="0"){
                            $nsonmenus['url'] = $sv['murl'].'/tkn/'.$this->token;
                        }else{
                            $nsonmenus['key'] = "V10_".$sv['id'];
                        }
                        //$nsonmenus['key'] = $sv['mkey'];
                    }
                    $nsonmenu[] = $nsonmenus;
                }
                unset($sonmenu['type']);
                unset($sonmenu['url']);
                unset($sonmenu['key']);
                $sonmenu['sub_button'] = $nsonmenu;
            }
            $newmenu['button'][] = $sonmenu;
        }
        $weixin_cfgdb  = M('weiconfig');
		$weixin_config = $weixin_cfgdb->order('id desc')->find();
        if($weixin_config){
            $options = array(
                   'token'          => $weixin_config['wx_token'], //填写你设定的key
                   'encodingaeskey' => $weixin_config['wx_appAESKey'], //填写加密用的EncodingAESKey
                   'appid'          => $weixin_config['wx_appid'], //填写高级调用功能的app id
                   'appsecret'      => $weixin_config['wx_appsct'] //填写高级调用功能的密钥
            );
            $weObj   = new Wechat($options);
            $menu = $weObj->getMenu();
            $result = $weObj->createMenu($newmenu);
            // var_dump($newmenu);
            echo json_encode(array('errormsg'=>$result,'errcode'=>$weObj->errCode,'errmsg'=>$weObj->errMsg,'data'=>json_encode($newmenu)));
        }else{
            echo json_encode(array('errormsg'=>"",'errcode'=>400,'errmsg'=>"没有找到相关的配置信息"));
        }
    }

    /**
     * @cc 下拉菜单列表
     * @return [type] [description]
     */
    public function select_menu()
    {
        $this->data['menu'] = M('weimenu')->where("mlevel=0")->order('morder asc')->select();
        $menu  = M('weimenu')->where("mlevel=0")->order('morder asc')->select();
        $menu1 = array();
        if($menu){
            foreach ($menu as $k => $v) {
               $v =  M('weimenu')->where('mlevel=%d',$v['id'])->order('morder asc')->select();
               $menu1[] = $v;
            }
        }
        //$menu['menulist'] = $menu1;
        //dump($menu1);
    	if($menu1)
    	{
    		echo json_encode(array(
    				'code' => 2000,
    				'msg'  => "ok",
    				'data' => $menu1,
    		));
    	}else
    	{
    		echo json_encode(array(
					'code' => 4000,
					'msg'  => " error",
				));
    	}
    }
    /**
     * @cc 微信菜单列表
     * @return [type] [description]
     */
    function menu_list(){

     //layout(false);
        $uid     = cookie('wx_tkn');
        $this->data['menu'] = M('weimenu')->where("mlevel=0")->order('morder asc')->select();
        $menu  = M('weimenu')->where("mlevel=0")->order('morder asc')->select();
        $menu1 = array();
        if($menu){
            foreach ($menu as $k => $v) {
               $v['son'] =  M('weimenu')->where('mlevel=%d',$v['id'])->order('morder asc')->select();
               $menu1[] = $v;
            }
        }
        //dump($menu1);
        $this->data['menulist'] = $menu1;
        $this->assign('data',$this->data);
        $this->display('Fun/menu_list');
    }


    /**
     * @cc 微信菜单编辑
     * @return [type] [description]
     */
    function menu_edit(){
        $id  = $_GET['id'];
        $uid     = cookie('wx_tkn');
        $where = "1=1";
        if($id){
            $this->data['info'] =json_encode(M('weimenu')->where('id=%d',$id)->find());
            $where .= " and id!=$id";
        }else{
            $this->data['info'] = "\"\"";
        }
        $this->data['sucai'] = M('weisucai')->select();
        $this->data['menu'] = M('weimenu')->where("mlevel=0",$uid)->order('morder asc')->select();
        // $this->data['ctrl'] = $this->list_controller();
        $this->assign('data',$this->data);
        $this->display();
    }

    /**
     * @cc 微信菜单保存
     * @return [type] [description]
     */
    function menu_editok(){
        $id      = $_POST['id'];
        $menu_db = M('weimenu');
        if($id){
            //dump($_POST);die();
            $data = $_POST;
            $rst = $menu_db->where('id=%d',$id)->save($data);
        }else{
            $data = $_POST;
            $data['addtime'] = time();
            $data['wxgzh']   = cookie('wx_tkn');
            $data['mkey']    = 'event'.$data['addtime'];
            $rst = $menu_db->data($data)->add();
        }

         $this->display();
         //$this->__R('操作成功!',U('Fun/menu_list'),2);
    }

    /**
     * @cc 删除微信菜单
     * @return [type] [description]
     */
    function menu_delete(){
        $id      = $_GET['id'];
        $menu_db = M('weimenu');
        $rst = $menu_db->delete($id);
        if($rst){
            echo json_encode(array('code'=>2000));
        }else{
            echo json_encode(array('code'=>4000));
        }
        //$this->redirect('Fun/menu_list');
       // $this->__R('操作成功!',U('Fun/menu_list'),0);
    }

    //列出某个控制器的action动作和视图
    private function list_action()
    {
        $ctrlId     = $_GET['ctrlId'];
        if($ctrlId != '')
        {
            $baseContrl = get_class_methods('Action');
            $advContrl  = get_class_methods($ctrlId);
            $diffArray  = array_diff($advContrl,$baseContrl);
            echo json_encode($diffArray);
        }
    }

    /**
     * @cc 微信基本设置
     * @return [type] [description]
     */
    function config(){
		//layout(false);
        $cfg_db = M('weiconfig')->order('id desc')->find();
        //dump($cfg_db);die();
        if($cfg_db){
            $this->data['info'] =json_encode($cfg_db);
        }else{
            $this->data['info'] = "\"\"";
        }
        $uid     = cookie('admin_id');
        $this->data['sucai'] = M('weisucai')->where("wxuid='%s'",$uid)->select();
        // $this->data['ctrl']  = $this->list_controller();
        // print_r($this->data);
        // die();
       // dump($this->data);
        $this->assign('data',$this->data);
        $this->display('config');
    }

    /**
     * @cc 微信配置保存
     * @return [type] [description]
     */
    function configok(){
        $id      = $_POST['id'];
        //dump($_POST);
        $weiconfig_db = M('weiconfig');
        if($id){
            $data = $_POST;
            $rst = $weiconfig_db->where('id=%d',$id)->save($data);
        }else{
            $data = $_POST;
            $data['gzh']   = cookie('admin_id');
            $data['addtime'] = time();
            $rst = $weiconfig_db->data($data)->add();
        }

        //  if($rst){
        //     echo json_encode(array('code'=>2000));
        // }else{
        //     echo json_encode(array('code'=>4000));
        // }

         echo "<script>parent.layer.msg('保存成功',{icon: 1,time:3000});</script>";
         $this->redirect('Fun/config');


    }

    /**
     * @cc 微信自动回复列表
     * @return [type] [description]
     */
    function autoreply_list(){
        $Reply  = M('weireply'); // 实例化User对象
        //import('ORG.Util.Page');// 导入分页类
        // $get   = $_GET;
        // unset($get['p']);
        // unset($get['_URL_']);
        $post = $_POST;
        $uid   = cookie('wx_tkn');
        if($post['kw']!=""){
            $kw = $post['kw'];
           $sch['_string'] = "sname like '%$kw%' or kw like '%$kw%' or wd like '%$kw%'";
        }
        if($post['ifok']!=""){
            $ifok = $post['ifok'];
            $sch['ifok'] = array('eq',$ifok);
        }
        $count = $Reply->where($sch)->count();// 查询满足要求的总记录数
        $Page  = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
        //分页跳转的时候保证查询条件
        foreach($post as $key=>$val) {
            $Page->parameter   .=   "&$key=".urlencode($val);
        }
        $show  = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list  = $Reply
                ->where($sch)
                ->limit($Page->firstRow.','.$Page->listRows)
                ->select();

        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('count',$count);
        $this->display();
    }

    /**
     * @cc 微信自动回复编辑
     * @return [type] [description]
     */
    function autoreply_edit(){
        $uid = cookie('wx_tkn');
        $id  = $_GET['id'];
        $where = "1=1";
        if($id){
            $this->data['info'] =json_encode(M('weireply')->where('id=%d',$id)->find());

        }else{
            $this->data['info'] = "\"\"";
        }
        $this->data['sucai'] = M('weisucai')->where("wxuid='%s'",$uid)->select();
        // $this->data['ctrl'] = $this->list_controller();
        $this->assign('data',$this->data);
        $this->display();
    }

    /**
     * @cc 微信自动回复保存
     * @return [type] [description]
     */
    function autoreply_editok(){
        $id      = $_GET['id'];
        $menu_db = M('weireply');
        if($id){
            $data = $_POST;
            $rst = $menu_db->where('id=%d',$id)->save($data);
        }else{
            $data = $_POST;
            $data['addtime'] = time();
            $data['gzh']   = cookie('wx_tkn');
            $rst = $menu_db->data($data)->add();
        }
          //echo "<script>parent.layer.msg('保存成功',{icon: 1,time:3000});</script>";

           $this->display('menu_editok');
           // $this->__R('操作成功!',U('Fun/autoreply_list'),2);
    }

    /**
     * @cc 删除微信自动回复
     * @return [type] [description]
     */
    function autoreply_delete()
    {
        $id      =  $_GET['id'];
        $menu_db = M('weireply');
        $rst = $menu_db->delete($id);
       // $this->__R('操作成功!',U('Fun/autoreply_list'),0);

        if($rst){
            echo json_encode(array('code'=>2000));
        }else{
            echo json_encode(array('code'=>4000));
        }
    }


     /**
      * @cc 微信用户列表
      * @return [type] [description]
      */
    function users(){
       // layout(false);
        $Oils  = M('weiuser'); // 实例化User对象
       // import('ORG.Util.Page');// 导入分页类
        $get   = $_GET;
        unset($get['p']);
        unset($get['_URL_']);
        $uid   = cookie('wx_tkn');
        if($get['kw']!=""){
            $kw = $get['kw'];
           $sch['_string'] = "username like '%$kw%' or wxname like '%$kw%'";
        }
        if($get['wxstatus']!=""){
            $wxstatus = $get['wxstatus'];
           $sch['wxstatus'] = array('eq',$wxstatus);
        }
        $count = $Oils->where($sch)->count();// 查询满足要求的总记录数
        $Page  = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
        //分页跳转的时候保证查询条件
        foreach($get as $key=>$val) {
            $Page->parameter   .=   "&$key=".urlencode($val);
        }
        $show  = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list  = $Oils
                ->where($sch)
                ->limit($Page->firstRow.','.$Page->listRows)
                ->select();
                //dump($list);
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->display('Fun:user_list');
    }

    /**
     * @cc 微信消息列表
     * @return [type] [description]
     */
    function msgs(){
        //layout(false);
        $Oils  = M('weimsg'); // 实例化User对象
        //import('ORG.Util.Page');// 导入分页类
        $get   = $_GET;
        unset($get['p']);
        unset($get['_URL_']);
        $uid   = cookie('wx_tkn');
        $sch['_string'] = "context!='null'";
        if($get['kw']!=""){
            $kw = $get['kw'];
            $sch['_string'] = "frm like '%$kw%' or tos like '%$kw%' or context like '%$kw%'";
        }

        $count = $Oils->where($sch)->count();// 查询满足要求的总记录数
        $Page  = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
        //分页跳转的时候保证查询条件
        foreach($get as $key=>$val) {
            $Page->parameter   .=   "&$key=".urlencode($val);
        }
        $show  = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list  = $Oils
                ->field(
                    array(
                         '*',
                         '(Select name from td_weixin_user where wxuser = td_weimsg.frm)' => 'frms',
                        )
                    )
                ->where($sch)
                ->limit($Page->firstRow.','.$Page->listRows)
                ->order('id desc')
                ->select();
                // echo $Oils->getlastsql();
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->display('Fun:msg_list');
    }


    /**
     * @cc 微信文章预览页面
     * @return [type] [description]
     */
    function article(){

        $dbname = $_GET['db'];
        $article = M($dbname)->find($_GET['id']);
        $this->assign('article',$article);
        if ($dbname == "issues" && $article['category'] == 3){
            $this->display('anli');
            exit;
        }
        $this->display();
    }
}