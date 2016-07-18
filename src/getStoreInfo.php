<?php
include __DIR__.DS.'src'.DS.'phpQuery-onefile.php'; //导入phpQuery库 
// $url = 'https://itunes.apple.com/cn/app/popstar!-xiao-mie-xing-xing/id834878585?mt=8';
//$url = 'https://itunes.apple.com/us/app/popstar!-xiao-mie-xing-xing/id834878585?mt=8';
//$url = 'https://play.google.com/store/apps/details?id=com.netmarble.mherosgb&hl=en';
//$url = 'https://play.google.com/store/apps/details?id=com.netmarble.mherosgb&hl=zh-cn';


// $ret = getAppStore($url);
// while(is_null($ret)){
//     $ret = '';
//     $ret = getAppStore($url);
// }
/*
  main 函数
  @data   ：Array  应用包名，系统，访问形式
  @查询token 剩余次数：https://42matters.com/api/1/account.json?access_token=
*/
function run($url){
  if(is_bool(strpos($url,'http'))){
    return fasle;
  }
  if(!is_bool(strpos($url,'play.google'))){
      $ret['data'] = getGooglePlay($url);
      return $ret;
      exit;
    }
    $ret['data'] = getAppStore($url);
  while(is_null($ret['data'])){
      $ret = '';
      $ret['data'] = getAppStore($url);
  }
  return $ret;
}



function getAppStore($url){
    phpQuery::newDocumentFile($url);  //实例化 
    $ret['artworkUrl100'] = pq("#left-stack .artwork:eq(0) img")->attr('src-swap-high-dpi'); 
    $ret['trackCensoredName'] = pq(".padder h1")->text();
    // $ret['artistName'] = pq(".padder h2:eq(0)")->text();
    $ret['artistName'] = pq("#left-stack .list li:eq(6) span:eq(1)")->text();
    $ret['description'] = trim(pq(".padder .center-stack p:eq(0)")->text());
    for($i=0;$i<=5;$i++){
        $data = pq(".toggle:eq(0) .iphone-screen-shots .lockup img:eq($i)")->attr('src');
        if(empty($data)){
            break;
        }
        $ret['screenshotUrls'][] = $data;
    }
   $ret['trackId'] = parse($url) ;
   $ret['os'] = 'iOS';
    $json = json_encode($ret,JSON_UNESCAPED_UNICODE);
    $data = mb_convert_encoding($json,'ISO-8859-1','utf-8');
    $encode = mb_detect_encoding($data, array("ASCII",'UTF-8','GB2312',"GBK",'BIG5'));
    if(!is_null($data)&&$encode == 'UTF-8' && is_json($data) && !strpos($data,'????')){
        return $data;
    }
    getAppStore($url);
}


function getGooglePlay($url){
    phpQuery::newDocumentFile($url);  //实例化 
    $ret['packageName'] = parse($url) ;
    // echo $ret['packageName'];exit;
    $ret['title'] = pq(".id-app-title")->text(); 
    $ret['developer'] = pq(".document-subtitle span:eq(0)")->text();
    $ret['description'] = trim(pq(".show-more-content:eq(0)")->text());
    $ret['icon'] = pq(".cover-image")->attr('src');
    for($i=0;$i<=5;$i++){
        $data = pq(".thumbnails-wrapper .thumbnails img:eq($i)")->attr('src');
        if(empty($data)){
            break;
        }
        $ret['screenshots'][] = $data;
    }
    $ret['os'] = 'Android';
//    $ret['trackId'] = $packageName;
//    $ret['bundleId'] = $packageName;
    $json = json_encode($ret,JSON_UNESCAPED_UNICODE);
    return $json;
}

function is_json($string) {
    if(empty($string)){
        return false;
    }
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

/*
* 将下载地址解析成包名
* @$download   String    下载地址
*/
    function parse($download){
       if(!is_bool(strpos($download,'http'))){
           if(!is_bool(strpos($download,'play.google.com'))){
               preg_match('/id=.+\&?/',$download,$packages);
               // var_dump($packages);
               $package = str_replace('id=', '', $packages[0]);
               $package = str_replace('&', '', $package);
               if(empty($packages)){
                   return null;
               }
           }else{
               preg_match('/\d{8,12}/',$download,$packages);
               if(empty($packages)){
                   return null;
               }
               $package = $packages[0];
           }
           return $package;
       }
       return null;
    }













/*
 * 调试专用
 * @ZB ：2016-07-15
 */


//$html = file_get_contents($url);
////phpquery::newDocumentHTML($html,'utf-8');
//phpQuery::newDocumentFile($url);  //实例化 

//phpQuery::newDocumentFile($url);  //实例化 
//echo pq("title")->text();   //获取站点标题
//echo "<br/>++++++++++++++++++++++++<br/>";
//echo pq(".id-app-title")->text();   //获取游戏名称
//echo "<br/>++++++++++++++++++++++++<br/>";
//echo pq(".document-subtitle span:eq(0)")->text();   //获取游戏开发商
//echo "<br/>++++++++++++++++++++++++<br/>";
//echo pq(".show-more-content:eq(0)")->html();   //获取游戏描述
//echo "<br/>++++++++++++++++++++++++<br/>";
//echo pq(".cover-image")->attr('src');   //获取游戏icon
////echo "<br/>++++++++++++++++++++++++<br/>";
//for($i=0;$i<=5;$i++){
//    $data = pq(".thumbnails-wrapper .thumbnails img:eq($i)")->attr('src');
//    if(empty($data)){
//        break;
//    }
//    $screenshots['screenshots'][] = $data;
//}
//var_dump($screenshots);
//exit;