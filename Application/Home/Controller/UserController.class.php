<?php
namespace Home\Controller;
class UserController extends CommonController {
    public function test(){
        $res = get_data(array('username'=>'haive','password'=>'12345','a'=>'login'));
        dump($res);
    }
    public function callback(){
        require_once('qqconnect/API/qqConnectAPI.php');
        $qc = new \QC();
        $access_token = $qc->qq_callback();
        $openid = $qc->get_openid();
        $qc = new \QC($access_token,$openid);
        $user = $qc->get_user_info();
        $m = D('User');
        $res = $m->qqLogin($user,$openid);
        if(!$res){
            $this->error($m->getError());
        }
        $this->success('登陆成功',U('index/index'));
    }
    public function regist(){
        if(IS_GET){
            $this->display();
            exit();
        }
        $username = I('post.username');
        $password = I('post.password');
        $checkcode = I('post.checkcode');
        $tel = I('post.tel');
        $telcode = I('post.telcode');
        $codeinfo = session('codeinfo');
        $config = array('length'=>5);
        $verify = new \Think\Verify($config);
        if(!$verify->check($checkcode,'regist')){
            $this->ajaxReturn(array('status'=>0,'msg'=>'验证码错误'));
        }
        if($codeinfo['telcode'] != $telcode){
            $this->ajaxReturn(array('status'=>0,'msg'=>'短信验证码错误'));
        }
        $co = $codeinfo['remain']*60+$codeinfo['sendtime'];
        if(time()>$co){
            $this->ajaxReturn(array('status'=>0,'msg'=>'短信验证码已过期'));
        }
        $user = D('User');
        $res = $user->regist($username,$password,$tel);
        if(!$res){
            $this->ajaxReturn(array('status'=>0,'msg'=>$user->getError()));
        }
        $this->ajaxReturn(array('status'=>1,'msg'=>'注册成功'));
    }

    public function oauth(){
        require_once("qqconnect/API/qqConnectAPI.php");
        $qc = new \QC();
        $qc->qq_login();

    }
    public function registbymail(){
        if(IS_GET){
            $this->display();
            exit();
        }
        $username = I('post.username');
        $password = I('post.password');
        $email = I('post.email');
        $user = D('User');
        $res = $user->registbymail($username,$password,$email);
        if(!$res){
            $this->ajaxReturn(array('status'=>0,'msg'=>$user->getError()));
        }
        $this->ajaxReturn(array('status'=>1,'msg'=>'注册成功'));
    }

    public function active(){
        $user_id = intval(I('post.user_id'));
        $active_code = I('post.active_code');
        $res = D('User')->where('id='.$user_id.' and active_code='.$active_code)
            ->find();
        $res2 = D('User')->where('id='.$user_id)->setField('status',1);
        if(!$res || !$res){
            $this->error('激活失败',U('/'));
        }else{
            $this->success('激活成功',U('/'));
        }
    }
    public function getCaptcha(){
        $type = I('get.type');
        $config = array('length'=>5);
        $verify = new \Think\Verify($config);
        $verify->entry($type);
    }

    public function login(){
        if(IS_GET){
            $this->display();
            exit();
        }
        $username = I('post.username');
        $password = I('post.password');
        $checkcode = I('post.checkcode');
        $verify = new \Think\Verify();
        if(!$verify->check($checkcode,'login')){
            $this->error('验证码错误');
        }
        $user = D('User');
        $res = $user->login($username,$password);
        if(!$res){
            $this->error($user->getError());
        }
        $this->success('登陆成功',U('/'));
    }

    public function loginout(){
        session('user',null);
        session('user_id',null);
        $this->success('登出成功',U('/'));
    }

    public function getTelCode(){
        $to = I('post.tel');
        if(!$to){
            $this->ajaxReturn(array('status'=>0));
        }
        $code = mt_rand(1000,9999);
        $datas = array($code,5);
        $tempId = 1;
        $res = sendTemplateSMS($to,$datas,$tempId);
        if(!$res){
            $this->ajaxReturn(array('status'=>0));
        }
        $data = array(
            'telcode' => $code,
            'sendtime' => time(),
            'remain' => 5
        );
        session('codeinfo',$data);
        $this->ajaxReturn(array('status'=>1));
    }
}