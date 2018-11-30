<!DOCTYPE html>
<html>
    <head>
        <titleASO广告主对接文档</title>
        <meta charset='utf-8'>
        <link rel="stylesheet" type="text/css" href="static/css/apidoc.css?v=201701040814">
        <link rel="apple-touch-icon" href="/static/ico/apple-touch-icon.png" />
        <link rel="apple-touch-icon" sizes="57x57" href="/static/ico/apple-touch-icon-57x57.png" />
        <link rel="apple-touch-icon" sizes="72x72" href="/static/ico/apple-touch-icon-72x72.png" />
        <link rel="apple-touch-icon" sizes="76x76" href="/static/ico/apple-touch-icon-76x76.png" />
        <link rel="apple-touch-icon" sizes="114x114" href="/static/ico/apple-touch-icon-114x114.png" />
        <link rel="apple-touch-icon" sizes="120x120" href="/static/ico/apple-touch-icon-120x120.png" />
        <link rel="apple-touch-icon" sizes="144x144" href="/static/ico/apple-touch-icon-144x144.png" />
        <link rel="apple-touch-icon" sizes="152x152" href="/static/ico/apple-touch-icon-152x152.png" />
    </head>
    <body>
        <div id='preview-contents' class='note-content'>
            <div id="wmd-preview" class="preview-content"></div>
            <div id="wmd-preview-section-39" class="wmd-preview-section preview-content">
            </div>
            <div id="wmd-preview-section-1298" class="wmd-preview-section preview-content">
                <h2 style="text-align:center"> ASO 广告主对接说明</h2>
                <blockquote>
                  <p style="width:800px">
    <h3>基本流程</h3><br/>
    1. 关键步骤<br/>
    <div style="margin-left:30px;margin-bottom:15px">
    <div>①广告主为我方 ASO 提供一个后台接口（一般称为点击接口）。我方 ASO</div><br/>
    <div>用户开启广告主的 App 推广任务时，我方 ASO 会将广告主应用的 appid 和</div><br/>
    <div>用户设备唯一标识（IOS 设备的 IDFA）等信息提供给广告主，广告主判断用户</div><br/>
    <div>是否能接任务，将结果返回给我方 ASO。</div><br/>
   <div> ② 户通过我方 ASO App 完成广告主 App 的激活任务后，我方 ASO 将</div><br/>
    <div>所有激活的 IDFA 数据导出给广告主，作为结算依据。<br/></div></div>
    2. 注意事项<br/>
    <div style="margin-left:30px">
    <div>① 广告主判断用户是否能接任务，主要是根据 App 历史激活数据，以及我方</div><br/>
    <div>ASO 提供的设备唯一标识（IOS 设备的 IDFA），判断该设备是否已安装过广告</div><br/>
    <div>主的 App，如果安装过，则用户无法进⾏行任务。</div><br/>
    <div>②广告主提供的点击接口，可以根据自身需求，定义参数名称<br/>方式，以提⾼安全性。</div><br/>
    <div>③ 广告主 App 需要获取 IDFA，在提交苹果 AppStore 审核的时候，“Does this</div><br/>
    <div>app use the Advertising Identifier (IDFA)? ”一项要选择为 YES，否则可能</div><br/>
    <div>会导致审核不通过。</div></div>
    <br/>3.名词解释：<br/>cpid:通信参数由合作方为我方所提供，且为唯一值，参数类型：int
</p>
                </blockquote>
            </div>
            <div id="wmd-preview-section-1461" class="wmd-preview-section preview-content" style="margin-left:30px;">
                <h3 id="一排重接口">一.排重接口示例（由广告主提供）</h3>
                <p>
                    说明：<code>本接口为示例，其作用主要是依据广告主历史设备激活库判断用户能否接任务。广告主可以根据此示例开发相应的排重接口</code> <br>
                    请求地址（示例）： https://ad.mycompany.cn/ad/IdfaRepeat(get请求方法)
                </p>
                <!-- <p>固定参数:</p>
                <ul>
                    <li>adid：1214</li>
                </ul> -->
                <p>动态参数：</p>
                <ul>
                    <li>appid：<code>广告主推广 app 标识（AppstoreID）</code></li>
                    <li>idfa：<code>设备 IDFA</code></li>
                    <!-- <li>os：<code>可选，除非特殊说明（如os=10.2.0）</code></li> -->
                </ul>
                <p>示例如下：</p>
                <blockquote>
                    <p>
                        https://ad.mycompany.cn/ad/IdfaRepeat?idfa=5787DEA8-06B2-4F0E-AD1E-7EB38406ED74&appid=436957087
                    </p>
                </blockquote>
                <p>返回结果：</p>
                <blockquote>
                    <p>
                        {'5787DEA8-06B2-4F0E-AD1E-7EB38406ED74':1} // 1:该 IDFA 没下载过（能做任务） <br>
                        {'5787DEA8-06B2-4F0E-AD1E-7EB38406ED74':0} // 0:该 IDFA 已下载过（不可以做任务）
                    </p>
                </blockquote>
            </div>
            <div id="wmd-preview-section-1397" class="wmd-preview-section preview-content" style="margin-left:30px;">
                <h3 id="二点击接口">二.点击接口示例（由广告主提供）</h3>
                <p>
                    说明：<code>本接口为示例，其作用主要是通知广告主用户已开始做任务。广告主可以根据此示例开发相应的点击接口</code> <br>
                    请求地址（示例）： https://ad.mycompany.cn/ad/click.do
                </p>
                <!-- <p>固定参数:</p>
                <ul>
                    <li>adid：1214</li>
                </ul> -->
                <p>动态参数：</p>
                <ul>
                    <li>appid：<code>广告主推广 app 标识（AppstoreID）</code></li>
                    <li>idfa：<code>设备 IDFA</code></li>
                    <li>ip ：<code>客户端ip 地址</code></li>
                    <li>os ：<code>系统版本号如 11.2</code></li>
                    <li>device ：<code>设备类型 如iphone11,2</code></li>
                     <li>keywords ：<code>任务关键词</code></li>
                    
                    <!-- <li>timestamp：<code>当前时间戳（+8 区）</code></li> -->
                    <!-- <li>sign：<code>请求参数的 MD5 签名</code></li> -->
                    <li>callback：<code>回调接口完整的 url urlencode 过的（回调模式任务必须参数）</code></li>
                </ul>
                <p>示例如下：</p>
                <blockquote>
                    <p>
                         https://ad.mycompany.cn/ad/click.do?appid={$appid}&idfa={$idfa}&ip={$ip}&device={$device}&os={$os}&keywords={$keywords}&callback=urlencode($callback)
                    </p>
                </blockquote>
                <p>返回结果：</p>
                <blockquote>
                    <p>
                       返回 JSON 数据，格式为 { “code” : 0, ”result” : "ok"}当调用成功时，
                      code=0, result 固定为 ok。如果调用失败，code 为其他值，result 为具体的错误信息 
                    </p>
                </blockquote>
               <!--  <p>签名算法：</p>
                <blockquote>
                    <p>
                       将参数列表中的 timestamp 的值与密钥拼接得到的字符串进行 MD5 即得到所需的 sign<br/>
                       <div> 例：timestamp=1453271664；</div><br/>
                        <div>例如密钥：81dc9bdb52d04dc20036dbd8313ed055</div><br/>
                        <div>拼接得字符串：145327166481dc9bdb52d04dc20036dbd8313ed055</div><br/>
                        <div>MD5 以后 sign 为：0cb1f54ffbdd715db96df4be59640283</div><br/>
                    </p>
                </blockquote> -->

                <p>回调接口：</p>
                <blockquote>
                    <p>
                    <div>当用户完成任务（用户联网打开APP试玩三分钟），广告主做回调，直接 urldecode 回调参数，不做修改</div><br/>
                    <div>返回值：</div><br/>
                    <div>返回值皆为 JSON 数据格式为：{ “success” : true, ”message” : "ok"}</div><br/>
                    <div>当调用成功时， success=true,message 固定为 ok。 如果调用失败， success 为其他值，</div><br/>
                    <div>message 为具体的错误信息。</div><br/>
                    </p>
                </blockquote>

                <p>补充说明：</p>
                <blockquote>
                    <p>
                    广告主在接收到请求后,需要把 idfa 以及 callbackurl 存储起来,
                    当检测到用户做完任务以后,取出对应的 callbackurl 进行回调,从而使
                    我们得知相应用户已完成任务
                    </p>
                </blockquote>
            </div>
            <div id="wmd-preview-section-1461" class="wmd-preview-section preview-content" style="margin-left:30px;">
                <h3 id="一排重接口">三.激活上报接口（由广告主提供）</h3>
                <p>
                    说明：<code>本接口为示例，其作用是通知广告主设备已激活已完成任务。广告主可以根据此示例开发相应的上报激活接口</code> <br>
                    请求地址（示例）： https://ad.mycompany.cn/ad/submit(get请求方法)
                </p>
                <!-- <p>固定参数:</p>
                <ul>
                    <li>adid：1214</li>
                </ul> -->
                <p>动态参数：</p>
                <ul>
                    <li>appid：<code>广告主推广 app 标识（AppstoreID）</code></li>
                    <li>idfa：<code>设备 IDFA</code></li>
                    <li>ip：<code>客户端ip</code></li>
                    <!-- <li>os：<code>可选，除非特殊说明（如os=10.2.0）</code></li> -->
                </ul>
                <p>示例如下：</p>
                <blockquote>
                    <p>
                        https://ad.mycompany.cn/ad/submit?idfa=5787DEA8-06B2-4F0E-AD1E-7EB38406ED74&appid=436957087&ip=123.43.56.7
                    </p>
                </blockquote>
                <p>返回结果：</p>
                <blockquote>
                   <p>
                       返回 JSON 数据，格式为 { “code” : 0, ”result” : "ok"}当调用成功时，
                      code=0, result 固定为 ok。如果调用失败，code 为其他值，result 为具体的错误信息 
                    </p>
                </blockquote>
            </div>
            
            <!-- <div id="wmd-preview-section-1397" class="wmd-preview-section preview-content">
                <h3 id="三激活接口">三、激活上报接口</h3>
                <p>
                    请求方式：<code>GET</code> <br>
                    请求地址：http://api.appletaba.com/iosad/active
                </p>
                <p>固定参数:</p>
                <ul>
                    <li>adid：1214</li>
                </ul>
                <p>动态参数：</p>
                <ul>
                    <li>idfa：<code>必填</code></li>
                    <li>ip：<code>必填，用户端IP，请勿使用服务端IP</code></li>
                    <li>os：<code>可选，除非特殊说明（如os=10.2.0）</code></li>
                    <li>device：<code>可选，除非特殊说明（如device=iPhone7,2）</code></li>
                </ul>
                <p>示例如下：</p>
                <blockquote>
                    <p>
                        http://api.appletaba.com/iosad/active?adid=1214&amp;ip=[user_ip]&amp;os=[ios_version]&amp;idfa=5787DEA8-06B2-4F0E-AD1E-7EB38406ED74
                    </p>
                </blockquote>
                <p>返回结果：</p>
                <blockquote>
                    <p>
                        {"code":1,"msg":"成功"} // 1表示激活上报成功 <br>
                        {"code":-1,"msg":"失败"} // -1表示激活上报失败
                    </p>
                </blockquote>
            </div> -->
            
            <div id="wmd-preview-section-671" class="wmd-preview-section preview-content"></div>
            <div id="wmd-preview-section-footnotes" class="preview-content"></div>
        </div>
    </body>
</html>
