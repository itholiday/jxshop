<?php
namespace Admin\Model;

class GoodsCateModel extends CommonModel {
    protected $fields = array('id','goods_id','cate_id');
    public function getExtCate($goods_id,$onlyCateId = false){
        if($onlyCateId){
            return $this->field('cate_id')->where('goods_id='.$goods_id )->select();
        }else{
            return $this->where('goods_id='.$goods_id )->select();
        }
    }


    public function updateExtCate($goods_id,$ext_cate_ids){
        $res = $this->where('goods_id='.$goods_id)->delete();
        if(!$res) return false;
        foreach ($ext_cate_ids as $value){
            if($value != 0){
                $list[] = array(
                    'goods_id' => $goods_id,
                    'cate_id' => $value
                );
            }
        }
        return $this->addAll($list);
    }
}