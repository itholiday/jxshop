<?php
namespace Admin\Model;

class RuleModel extends CommonModel{
    //自定义字段(表结构)
    protected $fields = array(
        'id','rule_name','module_name','controller_name','action_name','parent_id','is_show'
    );
    protected $_validate = array(
        array('rule_name','require','权限名称不能为空',1),
        array('module_name','require','模块名称不能为空',1),
        array('controller_name','require','控制器名称不能为空',1),
        array('action_name','require','方法名称不能为空',1)
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

    public function _after_insert($data, $options)
    {
        $admin = D('Role');
        $admin->flushAdmin();
    }
}