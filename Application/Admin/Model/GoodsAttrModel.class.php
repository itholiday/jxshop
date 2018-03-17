<?php
/**
 * @author:haive
 */
namespace Admin\Model;

class GoodsAttrModel extends CommonModel {
    protected $fields = array('id','attr_name','attr_id','attr_values');

    public function insertAttr($attr,$goods_id){
        if(!$attr) return true;
        foreach ($attr as $key => $value){
            foreach ($value as $k => $v){
                $list[] = array(
                    'goods_id' => $goods_id,
                    'attr_id' => $key,
                    'attr_values' => $v
                );
            }
        }
        return M('GoodsAttr')->addAll($list);
    }

    public function getGoodsAttr($goods_id){
        $data = $this->alias('a')->field('a.*,b.attr_name,b.attr_type,b.attr_input_type,b.attr_value')
            ->join('left join __ATTR__ b on a.attr_id=b.id')
            ->where("a.goods_id=$goods_id and b.attr_type=2")->select();
        foreach ($data as $key => $value){
            $list[$value['attr_id']][] = $value;
        }
        return $list;
    }
}