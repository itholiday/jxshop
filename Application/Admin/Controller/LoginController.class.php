<?php
/**
 * @author:haive
 */
namespace Admin\Controller;
use Think\Controller;

class LoginController extends Controller{
    public function login(){
        if(IS_GET){
            $this->display();
            exit();
        }
        $config = array('length'=>3);
        $verify = new \Think\Verify($config);
        $captcha = I('post.captcha');
        $username = I('post.username');
        $password = I('post.password');
        if(!$verify->check($captcha,'login')){
            $this->error('验证码错误');
        }
        $m = D('Admin');
        $res = $m->checkLogin($username,$password);
        if(!$res){
            $this->error($m->getError());
        }
        $admin = $m->where("username='$username'")->find();
        cookie('admin',$admin);
        $this->success('登陆成功',U('Index/index'));
    }

    public function getCaptcha(){
        $config = array('length'=>3);
        $verify = new \Think\Verify($config);
        $verify->entry('login');
    }

    public function loginOut(){
        cookie('admin',null);
        $this->success('注销成功，返回登陆界面','login/login');
    }

    public function clearCache(){
        $admin = cookie('admin');
        if(S('user_'.$admin['id'],null)){
            $this->success('清除缓存成功，返回');
        }
    }
}