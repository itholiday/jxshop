<?php
namespace Home\Controller;
class GoodsController extends CommonController {
    public function index(){
        $goods_id = intval(I('get.goods_id'));
        if($goods_id<=0){
            $this->redirect('Index/index');
        }
        $g = D('Admin/Goods');
        $goods = $g->where('is_sale=1 and isdel=1 and id='.$goods_id)->find();
        if(!$goods){
            $this->redirect('Index/index');
        }

        $goods['goods_body']=htmlspecialchars_decode($goods['goods_body']);
        $pic = M('GoodsImg')->where('goods_id='.$goods_id)->select();

        $attr = M('GoodsAttr')->alias('a')->field('a.*,b.attr_name,b.attr_type')->join('left join __ATTR__ b on a.attr_id=b.id')
            ->where('a.goods_id='.$goods_id)->select();
        foreach ($attr as $key => $value){
            if($value['attr_type']==1){
                $unique[] = $value;
            }else{
                $sigle[$value['attr_id']][] = $value;
            }
        }

        $c = D('Comment');
        $comment = $c->getList($goods_id);

        $buyer = M('Impression')->where('goods_id='.$goods_id)->order('count desc')
            ->select();
        $this->assign('buyer',$buyer);
        $this->assign('comment',$comment);
        $this->assign('unique',$unique);
        $this->assign('sigle',$sigle);
        $this->assign('pic',$pic);
        $this->assign('goods',$goods);
        $this->display();
    }

    public function comment(){
        $user = new UserController();
        $user->checkLogin();
        $m = D('Comment');
        $data = $m->create();
        if(!$data){
            $this->error('评论失败');
        }
        $m->add($data);
        $this->success('评论成功');
    }

    public function good(){
        $comment_id = intval(I('post.comment_id'));
        $m = D('Comment');
        $m->where('id='.$comment_id)->setInc('good_number');
        $good_number = $m->field('good_number')->find($comment_id);
        $this->ajaxReturn(array('good_number'=>$good_number['good_number']));
    }
}