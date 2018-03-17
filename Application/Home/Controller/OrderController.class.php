<?php
namespace Home\Controller;
class OrderController extends CommonController {
    public function check(){
        $user = D('user');
        $user->checkLogin();
        $cart = D('Cart');
        $data = $cart->getList();
        $this->assign('data',$data);
        $total = $cart->getTotal($data);
        $this->assign('total',$total);
        $this->display();
    }

    public function order(){
        $order = D('Order');
        $res = $order->order();
        if(!$res){
            $this->error($order->getError());
        }
        if($res['pay_status']==1){
            $this->error('已经支付了该订单');
        }
        require_once '../pay/config.php';
        require_once '../pay/pagepay/service/AlipayTradeService.php';
        require_once '../pay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';

        //商户订单号，商户网站订单系统中唯一订单号，必填
//        $out_trade_no = trim($_POST['WIDout_trade_no']);
        $out_trade_no = $res['id'];

        //订单名称，必填
//        $subject = trim($_POST['WIDsubject']);
        $subject = $res['name'].'-'.$res['address'];

        //付款金额，必填
        $total_amount = 0.01;

        //商品描述，可空
        $body = $res['name'].'-'.$res['address'];

        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setOutTradeNo($out_trade_no);

        $aop = new \AlipayTradeService($config);

        /**
         * pagePay 电脑网站支付请求
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @param $return_url 同步跳转地址，公网可以访问
         * @param $notify_url 异步通知地址，公网可以访问
         * @return $response 支付宝返回的信息
         */
        $response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);

        //输出表单
        var_dump($response);
    }

    //实现用户继续支付
    public function pay()
    {
        $order_id = intval(I('get.order_id'));
        $model = D('Order');
        $res = $model->where('id='.$order_id)->find();
        if(!$res){
            $this->error('参数错误');
        }
        if($res['pay_status']==1){
            $this->error('已经支付了该订单');
        }

        //实现具体的支付操作
        require_once '../pay/config.php';
        require_once '../pay/pagepay/service/AlipayTradeService.php';
        require_once '../pay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $res['id'];

        //订单名称，必填
        $subject = 'test';

        //付款金额，必填   实际项目中应该使用正确的金额
        $total_amount = 0.01;

        //商品描述，可空
        $body = 'test-desc';

        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setOutTradeNo($out_trade_no);

        $aop = new \AlipayTradeService($config);

        $response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);

        //输出表单
        var_dump($response);
    }

    public function returnUrl(){
        require_once("../pay/config.php");
        require_once '../pay/pagepay/service/AlipayTradeService.php';


        $arr=$_GET;
        $alipaySevice = new \AlipayTradeService($config);
        $result = $alipaySevice->check($arr);

        /* 实际验证过程建议商户添加以下校验。
        1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
        2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
        3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
        4、验证app_id是否为该商户本身。
        */
        if($result) {//验证成功
            //商户订单号
            $out_trade_no = htmlspecialchars($_GET['out_trade_no']);

            //支付宝交易号
            $trade_no = htmlspecialchars($_GET['trade_no']);
            //根据自己的业务逻辑进行代码修改
            //设置订单为已支付状态
            D('Order')->where('id='.$out_trade_no)->setField('pay_status',1);
        }
        else {
            //验证失败
            echo "验证失败";
        }
    }
}