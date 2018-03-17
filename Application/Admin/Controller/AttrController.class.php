<?php
namespace Admin\Controller;

class AttrController extends CommonController
{
    protected $_m;
    protected function model(){
        if(!$this->_m){
            $this->_m = D('Attr');
        }
        return $this->_m;
    }

    public function add(){
        if(IS_GET){
            $m = D('Type');
            $types = $m->select();
            $this->assign('types',$types);
            $this->display();
            exit;
        }
        $data = $this->model()->create();
        if(!$data){
            $this->error($this->model()->getError());
        }
        $res = $this->model()->add($data);
        if(!$res){
            $this->error('添加商品属性失败');
        }
        $this->success('添加成功');
    }

    public function index(){
        if(IS_GET){
            $data = $this->model()->listData();
            $this->assign('data',$data['data']);
            $types = D('Type')->select();
            $this->assign('types',$types);
            $this->assign('pagestring',$data['pagestring']);
            $this->display();
        }
    }

    public function del(){
        $id = intval(I('get.id'));
        $res = $this->model()->delete($id);
        if($res){
            $this->success('删除成功',U('index'));
        }else{
            $this->error($this->model()->getError(),U('index'));
        }
    }

    public function edit(){
        $id = intval(I('get.id'));
        if(IS_GET){
            $m = D('Type');
            $types = $m->select();
            $this->assign('types',$types);
            $role = $this->model()->find($id);
            $this->assign('role',$role);
            $this->display();
            exit();
        }
        $res = $this->model()->updateAttr();
        if($res){
            $this->success('更新属性成功',U('index'));
        }else{
            $this->error($this->model()->getError(),U('index'));
        }
    }
}