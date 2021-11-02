<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-10
 * Time: PM7:40
 */


function getImageUrlByPath($path, $size) {
    $thumb = getThumbImage($path, $size);
    $thumb = $thumb['src'];
    $thumb = substr($thumb,1);
    return getRootUrl().$thumb;
}

/**
 *  获取缩略图
 *  @param  unknown_type  $filename  原图路径、url
 *  @param  unknown_type  $width  宽度
 *  @param  unknown_type  $height  高
 *  @param  unknown_type  $cut  是否切割  默认不切割
 *  @return  string
 */
function getThumbImage($filename, $width=100, $height='auto', $cut=false, $replace=false) {
    define('UPLOAD_URL',  '');
    define('UPLOAD_PATH',  '');
    $filename  =  str_ireplace(UPLOAD_URL,  '',  $filename);  //将URL转化为本地地址
    $info  =  pathinfo($filename);
    $oldFile  =  $info['dirname']  .  DIRECTORY_SEPARATOR  .  $info['filename']  .  '.'  .  $info['extension'];
    $thumbFile  =  $info['dirname']  .  DIRECTORY_SEPARATOR  .  $info['filename']  .  '_'  .  $width  .  '_'  .  $height  .  '.'  .  $info['extension'];

    $oldFile  =  str_replace('\\',  '/',  $oldFile);
    $thumbFile  =  str_replace('\\',  '/',  $thumbFile);


    $filename  =  ltrim($filename,  '/');
    $oldFile  =  ltrim($oldFile,  '/');
    $thumbFile  =  ltrim($thumbFile,  '/');
    //原图不存在直接返回
    if  (!file_exists(UPLOAD_PATH  .  $oldFile))  {
        @unlink(UPLOAD_PATH  .  $thumbFile);
        $info['src']  =  $oldFile;
        $info['width']  =  intval($width);
        $info['height']  =  intval($height);
        return  $info;
        //缩图已存在并且  replace替换为false
    }  elseif  (file_exists(UPLOAD_PATH  .  $thumbFile)  &&  !$replace)  {
        $imageinfo  =  getimagesize(UPLOAD_PATH  .  $thumbFile);
        //dump($imageinfo);exit;
        $info['src']  =  $thumbFile;
        $info['width']  =  intval($imageinfo[0]);
        $info['height']  =  intval($imageinfo[1]);
        return  $info;
        //执行缩图操作
    }  else  {
        $oldimageinfo  =  getimagesize(UPLOAD_PATH  .  $oldFile);
        $old_image_width  =  intval($oldimageinfo[0]);
        $old_image_height  =  intval($oldimageinfo[1]);
        if  ($old_image_width  <=  $width  &&  $old_image_height  <=  $height)  {
            @unlink(UPLOAD_PATH  .  $thumbFile);
            @copy(UPLOAD_PATH  .  $oldFile,  UPLOAD_PATH  .  $thumbFile);
            $info['src']  =  $thumbFile;
            $info['width']  =  $old_image_width;
            $info['height']  =  $old_image_height;
            return  $info;
        }  else  {
            //生成缩略图  -  更好的方法
            if  ($height  ==  "auto")  $height  =  0;
            //import('phpthumb.PhpThumbFactory');
            require_once('ThinkPHP/Library/Vendor/phpthumb/PhpThumbFactory.class.php');
            $thumb  =  PhpThumbFactory::create(UPLOAD_PATH  .  $filename);
            if  ($cut)  {
                $thumb->adaptiveResize($width,  $height);
            }  else  {
                $thumb->resize($width,  $height);
            }
            $res  =  $thumb->save(UPLOAD_PATH  .  $thumbFile);
            //缩图失败
            if  (!$res)  {
                $thumbFile  =  $oldFile;
            }
            $info['width']  =  $width;
            $info['height']  =  $height;
            $info['src']  =  $thumbFile;
            return  $info;
        }
    }
}

function getRootUrl() {
    return "http://$_SERVER[HTTP_HOST]$GLOBALS[_root]";
}




/**
 * 友好的时间显示
 *
 * @param int    $sTime 待显示的时间
 * @param string $type  类型. normal | mohu | full | ymd | other
 * @param string $alt   已失效
 * @return string
 */
function friendlyDate($sTime,$type = 'normal',$alt = 'false') {
    if (!$sTime)
        return '';
    //sTime=源时间，cTime=当前时间，dTime=时间差
    $cTime      =   time();
    $dTime      =   $cTime - $sTime;
    $dDay       =   intval(date("z",$cTime)) - intval(date("z",$sTime));
    //$dDay     =   intval($dTime/3600/24);
    $dYear      =   intval(date("Y",$cTime)) - intval(date("Y",$sTime));
    //normal：n秒前，n分钟前，n小时前，日期
    if($type=='normal'){
        if( $dTime < 60 ){
            if($dTime < 10){
                return '刚刚';    //by yangjs
            }else{
                return intval(floor($dTime / 10) * 10)."秒前";
            }
        }elseif( $dTime < 3600 ){
            return intval($dTime/60)."分钟前";
            //今天的数据.年份相同.日期相同.
        }elseif( $dYear==0 && $dDay == 0  ){
            //return intval($dTime/3600)."小时前";
            return '今天'.date('H:i',$sTime);
        }elseif($dYear==0){
            return date("m月d日 H:i",$sTime);
        }else{
            return date("Y-m-d H:i",$sTime);
        }
    }elseif($type=='mohu'){
        if( $dTime < 60 ){
            return $dTime."秒前";
        }elseif( $dTime < 3600 ){
            return intval($dTime/60)."分钟前";
        }elseif( $dTime >= 3600 && $dDay == 0  ){
            return intval($dTime/3600)."小时前";
        }elseif( $dDay > 0 && $dDay<=7 ){
            return intval($dDay)."天前";
        }elseif( $dDay > 7 &&  $dDay <= 30 ){
            return intval($dDay/7) . '周前';
        }elseif( $dDay > 30 ){
            return intval($dDay/30) . '个月前';
        }
        //full: Y-m-d , H:i:s
    }elseif($type=='full'){
        return date("Y-m-d , H:i:s",$sTime);
    }elseif($type=='ymd'){
        return date("Y-m-d",$sTime);
    }else{
        if( $dTime < 60 ){
            return $dTime."秒前";
        }elseif( $dTime < 3600 ){
            return intval($dTime/60)."分钟前";
        }elseif( $dTime >= 3600 && $dDay == 0  ){
            return intval($dTime/3600)."小时前";
        }elseif($dYear==0){
            return date("Y-m-d H:i:s",$sTime);
        }else{
            return date("Y-m-d H:i:s",$sTime);
        }
    }
}