<?php
namespace Admin\Controller;

class TypeController extends CommonController
{
    public function add()
    {
        if (IS_GET) {
            $this->display();
        } else {
            $m = D('Type');
            $data = $m->create();
            if (!$data) {
                $this->error($m->getError());
            } else {
                $m->add($data);
                $this->success('添加商品类型成功');
            }
        }
    }

    public function index(){
        if(IS_GET){
            $m = D('Type');
            $data = $m->listData();
            $this->assign('data',$data['data']);
            $this->assign('pagestring',$data['pagestring']);
            $this->display();
        }
    }

    public function del(){
        $id = intval(I('get.id'));
        $m = D('Type');
        $res = $m->delete($id);
        if($res){
            $this->success('删除成功',U('index'));
        }else{
            $this->error($m->getError(),U('index'));
        }
    }

    public function edit(){
        $m = D('Type');
        $id = intval(I('get.id'));
        if(IS_GET){
            $role = $m->find($id);
            $this->assign('role',$role);
            $this->display();
            exit();
        }

        $res = $m->updateRole();
        if($res){
            $this->success('更新商品类型成功',U('index'));
        }else{
            $this->error($m->getError(),U('index'));
        }
    }
}