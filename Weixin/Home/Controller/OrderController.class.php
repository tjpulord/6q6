<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 基础控制类
 */
class OrderController extends WeixinController{

    public $userinfo;
    public $redirect_uri;

    public $orderdb, $goodsdb;
    /**
     * 基础类构造函数
     */
    function __construct()
    {
        parent::__construct();
        $this->userinfo = $this->_USER;

        $this->orderdb = M('order');
        $this->goodsdb = M('goods');
    }

    /**
     * 我的订单列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function orderlist()
    {
        $status = $_GET['status'];

        $where = array('o.userid'=>$this->userinfo['id'], 'o.if_del'=>0);
        if (!$status) {
            $status = 0;
        }else{
            $where['o.status'] = $status;
        }
        $orderlist = $this->goodsdb
                    ->join('as g left join tk_order as o on g.id=o.goodsid')
                    ->join('tk_image_data as d on g.id=d.goodsid')
                    ->field('o.*, g.name, g.goodsid as goodsno, g.startend, g.isself, d.image')
                    ->where($where)
                    ->group('o.id, g.id')
                    ->order('o.id desc')
                    ->select();

        $this->assign('orderlist', $orderlist);
        $this->assign('status', $status);
        $this->assign('userid', $this->userinfo['id']);
        $this->display();
    }

    /**
     * 订单详情
     * @return [type] [description]
     */
    public function order()
    {
        $orderno = $_GET['orderno'];
        $orderinfo = $this->orderdb->join('as o left join tk_user as u on o.userid=u.id')
                    ->join('tk_image_data as d on o.goodsid=d.goodsid')
                    ->field("o.*, u.name as owername, d.image")
                    ->where(array('orderno'=>$orderno))->find();

        $goodsinfo = $this->goodsdb->where(array('id'=>$orderinfo['goodsid']))->find();
        // echo $this->orderdb->getLastSql();
        $this->assign('orderinfo', $orderinfo);
        $this->assign('goodsinfo', $goodsinfo);
        $this->assign('userid', $this->userinfo['id']);
        $this->display();
    }

    /**
     * 我的攒单列表
     * @return [type] [description]
     */
    public function cuandanlist()
    {
        if (!$this->userinfo['id']) {
            redirect('http://'.$_SERVER['SERVER_NAME']."/index.php/home/Register/signin?url=$this->redirect_uri");
        }

        $where = array('g.userid'=>$this->userinfo['id'], 'g.status'=>1, 'g.iscomplete'=>1, 'd.position'=>1);

        $status = 0;
        if ($_GET['status']) {
            $status = $_GET['status'];
            $where['status'] = $status;
        }
        $goodslist = $this->goodsdb
                    ->join('as g left join tk_image_data as d on g.id=d.goodsid')
                    ->field('g.*, d.image')
                    ->where($where)
                    ->limit(6)
                    ->group('g.id')
                    ->select();
        $orderary = array();
        foreach ($goodslist as $key => $value) {
            $oinfo = $this->orderdb->where(array('goodsid'=>$value['id'], 'pay_status'=>1))->count();
            if ($oinfo > $value['min']) {
                $orderary[$value['id']] = array('number'=>$oinfo, 'ratio'=>100, 'left'=>0);
            }else{
                $orderary[$value['id']] = array('number'=>$oinfo, 'ratio'=>round($oinfo*100/$value['min']), 'left'=>$value['min']-$oinfo);
            }

        }

        $this->assign('olist', $orderary);
        $this->assign('goodslist', $goodslist);
        $this->assign('status', $status);
        $this->display();
    }


    /**
     * 攒单详情
     * @return [type] [description]
     */
    public function cuandan()
    {
        $gid = $_GET['id'];
        if (!$gid) {
            $this->error('找不到id');
            redirect('index.php?s=/home/goods/goodslist');
        }

        $goodsinfo = $this->goodsdb
                ->join('as g left join tk_image_data as d on g.id=d.goodsid')
                ->field('g.*, d.image')
                ->where(array('g.userid'=>$this->userinfo['id'], 'g.status'=>1, 'g.iscomplete'=>1, 'd.position'=>1, 'g.id'=>$gid))
                ->group('g.id')
                ->find();
        $oinfo = $this->orderdb->field('count(id) as number, sum(pay_amount) as totalmoney')
                        ->where(array('goodsid'=>$gid, 'pay_status'=>1))
                        ->find();

        $memberlist = $this->orderdb
                        ->where(array('goodsid'=>$gid, 'pay_status'=>1))
                        ->join('as o left join tk_user as u on o.userid=u.id')
                        ->field('o.*, u.name as username, u.header as header, u.id as userid')
                        ->select();

        $this->assign('userinfo', $this->userinfo);
        $this->assign('goodsinfo', $goodsinfo);
        $this->assign('oinfo', $oinfo);
        $this->assign('memberlist', $memberlist);
        $this->display();
    }


    /**
     * 下单确认页面
     * @return [type] [description]
     */
    public function orderconfirm()
    {
        $post = $_POST;
        // var_dump($post);
        $goodsinfo = $this->goodsdb->join('as g left join tk_user as u on g.userid=u.id')
                    ->join('tk_image_data as d on g.id=d.goodsid')
                    ->field('g.*, u.name as username, d.image')
                    ->where(array('g.id'=>$post['goodsid']))
                    ->group('g.id')
                    ->find();

        $post['money'] = $post['goodnum'] * $goodsinfo['price'];
        $post['total'] = $post['money'] + $goodsinfo['post_fee'];
        $post['userid']= $this->userinfo['id'];

        $this->assign('params',$post);
        $this->assign('goodsinfo', $goodsinfo);
        $this->display('dingdanqueren');
    }

    /**
     * 确认收货(商品分发)
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function handout()
    {
        $orderno = $_GET['orderno'];
        if (!$orderno) {
            $this->error('找不到订单id');
            exit();
        }
        $orderno = $_GET['orderno'];

        $where = array('orderno'=>$orderno, 'status'=>$status);
        $orderinfo = $this->orderdb->join('as o left join tk_user as u on o.userid=u.id')
                    ->join('tk_image_data as d on o.goodsid=d.goodsid')
                    ->field("o.*, u.name as owername, d.image")
                    ->where(array('o.orderno'=>$orderno))->find();
        if ($orderinfo && $orderinfo['orderno']) {
            $goodsinfo = $this->goodsdb->where(array('id'=>$orderinfo['goodsid']))->find();
            if ($goodsinfo['userid'] != $this->userinfo['id']) {
                $this->error('商品信息错误');
                die();
            }
        }

        $this->assign('orderinfo', $orderinfo);
        $this->display();
    }
}