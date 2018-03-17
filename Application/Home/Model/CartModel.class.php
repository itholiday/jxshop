<?php
namespace Home\Model;

class CartModel extends CommonModel{
    protected $fields = array('id','user_id','goods_id','goods_attr_ids','goods_count');
    public function addCart($goods_id,$goods_count,$attr){
        $user_id = session('user_id');
        sort($attr);
        $goods_attr_ids = $attr ? implode(',',$attr) : '';
        $res = $this->checkGoodsNumber($goods_id,$goods_attr_ids,$goods_count);
        //如果登录了
        if($user_id){
            $where = array('user_id'=>$user_id,'goods_id'=>$goods_id,'goods_attr_ids'=>$goods_attr_ids);
            if($this->where($where)->find()){
                $this->where($where)->setInc('goods_count',$goods_count);
            }else{
                $where['goods_count'] = $goods_count;
                $this->add($where);
            }
        }else{
            $cart = cookie('cart');
            $key = $goods_id . '-' . $goods_attr_ids;
            if(array_key_exists($key,$cart)){
                $cart[$key] += $goods_count;
            }else{
                $cart[$key] = $goods_count;
            }
            cookie('cart',$cart);
        }
        return true;
    }

    public function checkGoodsNumber($goods_id,$goods_attr_ids,$goods_count){
        if(!$goods_attr_ids){
            $info = D('Admin/Goods')->find($goods_id);
            if($info['goods_number'] < $goods_count){
                return false;
            }
        }else{
            $info = M('GoodsNumber')->where('goods_id='.$goods_id.' and goods_attr_ids='."'$goods_attr_ids'")->find();
            if(!$info || $info['goods_number']<$goods_count){
                return false;
            }
        }
        return true;
    }

    public function cookie2db(){
        $user_id = session('user_id');
        if(!$user_id) return false;
        $cart = cookie('cart');
        foreach ($cart as $key => $value){
            $tmp = explode('-',$key);
            $map = array(
                'user_id' => $user_id,
                'goods_attr_ids' => $tmp[1],
                'goods_id' => $tmp[0]
            );
            $info = $this->where($map)->find();
            if($info){
                $this->where($map)->setInc('goods_count',$value);
            }else{
                $map['goods_count'] = $value;
                $this->save($map);
            }
        }
        cookie('cart',null);
    }

    public function getList(){
        $user_id = session('user_id');
        //登陆，从库中找购物车信息
        if($user_id){
            $data = $this->where('user_id='.$user_id)->select();
        }else{
            $cart = cookie('cart');
            foreach ($cart as $key => $value){
                $tmp = explode('-',$key);
                $data[] = array(
                    'goods_id' => $tmp[0],
                    'goods_attr_ids' => $tmp[1],
                    'goods_count' => $value
                );
            }
        }
        $g = D('Admin/Goods');
        foreach ($data as $key => $value){
            $goods = $g->where('id='.$value['goods_id'])->find();
            //如果正在促销，将商品价格设置为促销价格
            if($goods['cx_price']>0 && $goods['start']<time() && $goods['end']>time()){
                $goods['shop_price'] = $goods['cx_price'];
            }
            $data[$key]['goods'] = $goods;
            if($value['goods_attr_ids']){
                $attr = M('GoodsAttr')->alias('a')->join('left join __ATTR__ b on a.attr_id=b.id')
                    ->field('a.attr_values,b.attr_name')
                    ->where("a.id in ({$value['goods_attr_ids']})")->select();
                $data[$key]['attr'] = $attr;
            }
        }
        return $data;
    }

    public function getTotal($data){
        $count = $price = 0;
        foreach ($data as $key => $value){
            $count += $value['goods_count'];
            $price += $value['goods_count'] * $value['goods']['shop_price'];
        }
        return array('count'=> $count,'price'=>$price);
    }

    public function dels($goods_id,$goods_attr_ids){
        $goods_attr_ids = $goods_attr_ids ? $goods_attr_ids : '';
        $user_id = session('user_id');
        if($user_id){
            $where = "user_id = $user_id and goods_id = $goods_id and goods_attr_ids = '$goods_attr_ids'";
            $this->where($where)->delete();
        }else{
            $cart = cookie('cart');
            $key = $goods_id . '-' . $goods_attr_ids;
            unset($cart[$key]);
            cookie('cart',$cart);
        }
    }

    public function updateCart($goods_id,$goods_attr_ids,$goods_count){
        if($goods_count<=0){
            return false;
        }
        $goods_attr_ids = $goods_attr_ids ? $goods_attr_ids : '';
        $user_id = session('user_id');
        //如果登陆了,更新数据库
        if($user_id){
            $this->where('user_id='.$user_id.' and goods_id='.$goods_id.' and goods_attr_ids='.$goods_attr_ids)->setField('goods_count',$goods_count);
        }else{
            $cart = cookie('cart');
            $key = $goods_id . '-' . $goods_attr_ids;
            $cart[$key] = $goods_count;
            cookie('cart',$cart);
        }
        return true;
    }

}