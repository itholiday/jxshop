<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {
    public function __construct()
    {
        parent::__construct();
        header('Content-type:text/html;charset=utf8');
        $cate = D('Admin/Category')->select();
        $this->assign('cate',$cate);
    }
}