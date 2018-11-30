
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
     <link rel="Shortcut icon" href="http://jfad.appubang.com/application/views/icon.jpg" />
    <title><?php echo $app_name?>接口文档说明</title>
    <!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet" href="http://jfad.appubang.com/public/layui/css/layui.css">
</head>
<body class="layui-container">

<br>
<br>
<h1>爱盈利外放(<?php echo $cp_name;?>)接口文档
     <?php
        if($is_advert == 1){
            if($is_source==1 && $is_repeat==1){
                echo '模式：回调任务（排重 + 点击上报+激活回调）';
            }else if($is_source==1 && $is_repeat==0){
               echo '模式：回调任务（点击上报+激活回调）';
            }
        }else{
            if($is_source == 1 && $is_repeat==0 && $is_submit==0){
                echo '模式：快速任务（点击上报）';

            }else if($is_source == 1 && $is_repeat==1 && $is_submit==0){
                 echo '模式：快速任务（排重+点击上报）';
            }else if($is_source == 1 && $is_repeat==1 && $is_submit==1){
                 echo '模式：快速任务（排重+点击上报+激活上报）';
            }else if($is_source == 0 && $is_repeat==1 && $is_submit==0){
                 echo '模式：快速任务（排重）';
            }else if($is_source == 0 && $is_repeat==1 && $is_submit==1){
                 echo '模式：快速任务（排重+激活上报）';
            }else if($is_source == 1 && $is_repeat==0 && $is_submit==1){
                 echo '模式：快速任务（点击上报+激活上报）';
            }else if($is_source == 1 && $is_repeat==0 && $is_submit==0){
                 echo '模式：快速任务（点击上报）';
            }else if($is_source == 0 && $is_repeat==0 && $is_submit==1){
                 echo '模式：快速任务（激活上报）';
            }
        }
   ?>
</h1>


<div class="layui-collapse" lay-accordion>

    <div class="layui-colla-item">
        <h2 class="layui-colla-title" style="font-weight:bold;font-size:18px">产品信息</h2>
        <div class="layui-colla-content layui-show">
            <p><img style="max-height: 50px;max-width: 50px;" src="<?php echo $image;?>" alt=""></p>
            <p>产品名称: <span style="color:orange"><?php echo $app_name;?></span></p>
            <p>产品链接:<a href="https://itunes.apple.com/cn/app/id<?php echo $appid;?>" style="color:orange;" target="_blank"> https://itunes.apple.com/cn/app/id<?php echo $appid;?>?mt=8</a></p>
        </div>
    </div>

         <?php if($is_repeat==1){
            echo '<div class="layui-colla-item">
            <h2 class="layui-colla-title" style="font-weight:bold;font-size:18px">去重</h2>
            <div class="layui-colla-content">
                <p><span style="">请求地址:</span> <a href="javascript:;" style="color:orange">http://asoapi.appubang.com/api/aso_IdfaRepeat/cpid/'.$channel.' </a></p>
                <p><span style="">请求方法:</span> <span style="color:orange">get </span> </p>
                <p><span style="">固定参数: </span><span style="color:orange">appid：'.$appid.' </span>(苹果ID)</p>
                 <p><span style="">固定参数: </span><span style="color:orange">adid：'.$cpid.' </span>(我方提供的广告ID)</p>
               <p><span style="">动态参数: </span><span style="color:orange">idfa </span>(用户设备的IDFA)</p>
                 <p><span style="f">动态参数: </span><span style="color:orange">ip </span>(客户端IP)</p>
                <hr>
                <p>返回值： {3AA65038-A422-4302-8DEF-30E02B0BFC2B:1}</p>
                 <p>1 : 没有安装，可以展示</p>
                <p>0 : 已经安装，不可展示</p>
               

                <hr>
                <p>请求示例： <a href="http://asoapi.appubang.com/api/aso_IdfaRepeat/cpid/'.$channel.'?appid='.$appid.'&adid='.$cpid.'&idfa={idfa}&ip={ip}" target="_blank" style="color:orange">http://asoapi.appubang.com/api/aso_IdfaRepeat/cpid/'.$channel.'?appid='.$appid.'&adid='.$cpid.'&idfa={idfa}&ip={ip} </a></p>
            </div>
        </div>';
    
         }
        ?>

        <?php if($is_source==1){
             if($key!=''){
                $str ='<p><span style="f">动态参数: </span><span style="color:orange">timestamp </span>(请求时间戳)</p>
                 <p><span style="f">动态参数: </span><span style="color:orange">sign </span>(加密签名)</p>';
                 $ustr='&timestamp={timestamp}&sign={sign}';
            }else{
                $str='';
                $ustr='';
            }
            echo '<div class="layui-colla-item">
            <h2 class="layui-colla-title" style="font-weight:bold;font-size:18px">点击</h2>
            <div class="layui-colla-content">
                <p><span style="">请求地址:</span> <a href="javascript:;" style="color:orange">http://asoapi.appubang.com/api/aso_source/cpid/'.$channel.' </a></p>
                <p><span style="">请求方法:</span> <span style="color:orange">get </span> </p>
                <p><span style="">固定参数: </span><span style="color:orange">appid：'.$appid.' </span>(苹果ID)</p>
                 <p><span style="">固定参数: </span><span style="color:orange">adid：'.$cpid.' </span>(我方提供的广告ID)</p>
                <p><span style="">动态参数: </span><span style="color:orange">idfa </span>(用户设备的IDFA)</p>
                 <p><span style="f">动态参数: </span><span style="color:orange">device </span>(设备型号*，如iPhone8,2)</p>
                  <p><span style="">动态参数: </span><span style="color:orange">os </span>(系统版本*,如11.1.2)</p>
                 <p><span style="f">动态参数: </span><span style="color:orange">ip </span>(客户端IP)</p>
                 <p><span style="f">动态参数: </span><span style="color:orange">keywords </span>(任务关键词)</p>
                  <p><span style="f">动态参数: </span><span style="color:orange">callback </span>(回调链接 需要urlencode 回调任务必传)</p>'.$str.'
                
                <hr>
                <p>返回值： {"code":0,"result":"ok"}</p>
                 <p>0 : 请求成功</p>
                <p>103 : 请求失败</p>
               

                <hr>
                <p>请求示例： <a href="http://asoapi.appubang.com/api/aso_source/cpid/'.$channel.'?appid='.$appid.'&adid='.$cpid.'&idfa={idfa}&ip={ip}&os={os}&device={$device}&keywords={$keywords}&callback={callback}'.$ustr.'" target="_blank" style="color:orange">http://asoapi.appubang.com/api/aso_source/cpid/'.$channel.'?appid='.$appid.'&adid='.$cpid.'&idfa={idfa}&ip={ip}&os={os}&device={device}&keywords={keywords}&callback={callback}'.$ustr.'</a></p>
            </div>
        </div>';
    
         }
        ?>

         <?php if($is_submit==1){
            if($key!=''){
                $str ='<p><span style="f">动态参数: </span><span style="color:orange">timestamp </span>(请求时间戳)</p>
                 <p><span style="f">动态参数: </span><span style="color:orange">sign </span>(加密签名)</p>';
                 $ustr='&timestamp={timestamp}&sign={sign}';
            }else{
                $str='';
                $ustr='';
            }
            echo '<div class="layui-colla-item">
            <h2 class="layui-colla-title" style="font-weight:bold;font-size:18px">激活</h2>
            <div class="layui-colla-content">
                <p><span style="">请求地址:</span> <a href="javascript:;" style="color:orange">http://asoapi.appubang.com/api/aso_submit/cpid/'.$channel.' </a></p>
                <p><span style="">请求方法:</span> <span style="color:orange">get </span> </p>
                <p><span style="">固定参数: </span><span style="color:orange">appid：'.$appid.' </span>(苹果ID)</p>
                 <p><span style="">固定参数: </span><span style="color:orange">adid：'.$cpid.' </span>(我方提供的广告ID)</p>
               <p><span style="">动态参数: </span><span style="color:orange">idfa </span>(用户设备的IDFA)</p>
                 <p><span style="f">动态参数: </span><span style="color:orange">ip </span>(客户端IP)</p>'.$str.'
                  
                <hr>
                <p>返回值： {"code":0,"result":"ok"}</p>
                 <p>0 : 请求成功</p>
                <p>103 : 请求失败</p>
               

                <hr>
                <p>请求示例： <a href="http://asoapi.appubang.com/api/aso_submit/cpid/'.$channel.'?appid='.$appid.'&adid='.$cpid.'&idfa={idfa}&ip={ip}'.$ustr.'" target="_blank" style="color:orange">http://asoapi.appubang.com/api/aso_submit/cpid/'.$channel.'?appid='.$appid.'&adid='.$cpid.'&idfa={idfa}&ip={ip}'.$ustr.'</a></p>
            </div>
        </div>';
    
         }
        ?>




     <?php if(($is_source==1 || $is_submit==1) && $key!=''){
            echo '<div class="layui-colla-item">
            <h2 class="layui-colla-title" style="font-weight:bold;font-size:18px">签名算法</h2>
            <div class="layui-colla-content">
              

<div style="margin-top:10px;">将参数列表中的timestamp的值与密钥拼接得到的字符串进行MD5即得到所需的sign</div>

<div style="margin-top:10px;">例：timestamp=1453271664；</div>

<div style="margin-top:10px;">分配渠道密钥（key值）：'.$key.'</div>

<div style="margin-top:10px;">拼接得字符串：1453271664'.$key.'</div>

<div style="margin-top:10px;">MD5以后sign为：' .md5("1453271664".$key).'</div>
</div>
            </div>
        </div>
    
</div>';
}?>

<script src="http://jfad.appubang.com/public/layui/layui.js"></script>
<script>
    //注意：折叠面板 依赖 element 模块，否则无法进行功能性操作
    layui.use('element', function () {
        var element = layui.element;

        //…
    });
</script>
</body>
</html>