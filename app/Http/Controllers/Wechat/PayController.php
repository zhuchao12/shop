<?php

namespace App\Http\Controllers\Wechat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Wechat\WXBizDataCryptController;
use App\Model\OrderModel;
use App\Model\WeixinPay;
use Illuminate\Support\Facades\Redis;
use QRcode;

class PayController extends Controller
{
    public $weixin_unifiedorder_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    public $weixin_notify_url = 'http://shop07.wjk1106.cn/wechat/pay/notice';     //支付通知回调

    public function test()
    {
        $total_fee = 1;         //用户要支付的总金额
        $order_id = OrderModel::generateOrderSN();

        $order_info = [
            'appid'         =>  env('WEIXIN_APPID_0'),      //微信支付绑定的服务号的APPID
            'mch_id'        =>  env('WEIXIN_MCH_ID'),       // 商户ID
            'nonce_str'     => str_random(16),             // 随机字符串
            'sign_type'     => 'MD5',
            'body'          => '测试订单-'.mt_rand(1111,9999) . str_random(6),
            'out_trade_no'  => $order_id,                       //本地订单号
            'total_fee'     => $total_fee,
            'spbill_create_ip'  => $_SERVER['REMOTE_ADDR'],     //客户端IP
            'notify_url'    => $this->weixin_notify_url,        //通知回调地址
            'trade_type'    => 'NATIVE'                         // 交易类型
        ];
        $order_data = $order_info;
        $order_data['pay_status']=1;

        WeixinPay::insertGetId($order_data);
        Redis::set('order_id',$order_id);

        $this->values = [];
        $this->values = $order_info;
        $this->SetSign();

        $xml = $this->ToXml();      //将数组转换为XML
        //var_dump($xml);
      //  exit;
        $rs = $this->postXmlCurl($xml, $this->weixin_unifiedorder_url, $useCert = false, $second = 30);

        $data =  simplexml_load_string($rs);
       // echo 'code_url: '.$data->code_url;echo '<br>';

        //将 code_url 返回给前端，前端生成 支付二维码

        include 'phpqrcode/phpqrcode.php';
        $url=$data->code_url;
        $file_name='qrcode/payimg.png';
        \QRcode::png($url,$file_name,'H','5','1');
        return view('wechat.pay',['file_name'=>$file_name]);

    }


    protected function ToXml()
    {
        if(!is_array($this->values)
            || count($this->values) <= 0)
        {
            die("数组数据异常！");
        }
        $xml = "<xml>";
        foreach ($this->values as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    private  function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            die("curl出错，错误码:$error");
        }
    }


    public function SetSign()
    {
        $sign = $this->MakeSign();
        $this->values['sign'] = $sign;
        return $sign;
    }


    private function MakeSign()
    {
        //签名步骤一：按字典序排序参数
        ksort($this->values);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".env('WEIXIN_MCH_KEY');
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     */
    protected function ToUrlParams()
    {
        $buff = "";
        foreach ($this->values as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }


    public function payselect(){
        // echo $_GET['order_id'];die;
        $order_id = Redis::get('order_id');
        $res = WeixinPay::where(['out_trade_no'=>$order_id])->first();

        $res = json_encode($res);
        $res = \GuzzleHttp\json_decode($res,true);
        if($res['pay_status']==2){
            Redis::del('order_id');
            return json_encode(
                ['status'=>1000,
                    'msg'=>'支付成功'
                ]
            );
        }else{
            return json_encode(
                ['status'=>1,
                    'msg'=>  '暂未支付'
                ]
            );
        }
    }

    public function paysuccess(){
        return view('pay.paysuccess');
    }
    /**
     * 微信支付回调
     */
    public function notice()
    {
        $order_id = Redis::get('order_id');

        $data = file_get_contents("php://input");

        //记录日志
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_pay_notice.log', $log_str, FILE_APPEND);

        $xml = simplexml_load_string($data);

        if ($xml->result_code == 'SUCCESS' && $xml->return_code == 'SUCCESS') {      //微信支付成功回调
            //验证签名
            $sign = true;

            if ($sign) {       //签名验证成功
                //TODO 逻辑处理  订单状态更新
                WeixinPay::where(['out_trade_no'=>$order_id])->update(['pay_status'=>2]);

            } else {
                //TODO 验签失败
                echo '验签失败，IP: ' . $_SERVER['REMOTE_ADDR'];
                // TODO 记录日志
            }

        }

        $response = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        echo $response;

    }



}
