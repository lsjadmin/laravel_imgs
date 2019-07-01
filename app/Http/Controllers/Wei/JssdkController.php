<?php

namespace App\Http\Controllers\Wei;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use App\Model\MessageModel;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
class JssdkController extends Controller
{
    //
    public function Jssdktest(){
        //生成签名
        $nonceStr=Str::random(10);
        $ticket=ticket();  
        //dd($ticket);   
        $timestamp=time();
        $current_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'];
        //echo($current_url);
        $string1 = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$current_url";
        $sign= sha1($string1);
        $jsconfig=[
            'appId'=>env('WX_APPID'),   //公众号的唯一标识
            'timestamp'=>$timestamp,   //生成签名的时间戳
            'nonceStr'=> $nonceStr,     //生成签名的随机串
            'signature'=> $sign,   //签名
        ];
        $data=[
            'jsconfig'=>$jsconfig
        ];
        //dd($data);
          return view('wei.Jssdktest',$data);   
    }
    public function getimg(){
        //echo'<pre>';print_r($_GET);echo'</pre>';
        // $b=$_GET;
        // $a=json_encode($b);
        // $MediaId=rtrim($a,',');
        $MediaId="987654321123456789";
        $token=accessToken();
        $urla="https://api.weixin.qq.com/cgi-bin/media/get?access_token=$token&media_id=$MediaId";
        var_dump($urla);die;
        $voice_str=file_get_contents($urla);
        $file_name=time().mt_rand(11111,99999).'.png';
        file_put_contents("/wwwroot/1809_weixin_shop/public/wx_image/$file_name",$voice_str,FILE_APPEND);
    }
    //网页授权
    public function scope(){
        // echo'<pre>';print_r($_GET);echo'</pre>';die;
            $code=$_GET['code'];
            //通过code换取网页授权access_token
            $url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.env('WX_APPID').'&secret='.env('WX_APPSECRET').'&code='.$code.'&grant_type=authorization_code';
            $response=json_decode(file_get_contents($url),true);
            //echo'<pre>';print_r($response);echo'</pre>'; 

            $access_token=$response['access_token'];
            $openid=$response['openid'];
            //拉取用户信息
            $urla='https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
            $res=json_decode(file_get_contents($urla),true);
            //echo'<pre>';print_r($res);echo'</pre>';die;
                    $info=[
                        'openid'=>$res['openid'],
                        'nickname'=>$res['nickname'],
                        'city'=>$res['city'],
                        'province'=>$res['province'],
                        'country'=>$res['country'],
                    ];
            $arr=MessageModel::insert($info);
            if($arr){
                echo "授权成功";
            }
    }
     //标签添加视图
     public function tallyAdd(){
         return view('wei.tallyAdd');
     }  
     //标签接口
     public function tally(){
        // echo $name;
        $name=request()->input('name');
        //echo $name;die;
       // $name="北京";
        $access_token=accessToken();
        $url='https://api.weixin.qq.com/cgi-bin/tags/create?access_token='.$access_token;
        $a=[
            "tag" =>["name"=>$name ]
        ];
        $data=json_encode($a,JSON_UNESCAPED_UNICODE);
        //echo $data;die;
        $client=new Client();
        $response=$client->request('post',$url,[
            'body'=>$data
        ]);
       $res=$response->getBody();
       $arr=json_decode($res,true);
      // echo'<pre>';print_r($arr);echo'</pre>';
     } 
     //标签展示
     public function tallyList(){
        $access_token=accessToken();
        $url='https://api.weixin.qq.com/cgi-bin/tags/get?access_token='.$access_token;
        $response=json_decode(file_get_contents($url),true);
         echo'<pre>';print_r($response);echo'</pre>';die;
         $arr=$response['tags'];
        //echo '<pre>';print_r($arr);echo'</pre>';
        return view('wei.tallylist',['arr'=>$arr]);
     }    
    //把用户 标签 在视图展示出来(视图)
     public function mass(){
        $userInfo=DB::table('userwx')->get()->toArray();
        //dd($userInfo);
        $access_token=accessToken();
        $url='https://api.weixin.qq.com/cgi-bin/tags/get?access_token='.$access_token;
        $response=json_decode(file_get_contents($url),true);
         //echo'<pre>';print_r($response);echo'</pre>';die;
         $arr=$response['tags'];
        // echo'<pre>';print_r($arr);echo'</pre>';die;
         return view('wei.mass',['userInfo'=>$userInfo,'arr'=>$arr]);
    }
    //给用户添加标签（执行  接口）
    public function make(){
        $b=request()->input('openid');
        $openid=explode(',',$b);
        //echo $openid;
        //dd($openid);
        $select=request()->input('select');
       // echo $select;
       $access_token=accessToken();
       $url='https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token='.$access_token;
       $data=[
           
           "openid_list" =>[//粉丝列表    
               $openid,    
               ],   
               "tagid" =>$select
         
       ];
       $a=json_encode($data,JSON_UNESCAPED_UNICODE);
       // echo $a;
       $client=new Client();
       $response=$client->request('post',$url,[
           'body'=>$a
       ]);
       $arr=$response->getBody();
       $res=json_decode($arr,true);
       dd($res);
    }
    //标签群发接口
    public function info(){
        $tag_id=request()->input('tag_id');
       
        $goods=DB::table('goods')->where(['g_id'=>2])->first();
       // echo '<pre>';print_r($goods);echo '</pre>';
         $key="text";
        $text=$goods->goods_name;
        Redis::set($key,$text);
        Redis::expire($key,3600);

        $text=request()->input('text');
        if($text==''){
            $text=Redis::get($key);
             //echo $text;die;
        }else{
            $text=request()->input('text');
        }
        $access_token=accessToken();
        $url='https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token='.$access_token;
        $a=[
            "filter"=>[
                "is_to_all"=>false,
                "tag_id"=>$tag_id
             ],
             "text"=>[
                "content"=>$text
             ],
              "msgtype"=>"text"
        ];
        $data=json_encode($a,JSON_UNESCAPED_UNICODE);
        //echo $data;die;
        $client=new Client();
        $response=$client->request('post',$url,[
            'body'=>$data
        ]);
       $res=$response->getBody();
       $arr=json_decode($res,true);
       echo'<pre>';print_r($arr);echo'</pre>';
    }
  //
 
  
}
