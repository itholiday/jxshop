<?php
namespace Admin\Model;

class RoleModel extends CommonModel
{
    //自定义字段(表结构)
    protected $fields = array('id', 'role_name');
    protected $_validate = array(
        array('role_name','require','角色名称不能为空'),
        array('role_name','','角色名称不能重复',1,'unique')
    );

    public function listData(){
        $count = $this->count();
        $pagesize = 10;
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
        if($data['id']<=1){
            $this->error = '参数错误';
        }
        return $this->save($data);
    }

    public function getRoleRule($id){
        return M('RoleRule')->where('role_id='.$id)->select();
    }

    //权限更新后，超级管理员的缓存需要删除更新
    public function flushAdmin(){
        $admininfo = M('AdminRole')->where('role_id=1')->select();
        foreach($admininfo as $value){
            S('user_'.$value['admin_id'],null);
        }
    }
}