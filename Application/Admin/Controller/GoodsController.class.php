<?php
namespace Admin\Controller;

class GoodsController extends CommonController{
    public function add(){
        if(IS_GET){
            $m = D('Category');
            $cate = $m->getCateTree();
            $type = D('Type')->select();
            $start = time();
            $this->assign('start',$start);
            $this->assign('type',$type);
            $this->assign('cate',$cate);
            $this->display();
            exit();
        }
        $m = D('Goods');
        $data = $m->create();
        if(!data){
            $this->error($m->getError());
        }
        $id = $m->add($data);
        if(!$id){
            $this->error($m->getError());
        }
        $this->success('商品添加成功');
    }
    public function index(){
        if(IS_GET){
            $m = D('Goods');
            $data = $m->listData();
            $cate = D('Category')->getCateTree();
            $this->assign('cate',$cate);
            $this->assign('data',$data['data']);
            $this->assign('pagestring',$data['pagestring']);
            $this->display();
        }
    }
    public function del(){
        $id = intval(I('get.id'));
        $m = D('Goods');
        $res = $m->checkDelete($id);
        if($res){
            $res1 = $m->deleteGoods($id);
        }else{
            $res1 = $m->trashGoods($id);
        }
        if(!$res1){
            $this->error($m->getError());
        }
        $this->success('删除成功');
    }
    public function recover(){
        $id = intval(I('get.id'));
        $m = D('Goods');
        $res = $m->where('id='.$id)->setField('isdel',1);
        if(!$res){
            $this->error($m->getError());
        }
        $this->success('恢复成功');
    }
    public function trash(){
        $m = D('Goods');
        $data = $m->listData(true);
        $this->assign('data',$data['data']);
        $this->assign('pagestring',$data['pagestring']);
        $this->display();
    }
    public function edit(){
        if(IS_GET){
            $m = D('Category');
            $g = D('Goods');
            $id = intval(I('get.id'));
            $info = $g->find($id);
            $info['goods_body'] = htmlspecialchars_decode($info['goods_body']);
            $e = D('GoodsCate');
            $ext_ids = $e->getExtCate($id,true);
            foreach ($ext_ids as $value){
                $ext_list[] = $value['cate_id'];
            }
            if(!$ext_list){
                $ext_list = array(null);
            }
            $this->assign('ext_list',$ext_list);
            $this->assign('info',$info);

            //找商品的属性信息
            $type = D('Type')->select();
            $this->assign('type',$type);

            $ga = D('GoodsAttr');
            $attr = $ga->alias('a')->field('a.*,b.attr_name,b.attr_type,b.attr_input_type,b.attr_value')
                ->join('left join __ATTR__ b on a.attr_id=b.id')->where('a.goods_id='.$id)
                ->select();
            foreach($attr as $key => $value){
                if($value['attr_input_type'] == 2){
                    $attr[$key]['attr_value'] = explode(',',$value['attr_value']);
                }
            }
            foreach ($attr as $key => $value){
                $attr_list[$value['attr_id']][] = $value;
            }
            $this->assign('attr',$attr_list);

            $goods_imgs = M('GoodsImg')->where('goods_id='.$id)->select();
            $this->assign('goods_img_list',$goods_imgs);
            $cate = $m->getCateTree();
            $start = time();
            $this->assign('start',$start);
            $this->assign('cate',$cate);
            $this->display();
            exit();
        }
        $m = D('Goods');
        $data = $m->create();
        if(!data){
            $this->error($m->getError());
        }
        $res = $m->updateGoods($data);
        if(!$res){
            $this->error($m->getError());
        }else{
            $this->success('商品更新成功');
        }
    }

    public function showAttr(){
        $type_id = I('post.type_id');
        if($type_id<=0){
            echo 'no data';exit();
        }
        $data = D('Attr')->where('type_id='.$type_id)->select();
        foreach ($data as $key => $value){
            if($value['attr_type'] == 2){
                $data[$key]['attr_value'] = explode(',',$value['attr_value']);
            }
        }
        $this->assign('data',$data);
        $this->display();
    }

    public function delImg(){
        $goods_img_id = intval(I('request.img_id'));
        $info = M('GoodsImg')->find($goods_img_id);
        if(!$info){
            $this->ajaxReturn(array('status'=>0,'msg'=>'参数错误'));
        }
        $res = M('GoodsImg')->delete($goods_img_id);
        if(unlink($info['goods_img']) &&
        unlink($info['goods_thumb']) && $res)
        $this->ajaxReturn(array('status'=>1,'msg'=>'删除成功'));
    }

    public function setNumber(){
        if(IS_GET){
            $goods_id = intval(I('get.id'));
            $ga = D('GoodsAttr');
            //商品单选属性
            $attr = $ga->getGoodsAttr($goods_id);
            if(!$attr){
                $info = D('Goods')->where('id='.$goods_id)->find();
                $this->assign('info',$info);
                $this->display('nosigle');
                exit();
            }
            $info = M('GoodsNumber')->where('goods_id='.$goods_id)->select();
            if(!$info){
                $info = array('goods_number'=>0);
            }
            $this->assign('info',$info);
            $this->assign('attr',$attr);
            $this->display();
            exit();
        }
        $attr = I('post.attr');
        $goods_number = I('post.goods_number');
        $goods_id = I('post.goods_id');
        if(!attr){
            D('Goods')->where('id='.$goods_id)->setField('goods_number',$goods_number);
            $this->success('更新库存成功');
            exit();
        }
        foreach($goods_number as $key => $value){
            $tmp = array();
            foreach ($attr as $k => $v){
                $tmp[] = $v[$key];
            }
            sort($tmp);
            $goods_attr_ids = implode(',',$tmp);
            if(in_array($goods_attr_ids,$has)){
                unset($goods_number[$key]);
                continue;
            }
            $has[] = $goods_attr_ids;
            $list[] = array(
                'goods_id' => $goods_id,
                'goods_number' => $value,
                'goods_attr_ids' => $goods_attr_ids
            );
        }
        M('GoodsNumber')->where('goods_id='.$goods_id)->delete();
        M('GoodsNumber')->addAll($list);
        $count = array_sum($goods_number);
        D('Goods')->where('id='.$goods_id)->setField('goods_number',$count);
        $this->success('更新库存成功');
    }
}