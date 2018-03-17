<?php
namespace Admin\Model;

class CategoryModel extends CommonModel{
    //自定义字段(表结构)
    protected $fields = array('id','cname','parent_id','isrec');
    protected $_validate = array(
        array('cname','require','分类名称不能为空',1)
    );
    public function getCateTree($id=0){
        $data = $this->select();
        return $this->getTree($data,$id);
    }
    public function getTree($data,$id=0,$level=1,$cache=true){
        static $cate = array();
        if(!$cache){
            $cate = array();
        }
        foreach ($data as $key => $value){
            if($value['parent_id'] == $id){
                $value['level'] = $level;
                $cate[] = $value;
                $this->getTree($data,$value['id'],$level+1);//如果只获得子分类数据，需在遍历前将$cate清空
            }
        }
        return $cate;
    }
    public function checkHasSubcate($id){
        $cate = $this->getCateTree($id);
        return count($cate) > 0 ? true : false;
    }
    public function getSubTree($id){
        $data = $this->select();
        return $this->getTree($data,$id,1,false);
    }

    //获取楼层信息，包括楼层的分类信息以及商品信息
    public function getFloor()
    {
        //1、获取所有的顶级分类
        $data = $this->where('parent_id=0')->select();
        //根据顶级分类的标识获取对应的二级分类及推荐的二级分类信息
        foreach ($data as $key => $value) {
            //获取二级分类信息
            $data[$key]['son']=$this->where('parent_id='.$value['id'])->select();
            //获取推荐的二级分类信息
            $data[$key]['recson']=$this->where('isrec =1 and parent_id='.$value['id'])->select();
            //根据每一个楼层推荐的二级分类信息获取对应的商品数据
            foreach ($data[$key]['recson'] as $k => $v) {
                //$v代表的就是每一个推荐分类信息
                $data[$key]['recson'][$k]['goods']=$this->getGoodsByCateId($v['id']);
            }
        }
        return $data;
    }

    //根据分类ID标识获取对应的商品信息
    public function getGoodsByCateId($cate_id,$limit=8)
    {
        //1、获取当前分类下面子分类信息
        $children = $this->getSubTree($cate_id);
        //2、将当前分类的标识追加到对应的子分类中
        $children[]['id']=$cate_id;
        //3、将$children格式化为字符串的格式目的就是为了使用MySQL中的in语法
        foreach ($children as $key => $value){
            $list[] = $value['id'];
        }
        $children = implode(',',$list);
        //4、通过目前的分类信息获取商品数据
        $goods = D('Goods')->where("is_sale=1 and cate_id in ($children)")->limit($limit)->select();
        return $goods;
    }
}