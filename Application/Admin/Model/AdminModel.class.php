<?php
namespace Admin\Model;

class AdminModel extends CommonModel
{
    //自定义字段(表结构)
    protected $fields = array('id', 'username', 'password');
    protected $_validate = array(
        array('username', 'require', '用户名不能为空'),
        array('username', '', '用户名不能重复', 1, 'unique'),
        array('password', 'require', '密码不能为空'),
    );
    protected $_auto = array(
        array('password', 'md5', 3, 'function')
    );

    public function _after_insert($data, $options)
    {
        $data2 = array(
            'admin_id' => $data['id'],
            'role_id' => I('post.role_id')
        );
        M('AdminRole')->add($data2);
    }

    public function getAdmins(){
        $count = $this->count();
        $pagesize = 3;
        $page = new \Think\Page($count,$pagesize);
        $pagestring = $page->show();
        $p = intval(I('get.p'));
        $data = $this->page($p,$pagesize)->field('a.*,c.role_name')->alias('a')
            ->join('left join __ADMIN_ROLE__ b on a.id=b.admin_id left join __ROLE__ c on b.role_id=c.id')
            ->select();
        return array(
            'data' => $data,
            'pagestring' => $pagestring
        );
    }

    public function deleteAdmin($id){
        $this->startTrans();
        if(!$this->delete($id)){
            $this->rollback();
            return false;
        }
        $res = M('AdminRole')->where('admin_id='.$id)->delete();
        if(!$res){
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    public function updateAdmin($data){
        $role_id = intval(I('post.role_id'));
        if(!$this->save($data)){
            return false;
        }
        $res = M('AdminRole')->where('admin_id='.$data['id'])->setField('role_id',$role_id);
        if(!res){
            return false;
        }
        return true;
    }

    public function getOneAdmin($id){
        return $this->field('a.*,b.role_id')->alias('a')
            ->join('left join __ADMIN_ROLE__ b on a.id=b.admin_id')
            ->where('a.id='.$id)
            ->find();
    }

    public function checkLogin($username,$password){
        $info = $this->where("username='$username'")->find();
        if(!$info){
            $this->error = '用户名不存在';
            return false;
        }
        if($info['password'] != md5($password)){
            $this->error = '密码错误';
            return false;
        }
        return true;
    }
}