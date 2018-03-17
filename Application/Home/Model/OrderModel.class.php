<?php
namespace Home\Model;

class OrderModel extends CommonModel{
    protected $fields = array('id','user_id','addtime','total_price','pay_status','name','address','tel');

    public function order(){
        $name = I('post.name');
        $address = I('post.address');
        $tel = I('post.tel');
        $cart = D('Cart');
        $data = $cart->getList();
        if(!$data){
            $this->error('购物车没有商品，去购物',U('/'));
        }
        $user_id = session('user_id');
        $total = $cart->getTotal($data);
        foreach($data as $key => $value) {
            $status = $cart->checkGoodsNumber($value['goods_id'], $value['goods_attr_ids'], $value['goods_count']);
            if (!$status) {
                $this->error = '库存不够';
                return false;
            }
        }
        $orderdata = array(
            'user_id' => $user_id,
            'addtime' => time(),
            'total_price' => $total['price'],
            'name' => $name,
            'address' => $address,
            'tel' => $tel
        );
        $order_id = $this->add($orderdata);
        $og = M('OrderGoods');
        foreach ($data as $key => $value){
            $order_goods_data[] = array(
                'order_id' => $order_id,
                'goods_id' => $value['goods_id'],
                'goods_attr_ids' => $value['goods_attr_ids'],
                'price' => $value['goods']['shop_price'],
                'goods_count' => $value['goods_count']
            );
        }
        $og->addAll($order_goods_data);
        //善后工作，减少库存、清空购物车,返回订单信息
        foreach($data as $key => $value){
            M('Goods')->where('id='.$value['goods_id'])
                ->setDec('goods_number',$value['goods_count']);
            M('Goods')->where('id='.$value['goods_id'])->setInc('sale_number',$value['goods_count']);
            if($value['goods_attr_ids']){
                $where = "goods_id={$value['goods_id']} and goods_attr_ids= '{$value['goods_attr_ids']}'";
                M('GoodsNumber')->where($where)->setDec('goods_number',$value['goods_count']);
            }
        }

        $user_Id = session('user_id');
        $cart->where('user_id='.$user_id)->delete();
        $orderdata['id'] = $order_id;
        return $orderdata;
    }
}