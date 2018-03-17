<?php
namespace Home\Model;

class CommentModel extends CommonModel{
    protected $fields = array('id','user_id','goods_id','addtime','content','star','good_number');
    public function _before_insert(&$data, $options)
    {
        $data['addtime'] = time();
        $data['user_id'] = session('user_id');
    }

    public function _after_insert($data, $options)
    {
        $old = I('post.old');
        foreach ($old as $key => $value){
            M('Impression')->where('id='.$value)->setInc('count');
        }
        $name = I('post.name');
        $name = explode(',',$name);
        $name = array_unique($name);
        foreach ($name as $key => $value){
            if(!$value) continue;
            $where = array('goods_id'=>$data['goods_id'],'name'=>$value);
            $m = M('Impression');
            $res = $m->where($where)->find();
            if($res){
                $m->where($where)->setInc('count');
            }else{
                $where['count'] = 1;
                $m->add($where);
            }
        }
        M('Goods')->where('id='.$data['goods_id'])->setInc('plcount');
    }

    public function getList($goods_id){
        $count = $this->where('goods_id='.$goods_id)->count();
        $pagesize = 2;
        $page = new \Think\Page($count,$pagesize);
        $page->setConfig('is_anchor',true);
        $pagestring = $page->show();
        $p = intval(I('get.p'));
        $list = $this->alias('a')->field('a.*,b.username')->join('left join __USER__ b on a.user_id=b.id')->page($p,$pagesize)
            ->where('a.goods_id='.$goods_id)->order('id desc')->select();
        return array('list'=>$list,'page'=>$pagestring);
    }
}