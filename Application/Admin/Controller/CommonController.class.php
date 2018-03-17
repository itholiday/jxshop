<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller {
    //是否进行权限验证
    public $is_check_rule = true;
    //登录用户权限信息
    public $user = array();

    public function __construct()
    {
        header('Content-type:text/html;charset=utf-8');
        parent::__construct();

        $admin = cookie('admin');

        if(!$admin){
            $this->success('没有登录，转去登陆',U('login/login'));exit();
        }

        //读缓存文件
        $this->user = S('user_'.$admin['id']);

        if(!$this->user['role_id']){
            $this->user = $admin;
            $role_info = M('AdminRole')->where('admin_id='.$admin['id'])->find();
            $role_id = $role_info['role_id'];
            $this->user['role_id'] = $role_id;

            //根据用户角色获取权限列表
            if($role_id == 1){
                $this->is_check_rule = false;
                $role_rules = D('Rule')->select();
            }else{
                $role_rule_ids = M('RoleRule')->where('role_id='.$role_id)->select();
                if(!$role_rule_ids){
                    $this->error('无权限',U('login/login'));
                }
                foreach($role_rule_ids as $value){
                    $rulelist[] = $value['rule_id'];
                }
                $rules = implode(',',$rulelist);
                $role_rules = D('Rule')->where("id in ($rules)")->select();
            }
        }

        foreach($role_rules as $value){
            $this->user['rules'][] = strtolower($value['module_name'].'/'.$value['controller_name'].'/'.$value['action_name']);
            if($value['is_show']){
                $this->user['menus'][] = $value;
            }
        }

        S('user_'.$admin['id'],$this->user);

        if($this->user['role_id'] == 1){
            $this->is_check_rule = false;
        }

        //如果需要权限认证，根据请求地址判断当前访问地址是否有权限，根据返回类型返回不同错误信息
        if($this->is_check_rule){
            $this->user['rules'][] = 'admin/index/index';
            $this->user['rules'][] = 'admin/index/top';
            $this->user['rules'][] = 'admin/index/menu';
            $this->user['rules'][] = 'admin/index/main';
            $url = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
            if(!in_array($url,$this->user['rules'])){
                if(IS_AJAX){
                    $this->ajaxReturn(array('status'=>0 , 'msg'=>'没有权限'));
                }else{
                    echo '没有权限';exit();
                }
            }
        }
    }
    public function _empty(){
        echo '非法操作(空)';
    }
}