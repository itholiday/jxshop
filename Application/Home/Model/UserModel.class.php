<?php
namespace Home\Model;

class UserModel extends CommonModel{
    protected $fields = array('id','username','password','salt','tel',
        'status','email','active_code','openid');
    public function regist($username,$password,$tel){
        $info = $this->where("username='$username'")->find();
        if($info){
            $this->error = '用户名已存在';
            return false;
        }
        $salt = mt_rand(100000,999999);
        $pass = md5(md5($password).$salt);
        $hastel = $this->where('tel='.$tel)->find();
        if($hastel){
            $this->error = '手机号已存在';
            return false;
        }
        $data = array('username'=>$username,'password'=>$pass,'salt'=>$salt,'tel'=>$tel,'status'=>1);
        return $this->add($data);
    }

    public function registbymail($username,$password,$email){
        $active_code = uniqid();
        $info = $this->where("username='$username'")->find();
        if($info){
            $this->error = '用户名已存在';
            return false;
        }
        if($info['email'] == $email){
            $this->error = '邮箱已存在';
            return false;
        }
        $salt = mt_rand(100000,999999);
        $pass = md5(md5($password).$salt);
        $data = array('username'=>$username,'password'=>$pass,'salt'=>$salt,'status'=>1,'email'=>$email,'active_code'=>$active_code);
        $res = $this->add($data);
        if($res){
            $body = "http://www.jxshop.com".U('user/active','user_id='.$res.'&active_code='.$active_code);
            sendmail($email,$username.'，请激活您的账号',$body);
        }
        return true;
    }


    public function login($username,$password){
        $data = array(
            'username' => $username,
            'password' => $password,
        );
        $res = get_data($data);
        if(!$res['status']){
            $this->error = $res['msg'];
            return false;
        }
        session('user',$res['data']);
        session('user_id',$res['data']['id']);
        D('Cart')->cookie2db();
        return true;
    }

    public function qqLogin($user,$openid){
        $userinfo = $this->where('openid='.$openid)->find();
        if($userinfo){
            $this->error = '该用户已存在';
        }
        $salt = mt_rand(100000,999999);
        $data = array(
            'username' => 'qquser_'.mt_rand(1000,9999).$user['nickname'],
            'password' => md5(md5('12345').$salt),
            'salt' => $salt,
            'status' => 1,
            'openid' => $openid
        );
        $res = $this->add($data);
        session('user_id',$res);
        session('user',$data);
        D('Cart')->cookie2db();
        return true;
    }

    public function checkLogin(){
        $user_id = session('user_id');
        if(!$user_id){
            $this->error = '转去登陆';
        }
    }
}