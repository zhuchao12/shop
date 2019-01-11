<?php
namespace App\Http\Controllers\alipay;
use App\Http\Controllers\Controller;
use GuzzleHttp\client;
class alipayController extends Controller
{
    public function alipay(){
        $url='http://vm.order.lening.com';
        $client = new Client([
            'base_uri'=>$url,
            'timeout'=>2.0,
        ]);
        $response=$client->request('GET','/order.php');
        echo $response->getBody();
    }
}