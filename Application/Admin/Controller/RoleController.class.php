<?php
namespace Admin\Controller;

class RoleController extends CommonController
{
    public function add()
    {
        if (IS_GET) {
            $this->display();
        } else {
            $m = D('Role');
            $data = $m->create();
            if (!$data) {
                $this->error($m->getError());
            } else {
                $m->add($data);
                $this->success('添加角色成功');
            }
        }
    }

    public function index(){
        if(IS_GET){
            $m = D('Role');
            $data = $m->listData();
            $this->assign('data',$data['data']);
            $this->assign('pagestring',$data['pagestring']);
            $this->display();
        }
    }

    public function del(){
        $id = intval(I('get.id'));
        $m = D('Role');
        $res = $m->delete($id);
        if($res){
            $this->success('删除成功',U('index'));
        }else{
            $this->error($m->getError(),U('index'));
        }
    }

    public function edit(){
        $m = D('Role');
        $id = intval(I('get.id'));
        if(IS_GET){
            $role = $m->find($id);
            $this->assign('role',$role);
            $this->display();
            exit();
        }

        $res = $m->updateRole();
        if($res){
            $this->success('更新角色成功',U('index'));
        }else{
            $this->error($m->getError(),U('index'));
        }
    }

    public function disfetch(){
        $id = intval(I('get.id'));
        if($id<=1){
            $this->error('参数错误');
        }
        $rolemodel = D('Role');
        if(IS_GET){
            $rulem = D('Rule');
            $role_rule = $rolemodel->getRoleRule($id);
            foreach ($role_rule as $value){
                $rulelist[] = $value['rule_id'];
            }
            $cate = $rulem->getCateTree();
            $this->assign('rules',$rulelist);
            $this->assign('cate',$cate);
            $this->display();
            exit();
        }
        $rule_ids = I('post.rule');
        foreach($rule_ids as $value){
            $data[] = array(
                'role_id' => $id,
                'rule_id' => intval($value)
            );
        }
        $rolerule = M('RoleRule');
        $rolerule->where('role_id='.$id)->delete();
        $res = $rolerule->addAll($data);

        $userinfo = M('AdminRole')->where('role_id='.$id)->select();
        foreach($userinfo as $value){
            S('user_'.$userinfo['admin_id'],null);
        }

        if(!$res){
            $this->error('更新角色权限失败');
        }
        $this->success('更新角色权限成功',U('index'));
    }
}