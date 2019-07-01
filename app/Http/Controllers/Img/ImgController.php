<?php

namespace App\Http\Controllers\Img;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\ImgsModel;
class ImgController extends Controller
{
    //
    public function img(){
            $img=ImgsModel::all();
          // dd($img);
            $data=[
                'img'=>$img
            ];
            return view('img.img',$data);

    }
}
