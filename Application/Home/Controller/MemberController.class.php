<?php
namespace Home\Controller;
class MemberController extends CommonController {
    public function __construct()
    {
        parent::__construct();
        //登录验证
        $user = D('User');
        $user->checkLogin();
    }

    //显示我的订单
    public function order()
    {
        $user_id = session('user_id');
        $data = D('Order')->where('user_id='.$user_id)->select();
        $this->assign('data',$data);
        $this->display();
    }

    //查看具体的订单额快递信息
    public function express()
    {
        $order_id = I('get.order_id');
        $info = M('Order')->where('id='.$order_id)->find();
        if(!$info || $info['order_status']!=1){
            $this->error('参数错误');
        }
        //根据快递公司代号以及运单号 查询快递信息
        //组装请求的URL地址
        $url = 'http://v.juhe.cn/exp/index?key=6a521dbfe39130377fc913541595c4b6&com='.$info['com'].'&no='.$info['no'];
        $res = file_get_contents($url);
        $res = json_decode($res,true);
        $this->assign('data',$res);
        $this->display();
    }
}