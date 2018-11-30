<!DOCTYPE html>
<html>
    <head>
        <title>爱盈利-外放对接文档</title>
        <meta charset='utf-8'>
        <style>
            .table-style{border-color:red;}
             tr td{
                text-align:center;
             }
        </style>
     </head>
     <body > 
     <div style="width:80%">
<p style="text-align:center">
    <span style=";font-family:微软雅黑;font-size:29px"><span style="font-family:微软雅黑">爱普优邦</span></span><span style=";font-family:Tahoma;font-size:29px">外放ASO</span><span style=";font-family:微软雅黑;font-size:29px"><span style="font-family:微软雅黑">接口文档</span></span>
</p>
<p>
    <span style=";font-family:微软雅黑;color:rgb(255,0,0);font-size:19px">PS<span style="font-family:微软雅黑">：注意文档内容仅为示例</span></span>
</p>
<p>
    <span style=";font-family:微软雅黑;font-size:19px"><span style="font-family:微软雅黑">概述：</span></span>
</p>
<p>
    <span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 请注意，文档中涉及接口部分英文皆对大小写敏感。</span></span>
</p>
<p>
    <span style=";font-family:微软雅黑;font-size:19px"><span style="font-family:微软雅黑">名词解释：</span></span>
</p>
<p>
    <span style=";font-family:微软雅黑;font-size:16px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; cpid:<span style="font-family:微软雅黑">由爱普优邦为合作方所提供，且为唯一值</span></span>
</p>
<p style="text-indent:48px">
    <span style=";font-family:微软雅黑;font-size:16px">adid:</span><span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">由爱普优邦为合作方所提供</span></span>
</p>
<p>
    <span style=";font-family:微软雅黑;font-size:16px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; key:<span style="font-family:微软雅黑">由爱普优邦提供与</span><span style="font-family:Tahoma">cpid</span><span style="font-family:微软雅黑">相绑定通信密钥（</span><span style="font-family:Tahoma">32</span><span style="font-family:微软雅黑">位</span><span style="font-family:Tahoma">16</span><span style="font-family:微软雅黑">进制字符串）。请勿泄露。</span></span>
</p>
<p>
    <span style=";font-family:微软雅黑;font-size:19px"><span style="font-family:微软雅黑">接口说明：</span></span>
</p>
<p>
    <span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 请求地址：</span></span><a href="http://asoapi.appubang.com/接口名/cpid/合作方id(我方提供)/"><span style="text-decoration:underline;"><span style=";font-family:Calibri;color:rgb(0,0,255);text-underline:single;font-size:16px">http://</span></span><span style="text-decoration:underline;"><span style=";font-family:微软雅黑;color:rgb(0,0,255);text-underline:single;font-size:16px">asoapi.appubang</span></span><span style="text-decoration:underline;"><span style=";font-family:Calibri;color:rgb(0,0,255);text-underline:single;font-size:16px">.com/</span></span><span style="text-decoration:underline;"><span style=";font-family:微软雅黑;color:rgb(0,0,255);text-underline:single;font-size:16px"><span style="font-family:微软雅黑">接口名</span></span></span><span style="text-decoration:underline;"><span style=";font-family:Calibri;color:rgb(0,0,255);text-underline:single;font-size:16px">/cpid/</span></span><span style="text-decoration:underline;"><span style=";font-family:微软雅黑;color:rgb(0,0,255);text-underline:single;font-size:16px"><span style="font-family:微软雅黑">合作方</span>id</span></span><span style="text-decoration:underline;"><span style=";font-family:Calibri;color:rgb(0,0,255);text-underline:single;font-size:16px">/</span></span></a><span style=";font-family:微软雅黑;font-size:16px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style=";font-family:微软雅黑;font-size:19px">&nbsp;</span><span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">（请求方式为</span>GET<span style="font-family:微软雅黑">）</span></span>
</p>
<p>
    <span style=";font-family:微软雅黑;font-size:19px"><span style="font-family:微软雅黑">接口列表：</span></span>
</p>
<p>
    <span style=";font-family:微软雅黑;color:rgb(255,0,0);font-size:19px"><span style="font-family:微软雅黑">排重接口</span></span><span style=";font-family:宋体;color:rgb(255,0,0);font-size:19px"><span style="font-family:宋体">：</span></span><span style=";font-family:Tahoma;font-size:19px">api/aso_IdfaRepeat</span>
</p>
<p style="text-indent:28px">
    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:19px">·</span></strong><span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">请求参数用</span>&amp;<span style="font-family:微软雅黑">拼接：例拼接后接口地址：</span></span>
</p>
<p style="text-indent:28px">
    <a href="http://asoapi.appubang.com/api/aso_IdfaRepeat/cpid/207/?appid=981025209&idfa=9N0224F3-3A98-48A5-AA35-FC40772F76KA&adid=1">http://asoapi.appubang.com/api/aso_IdfaRepeat/cpid/合作方cpid/?appid=981025209&idfa=9N0224F3-3A98-48A5-AA35-FC40772F76KA&adid=1</a>
</p>
<p style="text-indent:28px">
    <span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">请求方式：</span>GET</span>
</p>
<p style="text-indent:28px">
    <span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">请求参数：</span></span>
</p>
<p style="text-indent:28px">
    <span style=";font-family:微软雅黑;font-size:16px">&nbsp;</span>
</p>
<table border="1" width="600px" height="50px" style="margin-top:-40px;margin-left:30px">
  <tr>
    <th style=";font-family:微软雅黑;font-size:16px">参数名称</th>
    <th style=";font-family:微软雅黑;font-size:16px">数据类型</th>
     <th style=";font-family:微软雅黑;font-size:16px">参数说明</th>
  </tr>
  <tr>
     <th style=";font-family:微软雅黑;font-size:16px">appid</th>
    <th style=";font-family:微软雅黑;font-size:16px">int</th>
     <th style=";font-family:微软雅黑;font-size:16px">广告主推广 app 标识（AppstoreID）</th>
  </tr>
  <tr>
     <th style=";font-family:微软雅黑;font-size:16px">idfa</th>
    <th style=";font-family:微软雅黑;font-size:16px">char(36)</th>
     <th style=";font-family:微软雅黑;font-size:16px">设备 IDFA</th>
  </tr>
  <tr>
     <th style=";font-family:微软雅黑;font-size:16px">adid</th>
    <th style=";font-family:微软雅黑;font-size:16px">int</th>
     <th style=";font-family:微软雅黑;font-size:16px">由爱普优邦为合作方所提供</th>
  </tr>
  <tr>
     <th style=";font-family:微软雅黑;font-size:16px">ip</th>
    <th style=";font-family:微软雅黑;font-size:16px">varchar(20)</th>
     <th style=";font-family:微软雅黑;font-size:16px">客户端ip地址</th>
  </tr>
</table>
<p style="text-indent:28px">
    <span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">返回结果</span></span>
</p>
<table  border="1" width="600px" height="50px" style="margin-left:30px">
    <tbody>
        <tr class="firstRow">
            <td  valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">返回 json 格式数据</span>
                </p>
            </td>
            <td width="268" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">说明</span>
                </p>
            </td>
        </tr>
        <tr>
            <td  width="300" valign="top">
                <p style="margin-bottom:0;text-align:justify;text-justify:inter-ideograph">
                    <span style=";font-family:微软雅黑;font-size:16px">{&quot;57AE04BF-D17A-4236-898B-2FD16365B28F&quot;:1}</span>
                </p>
            </td>
            <td  width="268" valign="top">
                <p style="margin-bottom:0;text-indent:28px">
                    <span style=";font-family:微软雅黑;font-size:16px">0:该 IDFA 已下载过不可做任务</span>
                </p>
                <p style="margin-bottom:0;text-indent:28px;text-align:justify;text-justify:inter-ideograph">
                    <span style=";font-family:微软雅黑;font-size:16px">1:该 IDFA 没下载过可以做任务</span>
                </p>
            </td>
        </tr>
    </tbody>
</table>
<p>
    <span style=";font-family:微软雅黑;color:rgb(255,0,0);font-size:19px"><span style="font-family:微软雅黑">注意：排重返回值和其它渠道的区别</span></span>
</p>
<p>
    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:19px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ·</span></strong><span style=";font-family:微软雅黑;color:rgb(255,0,0);font-size:19px">aso<span style="font-family:微软雅黑">点击接口</span></span><span style=";font-family:微软雅黑;font-size:19px"><span style="font-family:微软雅黑">：</span></span><span style=";font-family:Tahoma;font-size:19px">api/aso_source</span>
</p>
<p>
    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:19px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ·</span></strong><span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">请求参数用</span>&amp;<span style="font-family:微软雅黑">拼接：例拼接后接口地址：</span></span><a href="http://asoapi.appubang.com/api/aso_source/cpid/207/?appid=981025209&idfa=9N0224F3-3A98-48A5-AA35-FC40772F76KA"><span style="text-decoration:underline;"><span style=";font-family:Calibri;color:rgb(0,0,255);text-underline:single;font-size:16px">http://asoapi.appubang.com/api/aso_source/cpid/</span></span><span style="text-decoration:underline;"><span style=";font-family:微软雅黑;color:rgb(0,0,255);text-underline:single;font-size:16px">合作方cpid</span></span><span style="text-decoration:underline;"><span style=";font-family:Calibri;color:rgb(0,0,255);text-underline:single;font-size:16px">/?appid=981025209&amp;idfa=9N0224F3-3A98-48A5-AA35-FC40772F76KA</span></span></a><span style=";font-family:微软雅黑;font-size:16px">........................</span>
</p>
<table border="1" width="600px" height="50px" style="margin-left:30px">
    <tbody>
        <tr class="firstRow">
            <td style="padding: 0px 7px; border-width: 1px; border-style: solid; border-color: windowtext;" width="101" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:16px">参数名称</span></strong>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="107" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:16px">数据类型</span></strong>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="80" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:16px">是否必须</span></strong>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="254" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:16px">参数说明</span></strong>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="101" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">appid</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="107" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">int</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="80" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="254" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">应用<span style="font-family:Tahoma">id,</span><span style="font-family:微软雅黑">每个不同应用不同值</span></span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="101" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">idfa</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="107" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">char(36)</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="80" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="254" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">设备<span style="font-family:Tahoma">IDFA</span></span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="101" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">ip</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="107" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">string</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="80" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="254" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">用户<span style="font-family:Tahoma">ip</span><span style="font-family:微软雅黑">地址</span></span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="101" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">timestamp</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="107" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">int(10)</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="80" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="254" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">当前时间戳（<span style="font-family:Tahoma">+8</span><span style="font-family:微软雅黑">区）</span></span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="101" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">reqtype</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="107" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">int</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="80" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">否</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="254" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">0<span style="font-family:微软雅黑">，表示点击下载一个应用</span></span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="101" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">device</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="107" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">string</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="80" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="254" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">设备类型</span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="101" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">os</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="107" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">string</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="80" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="254" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">操作系统版本号</span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="101" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">isbreak</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="107" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">int</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="80" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">否</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="254" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">是否越狱，<span style="font-family:Tahoma">0</span><span style="font-family:微软雅黑">：没有，</span><span style="font-family:Tahoma">1</span><span style="font-family:微软雅黑">已越狱</span></span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="101" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">sign</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="107" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">string</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="80" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">否</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="254" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">请求参数的<span style="font-family:Tahoma">MD5</span><span style="font-family:微软雅黑">签名</span></span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="101" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">adid</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="107" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">int</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="80" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="254" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">由爱普优邦为合作方所提供</span>
                </p>
            </td>
        </tr>
          <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="101" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">callback</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="107" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">varchar(255)</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="80" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">否</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="254" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">回调链接，回调任务需传参数</span><span style="color:red">(注意：回调成功统一值请设置为：json格式{"success":true,"message":'ok'},如不能统一格式请说明)</span>
                </p>
            </td>
        </tr>
    </tbody>
</table>
<p>
    <span style=";font-family:微软雅黑;font-size:19px"><span style="font-family:微软雅黑">签名算法：</span></span>
</p>
<p>
    <span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">将参数列表中的</span>timestamp<span style="font-family:微软雅黑">的值与密钥拼接得到的字符串进行</span></span><span style=";font-family:微软雅黑;font-size:15px">MD5<span style="font-family:微软雅黑">即得到所需的</span><span style="font-family:Tahoma">sign</span></span>
</p>
<p>
    <span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">例：</span></span><span style=";font-family:Tahoma;font-size:16px">timestamp=1453271664</span><span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">；</span></span>
</p>
<p >
    <span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">例如密钥：</span></span><span style=";font-family:Tahoma;font-size:16px">81dc9bdb52d04dc20036dbd8313ed055</span>
</p>
<p >
    <span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">拼接得字符串：</span></span><span style=";font-family:Tahoma;font-size:16px">145327166481dc9bdb52d04dc20036dbd8313ed055</span>
</p>
<p>
    <span style=";font-family:微软雅黑;font-size:16px">MD5<span style="font-family:微软雅黑">以后</span><span style="font-family:Tahoma">sign</span><span style="font-family:微软雅黑">为：</span></span><span style=";font-family:Tahoma;font-size:16px">0cb1f54ffbdd715db96df4be59640283</span>
</p>
<p>
    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:19px"><span style="font-family:微软雅黑">返回结果</span></span></strong>
</p>
<p>
    <strong> </strong>
</p>
<table border="1" width="600px" height="50px" style="margin-left:30px">
    <tbody>
        <tr class="firstRow">
            <td style="padding: 0px 7px; border-width: 1px; border-style: solid; border-color: windowtext;" width="102" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">参数名称</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="104" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">数据类型</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="104" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">返回值</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="145" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">参数说明</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="139" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">举例</span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="102" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">code</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="104" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">int</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="104" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="145" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">结果代码，<span style="font-family:Tahoma">0</span><span style="font-family:微软雅黑">成功，非零为失败</span></span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="139" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">0</span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="102" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">result</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="104" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">string</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="104" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="145" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">结果说明字符串</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="139" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">成功为：<span style="font-family:Tahoma">ok;</span><span style="font-family:微软雅黑">失败为具体信息</span></span>
                </p>
            </td>
        </tr>
    </tbody>
</table>
<p>
    <span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">返回值：</span></span>
</p>
<p>
    <span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 返回值皆为</span>JSON<span style="font-family:微软雅黑">数据格式为：</span></span><span style=";font-family:Tahoma;font-size:16px">{ “code” : 0, ”result” : &quot;ok&quot;}</span>
</p>
<p style="margin-left:48px">
    <span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">当调用成功时，</span>code=0,result<span style="font-family:微软雅黑">固定为</span><span style="font-family:Tahoma">ok</span><span style="font-family:微软雅黑">。如果调用失败，</span><span style="font-family:Tahoma">code</span><span style="font-family:微软雅黑">为其他值，</span><span style="font-family:Tahoma">result</span><span style="font-family:微软雅黑">为具体的错误信息。</span></span>
</p>
<p>
    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:19px">·</span></strong><span style=";font-family:微软雅黑;color:rgb(255,0,0);font-size:19px">aso<span style="font-family:微软雅黑">上报接口</span></span><span style=";font-family:微软雅黑;font-size:19px"><span style="font-family:微软雅黑">：</span></span><span style=";font-family:Tahoma;font-size:19px">api/aso_Submit</span>
</p>
<p>
    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:19px">·</span></strong><span style=";font-family:微软雅黑;font-size:16px"><span style="font-family:微软雅黑">请求参数用</span>&amp;<span style="font-family:微软雅黑">拼接：例拼接后接口地址：</span></span><a href="http://asoapi.appubang.com/api/aso_Submit/cpid/207/?appid=981025209&idfa=9N0224F3-3A98-48A5-AA35-FC40772F76KA"><span style="text-decoration:underline;"><span style=";font-family:Calibri;color:rgb(0,0,255);text-underline:single;font-size:16px">http://asoapi.appubang.com/api/</span></span><span style="text-decoration:underline;"><span style=";font-family:微软雅黑;color:rgb(0,0,255);text-underline:single;font-size:19px">aso_Submit</span></span><span style="text-decoration:underline;"><span style=";font-family:Calibri;color:rgb(0,0,255);text-underline:single;font-size:16px">/cpid/</span></span><span style="text-decoration:underline;"><span style=";font-family:微软雅黑;color:rgb(0,0,255);text-underline:single;font-size:16px">合作方cpid</span></span><span style="text-decoration:underline;"><span style=";font-family:Calibri;color:rgb(0,0,255);text-underline:single;font-size:16px">/?appid=981025209&amp;idfa=9N0224F3-3A98-48A5-AA35-FC40772F76KA</span></span></a><span style=";font-family:微软雅黑;font-size:16px">........................</span>
</p>
<table border="1" width="600px" height="50px" style="margin-left:30px">
    <tbody>
        <tr class="firstRow">
            <td style="padding: 0px 7px; border-width: 1px; border-style: solid; border-color: windowtext;" width="142" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:16px">参数名称</span></strong>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="142" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:16px">数据类型</span></strong>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="111" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:16px">是否必须</span></strong>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="173" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:16px">参数说明</span></strong>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="142" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">appid</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="142" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">int</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="111" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="173" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">应用<span style="font-family:Tahoma">id,</span><span style="font-family:微软雅黑">每个不同应用不同值</span></span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="142" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">idfa</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="142" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">char(36)</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="111" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="173" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">设备<span style="font-family:Tahoma">IDFA</span></span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="142" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">timestamp</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="142" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">int(10)</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="111" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="173" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">当前时间戳（<span style="font-family:Tahoma">+8</span><span style="font-family:微软雅黑">区）</span></span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="142" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">sign</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="142" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">string</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="111" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="173" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">请求参数的<span style="font-family:Tahoma">MD5</span><span style="font-family:微软雅黑">签名</span></span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="142" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">adid</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="142" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:19px">int</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="111" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="173" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">由爱普优邦为合作方所提供</span>
                </p>
            </td>
        </tr>
    </tbody>
</table>
<p>
    <strong><span style=";font-family:微软雅黑;font-weight:bold;font-size:19px"><span style="font-family:微软雅黑">返回结果</span></span></strong>
</p>
<p>
    <strong> </strong>
</p>
<table border="1" width="600px" height="50px" style="margin-left:30px">
    <tbody>
        <tr class="firstRow">
            <td style="padding: 0px 7px; border-width: 1px; border-style: solid; border-color: windowtext;" width="102" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">参数名称</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="104" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">数据类型</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="104" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">返回值</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="145" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">参数说明</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="139" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">举例</span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="102" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">code</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="104" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">int</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="104" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="145" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">结果代码，<span style="font-family:Tahoma">0</span><span style="font-family:微软雅黑">成功，非零为失败</span></span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="139" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">0</span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px 7px; border-left: 1px solid windowtext; border-right: 1px solid windowtext; border-top: medium none;" width="102" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">result</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="104" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">string</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="104" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style="font-family:微软雅黑;font-size:16px">是</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="145" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">结果说明字符串</span>
                </p>
            </td>
            <td style="padding: 0px 7px; border-left: medium none;" width="139" valign="top">
                <p style="margin-bottom:0;text-align:center">
                    <span style=";font-family:微软雅黑;font-size:16px">成功为：<span style="font-family:Tahoma">ok;</span><span style="font-family:微软雅黑">失败为具体信息</span></span>
                </p>
            </td>
        </tr>
    </tbody>
</table>
<p>
    <span style=";font-family:Tahoma;font-size:15px">&nbsp;</span>
</p>
<p>
    <br/>
</p>
<p style="text-align:center">
    <span style=";font-family:Tahoma;font-size:29px">&nbsp;</span>
</p>
<p>
    <span style=";font-family:Tahoma;font-size:29px">&nbsp;</span>
</p>
<p>
    <br/>
</p>
</div>
</body>
</html>