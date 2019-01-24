<?php

namespace App\Http\Controllers\Upload;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
    public function upload()
    {
        return view('upload.upload');
    }
    public function uploads(Request $request){
        $res=$request->file('pdf');
            //var_dump($res);
        $ext  = $res->extension();
        if($ext != 'pdf'){
            die("请上传PDF格式");
        }
        $result = $res->storeAs(date('Ymd'),str_random(5) . '.pdf');
        if($result){
            echo '上传成功';
        }
    }
}