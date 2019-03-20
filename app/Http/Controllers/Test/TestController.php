<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use DB;

class TestController extends Controller
{
    //

    public function abc()
    {
        var_dump($_POST);
        echo '</br>';
        var_dump($_GET);
        echo '</br>';
    }

    public function world1()
    {
        echo __METHOD__;
    }


    public function hello2()
    {
        echo __METHOD__;
        header('Location:http://baidu.com');
    }

    public function world2()
    {
        echo __METHOD__;
    }

    public function md($m, $d)
    {
        echo 'm: ' . $m;
        echo '<br>';
        echo 'd: ' . $d;
        echo '<br>';

    }

    public function showName($name = null)
    {
        var_dump($name);
    }

    public function query1()
    {
        $list = DB::table('p_users')->get()->toArray();
        echo '<pre>';
        print_r($list);
        echo '</pre>';
    }

    public function query2()
    {
        $user = DB::table('p_users')->where('uid', 3)->first();
        echo '<pre>';
        print_r($user);
        echo '</pre>';
        echo '<hr>';
        $email = DB::table('p_users')->where('uid', 4)->value('email');
        var_dump($email);
        echo '<hr>';
        $info = DB::table('p_users')->pluck('age', 'name')->toArray();
        echo '<pre>';
        print_r($info);
        echo '</pre>';


    }

    public function test()
    {
        $data = [];
        return view('test.index', $data);
    }

    public function check()
    {
        echo __METHOD__;
    }

    public function ccc(Request $request)
    {
        //  echo '1';
        // exit;
        $tumestamp = $request->input('t');
        $key = 'password';                //通信双方提前约定
        $salt = 'qweqwe';
        $method = 'AES-128-CBC';
        $iv = substr(md5($tumestamp . $salt), 5, 16); //加密向量
        //接收加密数据
        $post_data = base64_decode($_POST['data']);   //decode base64
        //解密
        $dec_str = openssl_decrypt($post_data, $method, $key, OPENSSL_RAW_DATA, $iv);


        /*
        echo $dec_str;
        echo '<br>';
        echo '<pre>';
        print_r($_GET);
        echo '</pre>';
        echo '<pre>';
        print_r($_POST);
        echo '</pre>';
        echo '<hr>';
        */
        if (1) {
            $now = time();
            $response = [
                'erron' => 0,
                'msg' => 'ok',
                'data' => 'this is secret',
            ];

            $iv2 = substr(md5($now.$salt),5,16);            //加密向量
            $json_str = json_encode($response);
            $enc_data = openssl_encrypt($json_str,$method,$key,OPENSSL_RAW_DATA,$iv2);
            $post_data = base64_encode($enc_data);

        }
        return [
            't'=> $now,
            'data'=>$response,
        ];
    }

    public function ddd(Request $request){
        $data = $request->input('data');
        $now = $request->input('now');
        $sign = $request->input('sign');
        $pubkey = file_get_contents('./key/pub.pem');
        $res = openssl_get_publickey($pubkey);
        ($res) or die('您使用的公钥格式错误，请检查RSA私钥配置');
        $result = openssl_verify($data,base64_decode($sign),$res,OPENSSL_ALGO_SHA256);
        //   openssl_free_key($result);
        if($result==1){
            $key="password";
            $iv = substr(md5($now),1,16);
            $en = openssl_decrypt(base64_decode($data),'AES-128-CBC',$key,OPENSSL_RAW_DATA,$iv);

            return '成功';
        }

    }

    public function hb(){
        echo 111111;
    }
}
