<?php
namespace Home\Controller;
class IndexController extends CommonController {
    public function index(){
        $this->assign('is_show',1);
        $g = D('Admin/Goods');
        $hot = $g->getRecGoods('is_hot');
        $this->assign('hot',$hot);
        $rec = $g->getRecGoods('is_rec');
        $this->assign('rec',$rec);
        $new = $g->getRecGoods('is_new');
        $this->assign('new',$new);
        $crazy = $g->getCrazyGoods();
        $this->assign('crazy',$crazy);
        //获取楼层信息
        $floor = D('Admin/Category')->getFloor();
        $this->assign('floor',$floor);
        $this->display();
    }
}