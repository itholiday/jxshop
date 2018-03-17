<?php
namespace Admin\Controller;

class OrderController extends CommonController
{
    public function index(){
        $data = M('Order')->select();
        $this->assign('data',$data);
        $this->display();
    }

    public function send(){
        if(IS_GET){
            $order_id = intval(I('get.order_id'));
            $info = M('Order')->alias('a')->join('left join __USER__ b on a.user_id=b.id')
                ->field('a.*,b.username')->where('a.id='.$order_id)->find();
            $this->assign('info',$info);
            $this->display();
            exit();
        }
        $order_id = intval(I('post.id'));
        $data = array(
            'id' => $order_id,
            'order_status' => 1,
            'com' => I('post.com'),
            'no' => I('post.no')
        );
        $res = M('Order')->save($data);
        if(!$res){
            $this->error('发货失败');
        }
        $this->success('发货成功');
    }
}