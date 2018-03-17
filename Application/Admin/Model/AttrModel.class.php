<?php
namespace Admin\Model;

class AttrModel extends CommonModel
{
    //自定义字段(表结构)
    protected $fields = array('id', 'attr_name','type_id','attr_type','attr_input_type','attr_value');
    protected $_validate = array(
        array('attr_name', 'require', '属性名称不能为空'),
        array('attr_type', '1,2', '属性类型只能为唯一或单选', 1, 'in'),
        array('attr_input_type','1,2','属性值录入类型需为手工输入或列表选择',1,'in')
    );

    public function listData()
    {
        $count = $this->count();
        $pagesize = 10;
        $page = new \Think\Page($count, $pagesize);
        $pagestring = $page->show();
        $p = intval(I('get.p'));
        $data = $this->page($p, $pagesize)->select();
        return array(
            'pagestring' => $pagestring,
            'data' => $data
        );
    }

    public function updateAttr()
    {
        $data = $this->create();
        if (!$data) {
            $this->error = $this->getError();
            return false;
        }
        return $this->save($data);
    }
}