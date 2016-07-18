<?php
/*
	获取google play 应用信息
	$param 	package				//应用包名
	$param 	OS 					//应用对应系统
	$param 	src					//前台访问 src=true;后他访问src=false

	访问地址：	hutui-img.img-cn-beijing.aliyuncs.com
	样式路径：	1e_1c_0o_0l_72h_72w_90q.src 	72w_72h	
				1e_1c_0o_0l_120h_120w_90q.src 	120w_120h
				0o_0l_280h_90q.src 				280h	
*/
define('DS',DIRECTORY_SEPARATOR);
require_once __DIR__.DS.'lib'.DS.'jvhe.oss.class.php';
require_once __DIR__.DS.'lib'.DS.'getStoreInfo.php';
error_reporting(0);

$data = $_GET;
$url = urldecode($data['package']);
// echo $url;exit;
$os = trimall($data['OS']);
$src = trimall($data['src']);
// $url = 'https://itunes.apple.com/cn/app/popstar!-xiao-mie-xing-xing/id834878585?mt=8';
// $url = 'https://play.google.com/store/apps/details?id=com.netmarble.mherosgb&hl=en';
$appinfos = run($url);
// var_dump($appinfos);
$storeType = $appinfos['os'];
$appinfo = json_decode($appinfos['data'],true);
//地址转存oss地址
if($storeType==1){
	$ret = appStore($appinfo,$src);
	echo json_encode($ret);
}else{
	$ret = googlePlay($appinfo,$src);
	echo json_encode($ret);
}

/*
	main 函数
	@data 	：Array 	应用包名，系统，访问形式
	@查询token 剩余次数：https://42matters.com/api/1/account.json?access_token=
*/
function run($url){
	if(is_bool(strpos($url,'http'))){
		return fasle;
	}
	if(!is_bool(strpos($url,'play.google'))){
	   	$ret['data'] = getGooglePlay($url);
	   	$ret['os'] = 3;
	   	return $ret;
	   	exit;
   	}
   	$ret['data'] = getAppStore($url);
	while(is_null($ret['data'])){
	    $ret = '';
	    $ret['data'] = getAppStore($url);
	}
	$ret['os'] = 1;
	return $ret;
}



/**获取googlePlay应用信息
*@param 	Array 		$retJson:从三方接口获取的应用信息
*@$src      bool  		是否前台调用，前台调用时不用返回OSS地址
*return 	:json
**/
function googlePlay($retJson='',$src=false){
	$fileName = 'Andriod/'.trimall($retJson['packageName']).'/'.date('Ymd').'-'.$retJson['packageName'];
	$icon = str_replace('//', 'http://', $retJson['icon']);
	$retJson['icon'] = getOssUrl($icon,$fileName.'icon');
	if($src){
		return $retJson;
	}
	$i = 0 ;
	foreach($retJson['screenshots'] as $screenUrl){
		$ret = getOssUrl($screenUrl,$fileName.'screenshots'.$i);
		if($i>4){
                        break;
                }
		if($ret){
			$retJson['screenshot'][$i] = $ret;
			$i++;
		}else{
			continue;
		}
	}
	$retJson['screenshots'] = $retJson['screenshot'];
	return $retJson;
}

/**生成二维码
*@$data 		String 	二维码地址
*@$upload_url	String 	上传文件地址
*@$errLevel 	int    	错误级别
*@$size 		Int 	图片大小
*
*return 		:bool
**/
function getQrimg($data = 'www.zplay.com',$upload_url,$errLevel = 'Q',$size = 10){
	include 'phpqrcode.class.php'; 
	$errorCorrectionLevel = $errLevel;
	$matrixPointSize = $size;
	QRcode::png($data, $upload_url, $errorCorrectionLevel, $matrixPointSize, 2);
	if(file_exists($upload_url)){
		return true;
	}
	return false;
}

function trimall($str)//删除空格
{
    $qian=array(" ","　","\t","\n","\r");
    $hou=array("","","","","");
    return str_replace($qian,$hou,$str);    
}

/**获取appStore应用信息
*@param 	Array 		$retJson:从三方接口获取的应用信息
*@$src      bool  		是否前台调用，前台调用时不用返回OSS地址
*return 	:json
**/
function appStore($retJson='',$src=false){
	$fileName = 'iOS/'.trimall($retJson['trackId']).'/'.date('Ymd').'-'.$retJson['trackId'];
	$retJson['artworkUrl100'] = getOssUrl($retJson['artworkUrl100'],$fileName.'artworkUrll00');	
	if($src){
		return $retJson;
	}
	$i = 0 ;
	foreach($retJson['screenshotUrls'] as $screenUrl){
		$ret = getOssUrl($screenUrl,$fileName.'screenshotUrls'.$i);
		if($i>4){
                        break;
                }
		if($ret){	
			$retJson['screenshotUrls'][$i] = $ret;
			$i++;
		}else{
			continue;
		}
	}
	// $retJson['screenshotUrls'] = $retJson['screenshotUrl'];
	return $retJson;
}



/** 获取图片OSS地址
* @param String  $screenUrl  图片当前地址 
* @param String  $fileName   存储文件名
*
* RETURN Json    ret['url']/ret['errmsg']	存储成返回图片地址/失败返回错误信息
*/ 
function getOssUrl($screenUrl='',$fileName=''){
	$logDir = __DIR__.'/logs/';
	$uploadOSS = new JVHE_OSS();
	$content = file_get_contents($screenUrl);
	$ret = $uploadOSS->uploadOSS($fileName,$content);
	$ret = json_decode($ret,true);
	if($ret['errno'] === 1001){
		error_log(date("[Y-m-d H:i:s]")." - :".$ret['errmsg']."\n", 3, $logDir."getAppInfoApi.log".date("Y-m-d"));
	}else{
		error_log(date("[Y-m-d H:i:s]")." - :".$ret['errmsg']."\n", 3, $logDir."getAppInfoApi.log".date("Y-m-d"));
		return null;
	}
	return $ret['url'];
}

/** curl 获取 https 请求 
* @param String $url        请求的url 
* @param Array  $data       要發送的數據 
* @param Array  $header     请求时发送的header 
* @param int    $timeout    超时时间，默认30s 
*/  
function curl_https($url, $data=array(), $header=array(), $timeout=30){  
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查  
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//从证书中检查SSL加密算法是否存在  
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $response = curl_exec($ch);
    if($error=curl_error($ch)){  
        die($error);  
    }  
    curl_close($ch);  
    return $response;   
}  