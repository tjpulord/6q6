<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 攒单类
 */
class GoodsController extends WeixinController{

	public $goodsdb, $commentdb, $orderdb, $imagedb;
	/**
	 * 攒单类构造函数
	 */
	function __construct()
    {
    	parent::__construct();
        $this->goodsdb   = M('goods');
        $this->commentdb = M('comments');
        $this->orderdb   = M('order');
        $this->imagedb = M('image_data');
    }


    /**
     * 攒单预约主页
     * @return [type] [description]
     */
    public function index()
    {
        if (!$this->_USER['id']) {
            redirect('http://'.$_SERVER['SERVER_NAME']."/index.php/home/Register/signin?url=$this->redirect_uri");
        }

        if ($_POST){
            $data = $_POST;
            if (!$data['issale']) {
                $data['issale'] = 0;
            }
            $res = $this->goodsdb->data($data)->filter('strip_tags')->where(array('id'=>$data['id']))->save();
        }

        $goodsinfo = $this->goodsdb->where(array('userid'=>$this->_USER['id'], 'iscomplete'=>0))->find();
        if ($goodsinfo) {
            $this->assign('info', $goodsinfo);
        }else{
            $dd = date("Ymd", time());
            $ct = $this->goodsdb->where('goodsid like "%SYGS-'.$dd.'%"')->count();
            $nid = "SYGS-".$dd.($ct+10001);
            $res = $this->goodsdb->data(array('userid'=>$this->_USER['id'], 'goodsid'=>$nid))->add();
            $this->assign('info', array('id'=>$res, 'userid'=>$this->_USER['id'], 'goodsid'=>$nid));
        }

        $imagelist = $this->imagedb
                ->where(array('userid'=>$this->_USER['id'], 'goodsid'=>$goodsinfo['id'], 'position'=>1))
                ->order('id asc')
                ->select();
        $this->assign('imagelist', $imagelist);

        $this->display();
    }


    /**
     * 商品预览
     * @return [type] [description]
     */
    public function preview()
    {
        $data = $_POST;
        if (!$data['issale']) {
            $data['issale'] = 0;
        }
        $res = $this->goodsdb->data($data)->filter('strip_tags')->where(array('id'=>$data['id']))->save();
        $goodsinfo = $this->goodsdb->where(array('id'=>$data['id']))->find();

        $imagelist = $this->imagedb
                ->where(array('userid'=>$this->_USER['id'], 'goodsid'=>$goodsinfo['id'], 'position'=>1))
                ->order('id asc')
                ->select();

        $this->assign('imagelist', $imagelist);
        $this->assign('info',$goodsinfo);
        $this->display();
    }

    /**
     * 预约商品信息保存
     * @return [type] [description]
     */
    public function keepin()
    {
        $post = $_POST;
        if (array_key_exists('key', $post) && array_key_exists('value', $post) && array_key_exists('id', $post)) {
            $res = $this->goodsdb->data(array(str_replace('goods_','',$post['key'])=>$post['value']))
                    ->where(array('id'=>$post['id']))->save();
            if ($res) {
                echo json_encode(array('code'=>2000, 'msg'=>'ok'));
            }else{
                echo json_encode(array('code'=>4000, 'msg'=>"保存失败"));
            }
        }else{
            echo json_encode(array('code'=>4000, 'msg'=>'参数不正确'));
        }
    }


    /**
     * 时令产品列表
     * @return [type] [description]
     */
    function shiling()
    {
        $this->display();
    }


    /**
     * 预约活动列表
     * @return [type] [description]
     */
    public function prebook()
    {
        $this->display('goodslist');
    }


    /**
     * 所有商品
     * @return [type] [description]
     */
    public function goodslist()
    {
        $glist = $this->goodsdb->join("as g left join tk_user as u on g.userid=u.id")
                ->join('tk_image_data as img on img.goodsid=g.id')
                ->field("g.*, u.name as username, img.image")
                ->where('iscomplete=1 and status>0')
                ->order('g.id desc')
                ->group('g.id')
                ->select();
        // echo "string:".$this->goodsdb->getLastSql();
        $this->assign('goodslist', $glist);
        $this->display();
    }



    /**
     * 商品详情展示页面
     * @return [type] [description]
     */
    public function show()
    {
        $gid = $_GET['id'];
        if (!$gid) {
            $this->error('找不到id');
            redirect('index.php?s=/home/goods/goodslist');
        }
        $goodsinfo = $this->goodsdb->join("as o left join tk_user as u on o.userid=u.id")
                        ->where(array('o.id'=>$gid))
                        ->field('o.*, u.header, u.id as userid, u.name as username, u.mobile')
                        ->find();

        // var_dump($goodsinfo);
        // 交易订单
        $orderlist = $this->orderdb->join("as o left join tk_user as u on o.userid=u.id")
                            ->field('u.header, u.id')
                            ->where('o.status=5 and o.pay_status=1 and o.goodsid='.$gid)
                            ->order('o.id desc')
                            ->select();
        // 评论
        $comments = $this->commentdb->join(" as c left join tk_user as u on u.id=c.userid")
                    ->field('c.*, u.name as username, u.header as header')
                    ->where(array('c.goodsid'=>$gid, 'c.status'=>1, "c.commentid"=>0))
                    ->limit(6)
                    ->select();
        // echo $this->commentdb->getLastSql();
        $commentlist = array();
        $commentlist[] = $comments;

        // 商品图片
        $imagelist = $this->imagedb
                ->where(array('goodsid'=>$gid, 'position'=>1))
                ->order('id asc')
                ->select();

        foreach ($comments as $ck => $cv) {
            $commentsinfo = $this->commentdb->join(" as c left join tk_user as u on u.id=c.userid")
                        ->join(' tk_user as s on c.replyuserid=s.id ')
                        ->field("c.*, u.name as username1, s.name as username2")
                        ->where(array('goodsid'=>$goodsinfo['id'], 'commentid'=>$cv['id']))
                        ->limit(6)
                        ->select();
            $commentlist[$cv['id']] = $commentsinfo;
        }

        $this->assign('imagelist', $imagelist);
        $this->assign("comments", $commentlist);
        $this->assign('info', $goodsinfo);
        $this->assign('userinfo', $this->_USER);
        $this->assign('orderlist', $orderlist);
        $this->display();
    }


    /**
     * 购买会员列表
     * @return [type] [description]
     */
    public function member()
    {
        // 交易订单
        $orderlist = $this->orderdb->join("as o left join tk_user as u on o.userid=u.id")
                            ->field('u.header, u.id')
                            ->where('o.status=5 and o.pay_status=1 and o.goodsid='.$_GET['gid'])
                            ->order('o.id desc')
                            ->select();
        $this->assign('memberlist', $orderlist);
        $this->display();
    }

    /**
     * 添加回复
     */
    public function addComment()
    {
        $data = $_POST;
        $data['addtime'] = time();
        $res = $this->commentdb->data($data)->filter('strip_tags')->add();

        if ($res) {
            json_encode($res);
        }else{
            json_encode(0);
        }
    }

    /**
     * 加载评论
     * @return [type] [description]
     */
    public function ajaxComments()
    {
        $data = $_POST;
        $clist = $this->commentdb->join(" as c left join tk_user as u on u.id=c.userid")
                ->field('c.*, u.name as username, u.header as header')
                ->where(array('goodsid'=>$data['goodsid'], 'status'=>1, "commentid"=>0))
                ->limit($data['page']*6, 6)
                ->select();
        $commentslist = array();
        $commentslist[] = $clist;

        $str = "";
        foreach ($clist as $ck => $cv) {
            $commentsinfo = $this->commentdb->join(" as c left join tk_user as u on u.id=c.userid")
                        ->join(' tk_user as s on c.replyuserid=s.id ')
                        ->field("c.*, u.name as username1, s.name as username2")
                        ->where(array('goodsid'=>$goodsinfo['id'], 'commentid'=>$cv['id']))
                        ->limit(6)
                        ->select();
            // $commentslist[$cv['id']] = $commentsinfo;
            $str .= '<div class="backgroud_ff mright5p mleft5p mbottom12"><div class="mright10p mleft10p">
            <img class="mright06 ftleft bgwidth30 bgheight30 mtop06" src="'.$cv['header'].'" alt="__ROOT__/Public/weixin/image/noimage.png" >
            <p class="fontsize105 ptop03">'.$cv['content'].'</p>
            <time class="fontsize09 texcolora0 " datetime="'.data('Y-m-d', $cv['addtime']).'">'.data('Y-m-d', $cv['addtime']).'</time>
            <a onclick="javascript:addcomment('.$cv['id'].','.$cv['userid'].', \''.$cv['username'].'\');" class="opacity08 bdsolid ptop01 pbottom01 pleft10 pright10 texcolor2d ftright mtop_13 braderradius6 fontsize08">回复</a>
            </div>';

            $str .= '<div class="texcolor66 mleft39 pright10 fontsize1" >
                    <p class=" fontsize1 pbottom04 ">'.$cv['content'].'</p>
                    <div id="aaaaa'.$cv['id'].'">';

            foreach ($commentsinfo as $ik => $iv) {
                $str .= '<p><a class="pbottom04 bbtopdd0 ptop04" href="javascript:addcomment('.$cv['id'].', '.$iv['userid'].', \''.$iv['username1'].'\');"><span class="texcolor4b">'.$iv['username1'].'</span>回复<span class="texcolor4b">'.$iv['username2'].':</span>'.$iv['content'].'</a></p>';
            }
            $str.="</div>";
            if (count($commentsinfo) == 6) {
                $str .= '<div id="position{{$pid}}" class="more_comments">';
                $str .= '<a class="more_huifu " href="javascript:jiazaihuifu('.$iv['id'].');"><<展开全部回复>></a></div>';
            }

        }

        echo json_encode(array('data'=>$str));
    }

    /**
     * 加载回复
     * @return [type] [description]
     */
    public function ajaxHuifu(){
        $data = $_POST;
        $commentsinfo = $this->commentdb->join(" as c left join tk_user as u on u.id=c.userid")
                ->join(' tk_user as s on c.replyuserid=s.id ')
                ->field("c.*, u.name as username1, s.name as username2")
                ->where(array('goodsid'=>$data['goodsid'], 'commentid'=>$data['commentid']))
                ->limit(6,1000)
                ->select();

        $str = "";
        foreach ($commentsinfo as $ik => $iv) {
            $str .= '<p><a class="pbottom04 bbtopdd0 ptop04" href="javascript:addcomment('.$cv['id'].', '.$iv['userid'].', \''.$iv['username1'].'\');"><span class="texcolor4b">'.$iv['username1'].'</span>回复<span class="texcolor4b">'.$iv['username2'].':</span>'.$iv['content'].'</a></p>';
        }
        echo json_encode(array('data'=>$str));
    }


    /**
     * 删除图片
     * @return [type] [description]
     */
    function delimage(){
        if (!$this->_USER['id']) {
            redirect('http://'.$_SERVER['SERVER_NAME']."/weixin.php/home/Register/signin?url=$this->redirect_uri");
        }
        $imageurl = $_POST['image'];
        $goodsid = $_POST['goodsid'];
        if($imageurl && $goodsid) {
            $imagedata = array('userid'=>$this->_USER['id'],
                            'image'=>$imageurl,
                            'goodsid'=>$goodsid,
                            'position'=>1);
            $res = $this->imagedb->where($imagedata)->delete();
            echo json_encode(array('code'=>$res));
        }else{
            echo json_encode(array('code'=>0));
        }
    }

    /**
     * 保存照片
     * @return [type] [description]
     */
    public function saveimage()
    {
        if (!$this->_USER['id']) {
            redirect('http://'.$_SERVER['SERVER_NAME']."/weixin.php/home/Register/signin?url=$this->redirect_uri");
        }
        $goodsid = $_POST['goodsid'];
        $imageurl = $_POST['image'];
        if($imageurl && $goodsid) {
            $imagedata = array('userid'=>$this->_USER['id'],
                            'image'=>$imageurl,
                            'goodsid'=>$goodsid,
                            'position'=>1);
            $res = $this->imagedb->data($imagedata)->add();
            echo json_encode(array('code'=>$res));
        }else{
            echo json_encode(array('code'=>0));
        }
    }
}