<?php
namespace Admin\Controller;

class RuleController extends CommonController{
    public function add(){
        if(IS_GET){
            $m = D('Rule');
            $cate = $m->getCateTree();
            $this->assign('cate',$cate);
            $this->display();
        }else{
            $m = D('Rule');
            $data = $m->create();
            if(!$data){
                $this->error($m->getError());
            }else{
                $m->add($data);
                $this->success('添加权限成功');
            }
        }
    }
    //权限列表
    public function index(){
        $m = D('Rule');
        $cate = $m->getCateTree();
        $this->assign('cate',$cate);
        $this->display();
    }
    public function del(){
        $id = intval(I('get.id'));
        $m = D('Rule');
        if($m->checkHasSubcate($id)){
            $this->error('当前权限存在子权限，不可被删除',U('index'));
        }else{
            $m->delete($id);
            $this->success('权限删除成功');
        }
    }
    public function edit(){
        $id = intval(I('get.id'));
        $m = D('Rule');
        $info = $m->find($id);
        if(IS_GET){
            $cate = $m->getCateTree();
            $this->assign('info',$info);
            $this->assign('cate',$cate);
            $this->display('edit');
            exit;
        }

        $m = D('Rule');
        $cate = $m->getCateTree($id);
        $deny_id = array();
        foreach($cate as $k => $v){
            $deny_id[] = $v['id'];
        }
        $deny_id[] = $id;

        $data = $m->create();

        if($data['parent_id'] == $id){
            $data['parent_id'] = $info['parent_id'];
        }
        if(in_array($data['parent_id'],$deny_id)){
            $this->error('非法操作,上级分类不要选择自己或自己的子分类');
        }
        if(!$data){
            $this->error($m->getError());
        }
        if($m->save($data)){
            $this->success('更新成功');
        }else{
            $this->error('更新失败');
        }
    }
}