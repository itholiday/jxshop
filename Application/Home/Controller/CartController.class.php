<?php
namespace Home\Controller;
class CartController extends CommonController {
    public function addCart(){
        $attr = I('post.attr');
        $goods_id = intval(I('post.goods_id'));
        $goods_count = intval(I('post.goods_count'));
        $cart = D('Cart');
        $res = $cart->addCart($goods_id,$goods_count,$attr);
        if(!$res){
            $this->error($cart->getError());
        }
        $this->success('添加购物车成功');
    }

    public function index(){
        $c = D('Cart');
        $data = $c->getList();
        $this->assign('data',$data);
        $total = $c->getTotal($data);
        $this->assign('total',$total);
        $this->display();
    }

    public function dels(){
        $goods_id = intval(I('get.goods_id'));
        $goods_attr_ids = I('get.goods_attr_ids');
        D('Cart')->dels($goods_id,$goods_attr_ids);
        $this->success('删除成功');
    }

    public function updateCart(){
        $goods_id = intval(I('post.goods_id'));
        $goods_attr_ids = I('post.goods_attr_ids');
        $goods_count = intval(I('post.goods_count'));
        D('Cart')->updateCart($goods_id,$goods_attr_ids,$goods_count);
    }
}