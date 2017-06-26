<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
        $this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px } a,a:hover{color:blue;}</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p><br/>版本 V{$Think.version}</div><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_55e75dfae343f5a1"></thinkad><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
    }


    function getareas(){
        $areadb = M("areas");

        $search = $_GET['key'];
        if ($search) {
            $citylist = $areadb->where('status>0 and name like "%'.$search.'%"')->select();
        }else{
            $citylist = $areadb->where('status=1')->select();
        }

        if ($citylist) {
            $this->getJson('ok', $citylist);
        }else{
            $this->getError('没找到对应的城市');
        }
    }

    function getzones(){
        $zonedb = M('community');
        $cid    = $_GET['cid'];
        $search = $_GET['key'];

        $csearch = "";
        if ($cid) {
            $csearch = " and city=$cid";
        }
        if ($search) {
            $zonelist = $zonedb->where('fullname like "%'.$search.'%"'.$csearch)->select();
        }else{
            $zonelist = $zonedb->where('1'.$csearch)->select();
        }
        if ($zonelist) {
            $this->getJson('ok', $zonelist);
        }else{
            $this->getError('没找到对应的小区');
        }
    }
}