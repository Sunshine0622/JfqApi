<!DOCTYPE html>
<html>
<head>
    <title>爱盈利外放接口说明</title>
    <meta charset='utf-8'>
    <meta name="robots" content="noindex">
    <link href='https://dn-maxiang.qbox.me/res-min/themes/marxico.css' rel='stylesheet'>
    <link rel="Shortcut icon" href="http://jfad.appubang.com/application/views/icon.jpg" />
    <style>
        
    </style>
</head>
<body>
    <div id='preview-contents' class='note-content'>
        <div id="wmd-preview" class="preview-content"></div>
        <div id="wmd-preview-section-39" class="wmd-preview-section preview-content">
        </div>
        <div id="wmd-preview-section-1298" class="wmd-preview-section preview-content">
            <h2 id="联盟渠道快速任务合作接口">爱盈利外放(<?php echo $cp_name;?>)接口说明</h2>
            <blockquote>
                <p>
                    <!-- 渠道名称：<span class="source"></span> <br> -->
                    App Name：<span class=""><?php echo $app_name;?></span> <br>
                    App ID：<span class="appid"><?php echo $appid;?></span> <br>
                    iTunes URL：<a href="https://itunes.apple.com/cn/app/id<?php echo $appid?>?mt=8" target="_blank">https://itunes.apple.com/cn/app/id<?php echo $appid;?>?mt=8</a> <br>
                   <!--  CPA单价：<span class="price"></span> 元 <br> -->
                   <?php
                        if($is_advert == 1){
                            if($is_source==1 && $is_repeat==1){
                                echo '对接模式：回调任务（排重 + 点击上报+激活回调）';
                            }else if($is_source==1 && $is_repeat==0){
                               echo '对接模式：回调任务（点击上报+激活回调）';
                            }
                        }else{
                            if($is_source == 1 && $is_repeat==0 && $is_submit==0){
                                echo '对接模式：快速任务（点击上报）';

                            }else if($is_source == 1 && $is_repeat==1 && $is_submit==0){
                                 echo '对接模式：快速任务（排重+点击上报）';
                            }else if($is_source == 1 && $is_repeat==1 && $is_submit==1){
                                 echo '对接模式：快速任务（排重+点击上报+激活上报）';
                            }else if($is_source == 0 && $is_repeat==1 && $is_submit==0){
                                 echo '对接模式：快速任务（排重）';
                            }else if($is_source == 0 && $is_repeat==1 && $is_submit==1){
                                 echo '对接模式：快速任务（排重+激活上报）';
                            }else if($is_source == 1 && $is_repeat==0 && $is_submit==1){
                                 echo '对接模式：快速任务（点击上报+激活上报）';
                            }else if($is_source == 1 && $is_repeat==0 && $is_submit==0){
                                 echo '对接模式：快速任务（点击上报）';
                            }else if($is_source == 0 && $is_repeat==0 && $is_submit==1){
                                 echo '对接模式：快速任务（激活上报）';
                            }
                        }
                   ?>
                </p>
            </blockquote>
        </div>
        <?php if($is_repeat==1){


       echo '<div id="wmd-preview-section-1461" class="wmd-preview-section preview-content" style="">
            <h3 id="排重接口">排重接口</h3>
            <p>
                请求方式：<code >GET</code> <br>
                请求地址：<a href="javascript:void(0)" >http://asoapi.appubang.com/api/aso_IdfaRepeat/cpid/'.$channel.'</a>
            </p>
            <p>固定参数:</p>
            <ul>
                <!-- <li>cpid：<span class="source"></span></li> -->
                <li>appid：<span class="appid">'.$appid.'</span></li>
                <li>adid：<span class="adid">'.$cpid.'</span></li>
            </ul>
            <p>动态参数：</p>
            <ul>
                <li>idfa：<code>必填</code></li>
                <li>ip：<code>必填，用户端IP，请勿使用服务端IP</code></li>
                
            </ul>
            <p>示例如下：</p>
            <blockquote>
                <p>
                    <a href="javascript:void(0)" >
                        http://asoapi.appubang.com/api/aso_IdfaRepeat/cpid/'.$channel.'?appid='.$appid.'&amp;adid='.$cpid.'&amp;ip=[user_ip]&amp;idfa=[idfa]
                    </a>
                </p>
            </blockquote>
            <p>返回结果：</p>
            <blockquote>
                <p>
                    {‘3BA5ADEE-6467-4FB7-9424-6D873071A73D’:0} // 0表示已经安装过（不能做任务） <br>
                    {‘3BA5ADEE-6467-4FB7-9424-6D873071A73D’:1} // 1表示未安装（可以做任务）
                </p>
            </blockquote>
        </div>';
        }
        ?>
        <?php if($is_source==1){


         echo '<div id="wmd-preview-section-1397" class="wmd-preview-section preview-content">
            <h3 id="点击上报接口">点击上报接口</h3>
            <p>
                请求方式：<code>GET</code> <br>
                请求地址：<a href="javascript:void(0)" target="">http://asoapi.appubang.com/api/aso_source/cpid/'.$channel.'</a>
            </p>
            <p>固定参数:</p>
            <ul>
                <!-- <li>source：<span class="source"></span></li> -->
                <li>appid：<span class="appid">'.$appid.'</span></li>
                <li>adid：<span class="adid">'.$cpid.'</span></li>
            </ul>
            <p>动态参数：</p>
            <ul>
                <li>idfa：<code>必填</code></li>
                <li>ip：<code>必填，用户端IP，请勿使用服务端IP</code></li>
                <li>device：<code>必填，设备型号，如：iphone11,2</code></li>
                <li>os：<code>必填，系统版本号，如：11.4</code></li>
                <li>timestamp：<code>必填，当前时间戳</code></li>
                <li>sign：<code>必填，请求参数的MD5签名,除非特殊说明</code></li>
                <li>keywords：<code>任务关键词，必填，除非特殊说明</code></li>
                <li>callback：<code> 回调链接需要urlencode，可选，回调任务需传参数</code></li>
                <!-- <li>deviceType：<code>设备类型，可选，除非特殊说明（如deviceType=iPhone8,1）</code></li> -->
            </ul>
            <p>示例如下：</p>
            <blockquote>
                <p>
                    <a href="javascript:void(0)" target="">
                        http://asoapi.appubang.com/api/aso_source/cpid/'.$channel.'?appid='.$appid.'&amp;adid='.$cpid.'&amp;device=[device]&amp;os=[os]&amp;idfa=[idfa]&amp;ip=[ip]&amp;timestamp=1532587790&amp;sign=[sign]&amp;keywords=[keywords]&amp;callback=urlencode([keywords])
                    </a>
                </p>
            </blockquote>
            <p>返回结果：</p>
            <blockquote>
                <p>
                    {"code":0,"result":"ok"} // 0表示点击上报请求成功,其它为上报失败<br>
                </p>
            </blockquote>
        </div>';
         }?>

         <?php if($is_submit==1){


        echo '<div id="wmd-preview-section-1397" class="wmd-preview-section preview-content">
            <h3 id="激活上报接口">激活上报接口</h3>
            <p>
                请求方式：<code>GET</code> <br>
                请求地址：<a href="javascript:void(0)" target="">http://asoapi.appubang.com/api/aso_submit/cpid/'.$channel.'</a>
            </p>
            <p>固定参数:</p>
            <ul>
                <!-- <li>source：<span class="source"></span></li> -->
                <li>appid：<span class="appid">'.$appid.'</span></li>
                <li>adid：<span class="adid">'.$cpid.'</span></li>
            </ul>
            <p>动态参数：</p>
            <ul>
                <li>idfa：<code>必填</code></li>
                <li>timestamp：<code>必填，当前时间戳</code></li>
                <li>sign：<code>必填，请求参数的MD5签名</code></li>
                <li>ip：<code>必填，客户端IP，请勿使用服务端IP</code></li>
                <li>keywords：<code>任务关键词，可选，除非特殊说明</code></li>
                <!-- <li>deviceType：<code>设备类型，可选，除非特殊说明（如deviceType=iPhone8,1）</code></li> -->
            </ul>
            <p>示例如下：</p>
            <blockquote>
                <p>
                    <a href="javascript:void(0)" target="">
                        http://asoapi.appubang.com/api/aso_submit/cpid/'.$channel.'?appid='.$appid.'&amp;adid='.$cpid.'&amp;idfa=[idfa]&amp;timestamp=1532587790&amp;sign=[sign]&amp;keywords=[keywords]
                    </a>
                </p>
            </blockquote>
            <p>返回结果：</p>
            <blockquote>
                <p>
                    {"code":0,"result":"ok"} // 0表示激活上报请求成功,其它为上报失败<br>
                </p>
            </blockquote>
        </div>';
        }?>
        <?php if(($is_source==1 || $is_submit==1) && $key!=''){


       echo  '<div id="wmd-preview-section-671" class="wmd-preview-section preview-content"> <h3>签名算法：</h3>

<div style="margin-top:10px;">将参数列表中的timestamp的值与密钥拼接得到的字符串进行MD5即得到所需的sign</div>

<div style="margin-top:10px;">例：timestamp=1453271664；</div>

<div style="margin-top:10px;">分配渠道密钥（key值）：'.$key.'</div>

<div style="margin-top:10px;">拼接得字符串：1453271664'.$key.'</div>

<div style="margin-top:10px;">MD5以后sign为：' .md5("1453271664".$key).'</div>
</div>';
}?>
        <div id="wmd-preview-section-footnotes" class="preview-content"></div>
    </div>
    <!-- <script>
        function GetQueryString(name) {
            var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
            var r = window.location.search.substr(1).match(reg);
            if (r != null) return decodeURI(r[2]); return null;
        }

        window.onload = function () {
            var source = GetQueryString("cpid");
            var appname = GetQueryString("appname");
            var appid = GetQueryString("appid");
            var price = GetQueryString("price");
            var adid = GetQueryString("adid");

            var docs = document.getElementsByClassName("appname");
            for (var i = 0; i < docs.length; i++) {
                docs[i].innerText = appname;
            }

            docs = document.getElementsByClassName("appid");
            for (i = 0; i < docs.length; i++) {
                docs[i].innerText = appid;
            }

            docs = document.getElementsByClassName("price");
            for (i = 0; i < docs.length; i++) {
                docs[i].innerText = price;
            }

            docs = document.getElementsByClassName("source");
            for (i = 0; i < docs.length; i++) {
                docs[i].innerText = source;
            }

            docs = document.getElementsByClassName("adid");
            for (i = 0; i < docs.length; i++) {
                docs[i].innerText = adid;
            }
        }
    </script> -->
</body>
</html>
