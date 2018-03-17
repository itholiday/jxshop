<?php
namespace Admin\Controller;

class AdminController extends CommonController{
    public function add(){
        if(IS_GET){
            $r = D('Role');
            $role = $r->select();
            $this->assign('role',$role);
            $this->display();
            exit();
        }

        $m = D('Admin');
        $data = $m->create();
        if(!$data){
            $this->error($m->getError());
        }
        $m->add($data);
        $this->success('添加管理员成功');
    }

    public function index(){
        $a = D('Admin');
        $admins = $a->getAdmins();
        $this->assign('pagestring',$admins['pagestring']);
        $this->assign('admins',$admins['data']);
        $this->display();
    }

    public function del(){
        $id = intval(I('get.id'));
        if($id<=1){
            $this->error('参数操作错误');
        }
        $m = D('Admin');
        $res = $m->deleteAdmin($id);
        if(!$res){
            $this->error($m->getError());
        }else{
            $this->success('用户删除成功');
        }
    }

    public function edit(){
        $m = D('Admin');
        $id = intval(I('get.id'));
        if(IS_GET){
            $info = $m->getOneAdmin($id);
            $role = D('Role')->select();
            $this->assign('role',$role);
            $this->assign('info',$info);
            $this->display();
            exit();
        }
        if($id<=1){
            $this->error('参数错误');
        }

        $data = $m->create();
        if(!$data){
            $this->error($m->getError());
        }
        $res = $m->updateAdmin($data);
        if(!$res){
            $this->error($m->getError());
        }else{
            $this->success('用户修改成功');
        }
    }
}
