<?php

namespace App\Http\Controllers\Login;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

use App\Model\LoginModel;
use Illuminate\Support\Facades\Redis;

class LoginController extends Controller
{
    public function klogin(){
        return view('login.login');
    }

    public function kloginadd(Request $request ){
        $reg = request()->all();
        //var_dump($reg);
        $pass =  $request->input('pwd');
        $where = [
            'name'=>$reg['name'],
        ];
        $result = LoginModel::where($where)->first();
        //dump($result);
        // exit;
        if($result){
            if(password_verify($pass,$result->pass)){
                $token = substr(md5(time().mt_rand(1,99999)),10,10);
                setcookie('uid',$result->uid,time()+86400,'/','shop_laravel',false,true);
                setcookie('token',$token,time()+86400,'/klogin','',false,true);
                $request->session()->put('u_token',$token);
                $request->session()->put('uid',$result->uid);
                $value1=Cache::add('name', '$name', 1);
                var_dump($value1);
           //     header("Refresh:3;url=/conten");
                echo '登陆成功';
            }else{
                echo '登录失败';
            }
        }

    }
        public function pwd(){
                $info = [
                    'title'=>'修改密码',
                ];
                return view('login.pwd');
        }

        public function pwd2(Request $request){
            $name = $request->input('name');
            $passwrod = $request->input('password');
            $pas = password_hash($passwrod,PASSWORD_BCRYPT);
            $res = LoginModel::where(['name'=>$name])->update(['password'=>$pas]);
           // var_dump($res);exit;
            if($res){
                echo '修改成功';
            }else{
                echo '修改失败';
            }
        }

}