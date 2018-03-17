<?php
namespace Home\Controller;
class CategoryController extends CommonController {
    public function index(){
        $m = D('Admin/Goods');
        $cate_id = intval(I('get.id'));
        $data = $m->getList($cate_id,$limit=8);
        $this->assign('data',$data);
        $this->display();
    }
}