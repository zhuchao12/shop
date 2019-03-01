<?php
namespace App\Http\Controllers\Wechat;

use App\Model\MaterialUser;
use App\Model\MediaUser;
use App\Model\WechatUser;
use App\Model\WechatChatModel;
use App\Model\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;


class WechatController extends Controller
{
    //

    protected $redis_weixin_access_token = 'str:weixin_access_token';     //微信 access_token
    protected $redis_weixin_jsapi_ticket = 'str:weixin_jsapi_ticket';
    public function test()
    {
        //echo __METHOD__;
        //$this->getWXAccessToken();
        $this->getUserInfo(1);
    }

    /**
     * 首次接入
     */
    public function validToken1()
    {
        //$get = json_encode($_GET);
        //$str = '>>>>>' . date('Y-m-d H:i:s') .' '. $get . "<<<<<\n";
        //file_put_contents('logs/weixin.log',$str,FILE_APPEND);
        echo $_GET['echostr'];
    }

    /**
     * 接收微信服务器事件推送
     */
    public function wxEvent()
    {
        $data = file_get_contents("php://input");


        //解析XML
        $xml = simplexml_load_string($data);        //将 xml字符串 转换成对象

        $event = $xml->Event;                       //事件类型
        //var_dump($xml);echo '<hr>';
        $openid = $xml->FromUserName;               //用户openid

        // 处理用户发送消息
        if (isset($xml->MsgType)) {
            if ($xml->MsgType == 'text') {            //用户发送文本消息
                $msg = $xml->Content;

                //记录聊天消息

                $data = [
                    'msg'       => $xml->Content,
                    'msgid'     => $xml->MsgId,
                    'openid'    => $openid,
                    'msg_type'  => 1,    // 1用户发送消息 2客服发送消息
                    'add_time' =>time(),
                ];

                $id = WechatChatModel::insertGetId($data);
                var_dump($id);
                //$xml_response = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. $msg. date('Y-m-d H:i:s') .']]></Content></xml>';


                //echo $xml_response;



                /*$xml_response =
                    '<xml>
                        <ToUserName><![CDATA[' . $openid . ']]></ToUserName>
                        <FromUserName><![CDATA[' . $xml->ToUserName . ']]></FromUserName>
                        <CreateTime>' . time() . '</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[' . $msg . date('Y-m-d H:i:s') . ']]></Content>
                     </xml>';
                echo $xml_response;*/
            } elseif ($xml->MsgType == 'image') {       //用户发送图片信息
                //视业务需求是否需要下载保存图片
                if (1) {  //下载图片素材
                    $file_name=$this->dlWxImg($xml->MediaId);
                    $xml_response =
                        '<xml>
                            <ToUserName><![CDATA[' . $openid . ']]></ToUserName>
                            <FromUserName><![CDATA[' . $xml->ToUserName . ']]></FromUserName>
                            <CreateTime>' . time() . '</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[' . str_random(10) . ' >>> ' . date('Y-m-d H:i:s') . ']]></Content>
                         </xml>';
                    echo $xml_response;
                    $data = [
                        'openid' => $openid,
                        'add_time' => time(),
                        'msg_type' => 'image',
                        'media_id' => $xml->MediaId,
                        'format' => $xml->Format,
                        'msg_id' => $xml->MsgId,
                        'local_file_name' => $file_name
                    ];
                    $m_id = MediaUser::insertGetId($data);
                    var_dump($m_id);

                }
            }elseif($xml->MsgType=='voice'){        //处理语音信息
                $this->dlVoice($xml->MediaId);
                $msg = $xml->Content;
                $xml_response =
                    '<xml>
                        <ToUserName><![CDATA[' . $openid . ']]></ToUserName>
                        <FromUserName><![CDATA[' . $xml->ToUserName . ']]></FromUserName>
                        <CreateTime>' . time() . '</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[' . $msg . date('Y-m-d H:i:s') . ']]></Content>
                     </xml>';
                echo $xml_response;
            }


            if ($event == 'subscribe') {
                $sub_time = $xml->CreateTime;               //扫码关注时间


                echo 'openid: ' . $openid;
                echo '</br>';
                echo '$sub_time: ' . $sub_time;

                //获取用户信息
                $user_info = $this->getUserInfo($openid);
                echo '<pre>';
                print_r($user_info);
                echo '</pre>';

                //保存用户信息
                $u = WechatUser::where(['openid' => $openid])->first();
                //var_dump($u);die;
                if ($u) {       //用户不存在
                    echo '用户已存在';
                } else {
                    $user_data = [
                        'openid' => $openid,
                        'add_time' => time(),
                        'nickname' => $user_info['nickname'],
                        'sex' => $user_info['sex'],
                        'headimgurl' => $user_info['headimgurl'],
                        'subscribe_time' => $sub_time,
                    ];

                    $id = WechatUser::insertGetId($user_data);      //保存用户信息
                    var_dump($id);
                }
            } elseif ($event == 'CLICK') {               //click 菜单
                if ($xml->EventKey == 'kefu01') {
                    $this->kefu01($openid, $xml->ToUserName);
                }
            }

            /*
            $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
            file_put_contents('logs/wx_event.log', $log_str, FILE_APPEND);
            */
        }
    }

    /**
     * 下载图片素材
     * @param $media_id
     */
    public function dlWxImg($media_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->getWXAccessToken().'&media_id='.$media_id;
        //echo $url;echo '</br>';

        //保存图片
        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //$h = $response->getHeaders();

        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0],'"'),-20);

        $wx_image_path = 'wx/images/'.$file_name;
        //保存图片
        $r = Storage::disk('local')->put($wx_image_path,$response->getBody());
        if($r){     //保存成功
            echo '保存成功';
        }else{      //保存失败
            echo '保存失败';
        }
        return $file_name;
    }

    public function all()
    {
        $access_token = $this->getWXAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token='.$access_token;
        //var_dump($url);exit;
        $client = new GuzzleHttp\Client(['base_url' => $url]);
        $param = [
            "filter"=>[
                "is_to_all"=>true
            ],
            "text"=>[
                "content"=>"哈喽你大爷."
            ],
            "msgtype"=>"text"
        ];
        ///var_dump($param);exit;
        $r = $client->Request('POST', $url, [
            'body' => json_encode($param, JSON_UNESCAPED_UNICODE)
        ]);
        //var_dump($r);exit;
        $response_arr = json_decode($r->getBody(), true);
        //echo '<pre>';
        //print_r($response_arr);
        // echo '</pre>';

        if ($response_arr['errcode'] == 0) {
            echo "发送成功";
        } else {
            echo "发送失败";
            echo '</br>';
            echo $response_arr['errmsg'];

        }

    }


    /**
     * 下载语音文件
     * @param $media_id
     */
    public function dlVoice($media_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->getWXAccessToken().'&media_id='.$media_id;

        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //$h = $response->getHeaders();
        //echo '<pre>';print_r($h);echo '</pre>';die;
        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0],'"'),-20);

        $wx_image_path = 'wx/voice/'.$file_name;
        //保存图片
        $r = Storage::disk('local')->put($wx_image_path,$response->getBody());
        if($r){     //保存成功
            echo '保存成功';
        }else{      //保存失败
            echo '保存失败';
        }
    }

    /**
     * 客服处理
     * @param $openid   用户openid
     * @param $from     开发者公众号id 非 APPID
     */
    public function kefu01($openid,$from)
    {
        // 文本消息
        $xml_response =
            '<xml>
                <ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$from.']]></FromUserName>
                <CreateTime>'.time().'</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA['. 'Hello World, 现在时间'. date('Y-m-d H:i:s') .']]></Content>
            </xml>';
        echo $xml_response;
    }


    /**
     * 接收事件推送
     */
    public function validToken()
    {
        //$get = json_encode($_GET);
        //$str = '>>>>>' . date('Y-m-d H:i:s') .' '. $get . "<<<<<\n";
        //file_put_contents('logs/weixin.log',$str,FILE_APPEND);
        //echo $_GET['echostr'];
        $data = file_get_contents("php://input");
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_event.log',$log_str,FILE_APPEND);
    }

    /**
     * 获取微信AccessToken
     */
    public function getWXAccessToken()
    {

        //获取缓存
        $token = Redis::get($this->redis_weixin_access_token);
        if(!$token){        // 无缓存 请求微信接口
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_APPID').'&secret='.env('WEIXIN_APPSECRET');
            $data = json_decode(file_get_contents($url),true);

            //记录缓存
            $token = $data['access_token'];
            Redis::set($this->redis_weixin_access_token,$token);
            Redis::setTimeout($this->redis_weixin_access_token,3600);
        }
        return $token;

    }

    /**
     * 获取用户信息
     * @param $openid
     */
    public function getUserInfo($openid)
    {
        //$openid = 'oLreB1jAnJFzV_8AGWUZlfuaoQto';
        $access_token = $this->getWXAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';

        $data = json_decode(file_get_contents($url),true);
        //echo '<pre>';print_r($data);echo '</pre>';
        return $data;
    }
    /**
     * 创建服务号菜单
     */
    public function createMenu(){
        //echo __METHOD__;
        // 1 获取access_token 拼接请求接口
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getWXAccessToken();
        //echo $url;echo '</br>';

        //2 请求微信接口
        $client = new GuzzleHttp\Client(['base_uri' => $url]);

        $data= [
            "button"=>[
                [
                    "name"=>"一级菜单",
                    "sub_button"=>[
                        [
                            "type"=>"view",
                            "name"=>"网易云",
                            "url"=>"https://music.163.com/"
                        ],
                        [
                            "type"=>"miniprogram",
                            "name"=>"微信抽奖",
                            "url"=>"http://mp.weixin.qq.com",
                            "appid"=>"wxe072a1fff4e9a930",
                            "pagepath"=>"pages/lunar/index"
                        ],
                    ]
                ],
                [
                    "name"=>"百度一下",
                    "sub_button"=>[
                        [
                            "type"=>"view",
                            "name"=>"进入百度",
                            "url"=>"https://baidu.com/"
                        ],
                        [
                            "type"=>"miniprogram",
                            "name"=>"微信扫码",
                            "url"=>"http://mp.weixin.qq.com",
                            "appid"=>"wxe072a1fff4e9a930",
                            "pagepath"=>"pages/lunar/index"
                        ],
                        [
                        "type"=>"click",
                        "name"=>"赞一下我们",
                        "key"=>"kefu01"
                    ]
                    ]
                ]
            ]
        ];


        $r = $client->request('POST', $url, [
            'body' => json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);

        // 3 解析微信接口返回信息

        $response_arr = json_decode($r->getBody(),true);
        //echo '<pre>';print_r($response_arr);echo '</pre>';

        if($response_arr['errcode'] == 0){
            echo "菜单创建成功";
        }else{
            echo "菜单创建失败，请重试";echo '</br>';
            echo $response_arr['errmsg'];

        }
    }



    public function materialTest()
    {
        //echo __METHOD__;echo '</br>';
        echo '<pre>';print_r($_POST);echo '</pre>';echo '</br>';
        echo '<pre>';print_r($_FILES);echo '</pre>';
    }

    /**
     * 上传素材
     */
    public function upMaterial()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$this->getWXAccessToken().'&type=image';
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST',$url,[
            'multipart' => [
                [
                    'name'     => 'username',
                    'contents' => 'zhangsan'
                ],
                [
                    'name'     => 'media',
                    'contents' => fopen('abc.jpg', 'r')
                ],
            ]
        ]);

        $body = $response->getBody();
        echo $body;echo '<hr>';
        $d = json_decode($body,true);
        echo '<pre>';print_r($d);echo '</pre>';


    }



    public function upMaterialTest($file_path)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$this->getWXAccessToken().'&type=image';
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST',$url,[
            'multipart' => [
                [
                    'name'     => 'media',
                    'contents' => fopen($file_path, 'r')
                ],
            ]
        ]);

        $body = $response->getBody();
        echo $body;echo '<hr>';
        $d = json_decode($body,true);
        echo '<pre>';print_r($d);echo '</pre>';


    }


    /**
     * 获取永久素材列表
     */
    public function materialList()
    {
        $client = new GuzzleHttp\Client();
        $type = $_GET['type'];
        $offset = $_GET['offset'];

        $url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$this->getWXAccessToken();

        $body = [
            "type"      => $type,
            "offset"    => $offset,
            "count"     => 20
        ];
        $response = $client->request('POST', $url, [
            'body' => json_encode($body)
        ]);

        $body = $response->getBody();
        echo $body;echo '<hr>';
        $arr = json_decode($response->getBody(),true);
        echo '<pre>';print_r($arr);echo '</pre>';


    }

    public function formShow()
    {

        return view('wechat.wechat');



    }

    public function formTest(Request $request)
    {
        //echo '<pre>';print_r($_POST);echo '</pre>';echo '<hr>';
        //echo '<pre>';print_r($_FILES);echo '</pre>';echo '<hr>';

        //保存文件
        $img_file = $request->file('media');
        //echo '<pre>';print_r($img_file);echo '</pre>';echo '<hr>';

        $img_origin_name = $img_file->getClientOriginalName();
        echo 'originName: '.$img_origin_name;echo '</br>';
        $file_ext = $img_file->getClientOriginalExtension();          //获取文件扩展名
        echo 'ext: '.$file_ext;echo '</br>';

        //重命名
        $new_file_name = str_random(15). '.'.$file_ext;
        echo 'new_file_name: '.$new_file_name;echo '</br>';

        //文件保存路径


        //保存文件
        $save_file_path = $request->media->storeAs('form_test',$new_file_name);       //返回保存成功之后的文件路径

        echo 'save_file_path: '.$save_file_path;echo '<hr>';

        $data=[
            'url'=>$save_file_path,
            'add_time'=>time()
        ];

        $r=MaterialUser::insertGetId($data);
        //上传至微信永久素材
        $this->upMaterialTest($save_file_path);
    }


    /**
     * 微信客服聊天
     */
    public function chatView($id)
    {

        $userInfo = WechatUser::where(['id'=>$id])->first();
        $data = [
            'openid'    => $userInfo->openid
        ];
        return view('wechat.chat',$data);
    }


    public function getChatMsg()
    {
        $openid = $_GET['openid'];  //用户openid
        $pos = $_GET['pos'];        //上次聊天位置
        $msg = WechatChatModel::where(['openid'=>$openid])->where('id','>',$pos)->first();
        if($msg){
            $response = [
                'errno' => 0,
                'data'  => $msg->toArray()
            ];

        }else{
            $response = [
                'errno' => 50001,
                'msg'   => '服务器异常，请联系管理员'
            ];
        }

        die( json_encode($response));

    }

    public function getChatMsgs()
    {

        $send_msg = $_GET['send_msg'];  //用户openid
        $openid = $_GET['openid'];        //上次聊天位置

        //获取access_token
        $access_token=$this->getWXAccessToken();
        $url='https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
        //请求微信接口
        $client = new GuzzleHttp\Client(['base_uri' => $url]);
        $data=[
            "touser"=>$openid,
            'msgtype'=>'text',
            'text'=>[
                "content"=>$send_msg
            ]
        ];
        $res=$client->request('POST', $url, ['body' => json_encode($data,JSON_UNESCAPED_UNICODE)]);
        $res_arr=json_decode($res->getBody(),true);
        if($res_arr['errcode']==0&&$res_arr['errmsg']=='ok'){
            //将聊天记录保存到数据库
            $data=[
                'openid'=>$openid,
                'msg'=>$send_msg,
                'msgid'=>'空',
                'add_time'=>time(),
                'msg_type'=>2
            ];
            $res=WechatChatModel::insertGetId($data);
            var_dump($res);
        }



    }

    public  function  login()
    {
        $myurl="http://mall.77sc.com.cn/weixin.php?r1=http://shop07.wjk1106.cn/wechat/getcode";
        $data=[
        "url"=>'https://open.weixin.qq.com/connect/qrconnect?appid=wxe24f70961302b5a5&redirect_uri='.urlencode($myurl).'&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect'
        ];
        return view('wechat.login',$data);
    }

    public function getCode(){
        $code = $_GET['code'];          // code
        //2 用code换取access_token 请求接口
        $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxe24f70961302b5a5&secret=0f121743ff20a3a454e4a12aeecef4be&code='.$code.'&grant_type=authorization_code';
        $token_json = file_get_contents($token_url);
        $token_arr = json_decode($token_json,true);
        echo '<hr>';
        echo '<pre>';print_r($token_arr);echo '</pre>';

        $access_token = $token_arr['access_token'];
        $openid = $token_arr['openid'];

        // 3 携带token  获取用户信息
        $user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $user_json = file_get_contents($user_info_url);

        $user_arr = json_decode($user_json,true);
      //  echo '<hr>';
       // echo '<pre>';print_r($user_arr);echo '</pre>';

        //查询数据库中该用户是否存在

        $unionid = $user_arr['unionid'];
        $where = [
            'union_id'=>$unionid
        ];
        $wx_user_info = WechatUser::where($where)->first();
        if($wx_user_info){
            $user_info = UserModel::where(['wechat_id'=>$wx_user_info->id])->first();
        }
        if(empty($wx_user_info) || empty($user_info)){

            //第一次登录

            $data = [
                'openid'        =>  $user_arr['openid'],
                'nickname'      =>  $user_arr['nickname'],
                'sex'           =>  $user_arr['sex'],
                'headimgurl'    =>  $user_arr['headimgurl'],
                'union_id'      =>  $unionid,
                'add_time'      =>  time()

            ];
            $wechat_id = WechatUser::insertGetId($data);
            $rs = UserModel::insertGetId(['wechat_id'=>$wechat_id]);
            if($rs){
                echo '注册成功';
                exit;
            }
        }
        echo '登录成功';

    }

    /**
     * 微信jssdk 调试
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function jssdk()
    {

        //计算签名

        $jsconfig = [
            'appid' => env('WEIXIN_APPID'),        //APPID
            'timestamp' => time(),
            'noncestr'    => str_random(10),
            //'sign'      => $this->wxJsConfigSign()
        ];

        $sign = $this->wxJsConfigSign($jsconfig);
        $jsconfig['sign'] = $sign;
        $data = [
            'jsconfig'  => $jsconfig
        ];
        return view('wechat.jssdk',$data);
    }
    /**
     * 计算JSSDK sign
     */
    public function wxJsConfigSign($param)
    {
        $current_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];     //当前调用 jsapi的 url
        $ticket = $this->getJsapiTicket();
        $str =  'jsapi_ticket='.$ticket.'&noncestr='.$param['noncestr']. '&timestamp='. $param['timestamp']. '&url='.$current_url;
        $signature=sha1($str);
        return $signature;
    }


    /**
     * 获取jsapi_ticket
     * @return mixed
     */

    public function getJsapiTicket()
    {
        //是否有缓存
        $ticket = Redis::get($this->redis_weixin_jsapi_ticket);
        if(!$ticket){           // 无缓存 请求接口
            //$access_token = $this->getWXAccessToken();
            $access_token = $this->getWXAccessToken();

            $ticket_url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
            $ticket_info = file_get_contents($ticket_url);
            $ticket_arr = json_decode($ticket_info,true);

            if(isset($ticket_arr['ticket'])){
                $ticket = $ticket_arr['ticket'];
                Redis::set($this->redis_weixin_jsapi_ticket,$ticket);
                Redis::setTimeout($this->redis_weixin_jsapi_ticket,3600);       //设置过期时间 3600s
            }
        }
        return $ticket;

    }

}
