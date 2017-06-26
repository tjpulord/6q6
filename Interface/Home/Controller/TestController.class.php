<?php
namespace Home\Controller;
use Think\Controller;
class TestController extends BaseController {

    public function index()
    {
        $replydata = array("appid"=>"wxc42e02da87c85ebe",
                            "attach"=>"计划账户充值 1 元",
                            "bank_type"=>"CFT",
                            "cash_fee"=>"1",
                            "fee_type"=>"CNY",
                            "is_subscribe"=>"Y",
                            "mch_id"=>"1350866801",
                            "nonce_str"=>"jpzugoxcwb664ahzt0xm4luvgza1nsbe",
                            "openid"=>"oLnZosz11atlhjFs2Noq5nKcSoos",
                            "out_trade_no"=>"td_e5121e2b1766167a1",
                            "result_code"=>"SUCCESS",
                            "return_code"=>"SUCCESS",
                            "sign"=>"BFCD4128F3D9150BC73B8753C3BA88E8",
                            "time_end"=>"20160805182510",
                            "total_fee"=>1,
                            "trade_type"=>"JSAPI",
                            "transaction_id"=>"4007112001201608050645504424");
        $this->assign('replaydata',$replydata);
        $this->display();
    }

    public function isvalidcardnumber()
    {
        $idcard = $_GET['id'];
        $ch = curl_init();
        $url = 'http://apis.baidu.com/apistore/idservice/id?id='.$idcard;
        $header = array(
            'apikey: 87189408630cfe0748dbd8cae8cc2b02',
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);

        // var_dump(json_decode($res));
        $result = json_decode($res, ture);
        // var_dump($result);
        if ($result['errNum'] == 0) {
            $mydata = $result['retData'];
            $this->getJson('ok',$mydata);
        }else{
            $this->getError('false', $result);
        }
    }



    function dizzsearch(){
        $dbname = $_POST['db'];
        $value = $_POST['name'];
        $colunm = $_POST["colunm"]?$_POST["colunm"]:"name";
        $searchdb = M($dbname);
        if ($value) {
            $datas = $searchdb->where("$colunm like '%".$value."%'")->limit(10)->select();
        }else{
            $datas = $searchdb->where("1")->limit(10)->select();
        }
        if ($datas) {
            $this->getJson('ok', $datas);
            // echo json_encode(array('code'=>2000, 'data'=>$datas));
        }else{
            $this->getError('false', "没搜索到任何数据");
        }
    }
}