<?php
namespace Admin\Model;

class TypeModel extends CommonModel
{
    //自定义字段(表结构)
    protected $fields = array('id', 'type_name');
    protected $_validate = array(
        array('type_name','require','类型名称不能为空'),
        array('type_name','','类型名称不能重复',1,'unique')
    );

    public function listData(){
        $count = $this->count();
        $pagesize = 2;
        $page = new \Think\Page($count,$pagesize);
        $pagestring = $page->show();
        $p = intval(I('get.p'));
        $data = $this->page($p,$pagesize)->select();
        return array(
            'pagestring' => $pagestring,
            'data' => $data
        );
    }

    public function updateRole(){
        $data = $this->create();
        if(!$data){
            $this->error = $this->getError();
            return false;
        }
        if($data['id']<=0){
            $this->error = '参数错误';
        }
        return $this->save($data);
    }
}