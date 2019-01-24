<?php

namespace App\Http\Controllers\Movie;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class MovieController extends Controller
{
    public function movie(){
        $key = 'test_bit';
        $seat_status = [];
        for($i=0;$i<=30;$i++){
            $status =Redis::getBit($key,$i);
            $seat_status[$i] = $status;
        }

        $data = [
            'seat'=>$seat_status
        ];

        return view('movie.movie',$data);
    }

    public function buy($pos){
        $key = 'test_bit';
        Redis::setbit($key,$pos,1);
        header('Refresh:2;url=/movie');
        echo '抢座成功';

    }

}