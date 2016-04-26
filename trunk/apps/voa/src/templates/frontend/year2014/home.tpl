<!DOCTYPE html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=0">
<title>我的2014，不服来战！</title>
<meta name="description" content="畅移云工作-首个微信企业号第三方应用平台,免费试用微信移动OA应用,点击马上使用！"/>
<link rel="image_src" type="image/jpeg" href="/static/images/year2014/share.png">
</head>
{literal}
<style>
body,h1,h2,h3,p,dl,dd,ul,th,td,form,input,button,textarea {
    margin:0;
    padding:0;
}
h1,h2,h3,h4,h5,h6 {
    font-weight:normal;
}
html,body {
    overflow-y:hidden;
}
body {
    position:absolute;
    top:0;
    left:0;
    right:0;
    bottom:0;
    width:100%;
    height:100%;
    font-size:24px;
    font-family:"微软雅黑","Microsoft Yahei","宋体",'Simsun',Arial,Helvetica,sans-serif;
    text-align:center;
}
html,#part1 {
    display:block;
    padding:0;
    margin:0;
    width:100%;
    height:100%;
}
img {
    width:100%;
    height:auto;
    vertical-align:top;
}
a {
    text-decoration:none;
}
.imgs {
    width:100%;
}
.front_load {
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    z-index:999998;
}
.loading_bg {
    display:block;
    position:absolute;
    left:0;
    top:0;
    width:100%;
    height:100%;
    z-index:999998;
    background-color:#fff;
}
.loading_img {
    position:absolute;
    top:45%;
    z-index:999999;
}
.spinner {
    margin:0 auto;
    width:30px;
    height:30px;
    position:relative;
}
.container1 > div,.container2 > div,.container3 > div {
    width:6px;
    height:6px;
    background-color:#00a7ea;
    border-radius:100%;
    position:absolute;
    -webkit-animation:bouncedelay 1.2s infinite ease-in-out;
    animation:bouncedelay 1.2s infinite ease-in-out;
    -webkit-animation-fill-mode:both;
    animation-fill-mode:both;
}
.spinner .spinner-container {
    position:absolute;
    width:100%;
    height:100%;
}
.container2 {
    -webkit-transform:rotateZ(45deg);
    transform:rotateZ(45deg);
}
.container3 {
    -webkit-transform:rotateZ(90deg);
    transform:rotateZ(90deg);
}
.circle1 {
    top:0;
    left:0;
}
.circle2 {
    top:0;
    right:0;
}
.circle3 {
    right:0;
    bottom:0;
}
.circle4 {
    left:0;
    bottom:0;
}
.container2 .circle1 {
    -webkit-animation-delay:-1.1s;
    animation-delay:-1.1s;
}
.container3 .circle1 {
    -webkit-animation-delay:-1.0s;
    animation-delay:-1.0s;
}
.container1 .circle2 {
    -webkit-animation-delay:-0.9s;
    animation-delay:-0.9s;
}
.container2 .circle2 {
    -webkit-animation-delay:-0.8s;
    animation-delay:-0.8s;
}
.container3 .circle2 {
    -webkit-animation-delay:-0.7s;
    animation-delay:-0.7s;
}
.container1 .circle3 {
    -webkit-animation-delay:-0.6s;
    animation-delay:-0.6s;
}
.container2 .circle3 {
    -webkit-animation-delay:-0.5s;
    animation-delay:-0.5s;
}
.container3 .circle3 {
    -webkit-animation-delay:-0.4s;
    animation-delay:-0.4s;
}
.container1 .circle4 {
    -webkit-animation-delay:-0.3s;
    animation-delay:-0.3s;
}
.container2 .circle4 {
    -webkit-animation-delay:-0.2s;
    animation-delay:-0.2s;
}
.container3 .circle4 {
    -webkit-animation-delay:-0.1s;
    animation-delay:-0.1s;
}
@-webkit-keyframes bouncedelay {
    0%,80%,100% {
    -webkit-transform:scale(0.0)
}
40% {
    -webkit-transform:scale(1.0)
}
}@keyframes bouncedelay {
    0%,80%,100% {
    transform:scale(0.0);
    -webkit-transform:scale(0.0);
}
40% {
    transform:scale(1.0);
    -webkit-transform:scale(1.0);
}
}
#part1 {
    position:relative;
    top:0;
    left:0;
    right:0;
    bottom:0;
    z-index:10;
    width:100%;
    max-width:768px;
    height:100%;
    margin:0 auto;
    -webkit-transition:all .41s;
    -webkit-transition-delay:cubic-bezier(1,.07,.6,.645);
    -moz-transition:all .41s cubic-bezier(1,.07,.6,.645);
    -o-transition:all .41s cubic-bezier(1,.07,.6,.645);
    transition:all .41s cubic-bezier(1,.07,.6,.645)
}
.section {
    display:block;
    position:absolute;
    z-index:19;
    top:100%;
    left:0;
    right:0;
    bottom:0;
    width:100%;
    height:100%;
    overflow:hidden;
    -webkit-transform:translateZ(0);
    transform:translateZ(0)
}
.section_child {
    height:100%;
    width:100%;
    position:absolute;
    top:0;
    left:0;
    bottom:0;
    right:0;
    z-index:999
}
#home {
    top:0;
    background-color:#f9c652;
    background-size:70px 13px
}
#home h1 {
    height:50px;
    line-height:50px;
    font-size:2.231em;
    margin-top:5%;
    color:#333
}
#home h2 {
    margin-top:5%;
    font-size:.923em;
    color:#666
}
#home .view_video {
    margin:5% 0;
    line-height:20px;
    padding:.5em 1.25em;
    border:1px solid #00a7ea;
    background-color:transparent;
    border-radius:26px
}
#home .arrow {
    display:inline-block;
    width:0;
    height:0;
    overflow:hidden;
    font-size:0;
    border:5px solid transparent;
    border-left-style:solid;
    border-left-color:#00a7ea
}
#home .view_video a {
    font-size:.923em;
    color:#00a7ea
}
.mx_num {
    padding-top:15%
}
#home .view_it {
    margin:10% 0;
    position:relative;
    z-index:9997
}
#screen {
    top:100%;
    background-color:#00a4ea;
    width:100%;
}
#screen .p1 {
    font-size:2.6em;
    line-height:1.4em;
    color:#fff;
    margin-top:6%
}
#screen .p2 {
    font-size:1.1em;
    line-height:2em;
    color:#fff
}
#screen .screen_first {
    margin-top:23%;
    color:#fff
}
#screen .screen_first span {
    font-size:4em;
    padding-left:140px
}
.screen_first span:after {
    background-image:url("/static/images/year2014/day-bg.png");
    background-size:112px 39px;
    width:112px;
    height:39px;
    left:50%;
    margin-top:-45px;
    margin-left:-101px;
    content:"";
    position:absolute;
    display:block
}
.screen_middle {
    margin-top:10%;
    color:#fff;
    font-size:1em
}
#screen .screen_last {
    position:absolute;
    bottom:10%;
    width:97%;
    font-size:1.1em
}
#screen .screen_last span {
    font-size:1.6em
}
#function0 {
    top:200%;
    width:100%;
}
#function0 .imgs,#standard .imgs.active {
    -webkit-transition:all 1s;
    -webkit-transform:translate(0%,0)
}
#function0 .imgs.active,#standard .imgs.active {
    -webkit-transform:translate(0,0)
}
.border_info {
    position:absolute;
    top:6%;
    width:100%;
}
.border_info .p1 {
    color:#00a7ea;
    font-size:1.846em;
    line-height:1.4em;
    width:100%
}
.border_info .p2,.border_info .p3 {
    font-size:.923em;
    line-height:2em;
    color:#666;
    margin-top:15px;
    font-size:1.2em;
    color:#00a4ea
}
.border_info .p2 span {
    font-size:2em
}
.border_info .p3 {
    margin-top:1px
}
.border_info .p3 span {
    font-size:1.5em
}
.border_info .p4 {
    margin-top:6%;
    color:#666;
    font-size:1.5em
}
#standard {
    top:300%;
    width:100%;
}
#standard .p2,#standard .p3 {
    color:#9dcd5d
}
#cpu .p2,#cpu .p3 {
    color:#fc0
}
#network .p2,#network .p2,#network .p2,#network .p3 {
    color:#9dcd5d
}
#function0 .imgs,#standard .imgs,#cpu .imgs,#network .imgs,#battery .imgs,#camera .imgs,#flyme .imgs {
    position:absolute;
    bottom:0
}
#cpu {
    top:400%;
    width:100%;
}
#network {
    top:500%;
    width:100%;
}
#battery {
    top:600%;
    background-color:#f98166;
    width:100%;
}
#battery .border_info .p2,#battery .border_info .p3,#battery .border_info .p4 {
    margin-top:10%;
    color:#fff
}
#battery .border_info .p3 {
    font-size:1.0em
}
#battery .border_info .p2 {
    margin-top:0;
    font-size:1.6em
}
#battery .border_info .p4 {
    margin-top:0;
    font-size:1.6em;
    margin-top:2%
}
#battery .border_info .p1 {
    margin-top:10%
}
#camera {
    top:700%;
    background-color:#30b3f5;
    width:100%;
}
#flyme {
    top:800%;
    width:100%;
}
#camera .p3 {
    position: absolute;
    bottom:-8%;
}
#camera .border_info {
    top:3%;
}
.po {
    position:relative;
    height:100%
}
.section .title {
    color:#00a7ea;
    font-size:1.846em;
    margin:10% 0 3% 0
}
.section .info {
    font-size:.923em;
    line-height:2em;
    color:#666
}
.fullpage_option {
    position:fixed;
    bottom:7px;
    left:0;
    height:25px;
    width:100%;
    z-index:99
}
.next_screen {
    display:none;
    position:absolute;
    z-index:20;
    top:0;
    width:100%;
    -webkit-transition:all 1s
}
.next_screen.on {
    display:block;
    -webkit-animation:goNext 1.5s infinite
}
@-webkit-keyframes goNext {
    0% {
    -webkit-transform:translate(0,0) translateZ(0);
    opacity:1
}
100% {
    -webkit-transform:translate(0,-20px) translateZ(0);
    opacity:.1
}
}.next_screen img {
    width:22px
}
.mx4_music {
    position:absolute;
    z-index:20;
    bottom:0;
    right:3%;
    width:42px;
    height:42px
}
.mx4_music img {
    width:21px;
    padding-top:8px
}
.pop_bg {
    display:none;
    position:absolute;
    left:0;
    top:-30px;
    background-color:rgba(0,0,0,0.7);
    -webkit-transition:all .5s;
    width:100%;
    height:130%;
    z-index:9998
}
.pop_share {
    display:none;
    position:absolute;
    left:0;
    top:0;
    width:100%;
    z-index:9998
}
.pop_act {
    display:block
}
.pop_share .pop_close {
    -webkit-transform:translate(0%,0%)
}
.pop_close {
    background-image:url("/static/images/year2014/weixin_share.png");
    background-size:226px 153px;
    width:226px;
    height:153px;
    right:10px;
    margin-top:0;
    margin-right:0;
    display:block;
    position:absolute;
    top:10px;
    -webkit-transition:all .6s;
    -webkit-transform:translate(100%,-100%)
}
.pop_mx4 {
    width:100%;
    margin-top:40%;
    height:350px;
    background-color:#000
}
@media only screen and (max-width:320px) and (min-width:310px) {
    body {
    font-size:13px;
    width:320px!important;
    margin:0;
    padding:0
}
#fullscreen {
    width:320px
}
.pop_mx4 {
    height:300px
}
.section {
    margin:0;
    padding:0
}
#camera .lens_par {
    bottom:-9em
}
#camera .mx4_lens_1 {
    -webkit-transform:scale(.8) translate(-103px,-158px) translateZ(0)
}
}@media only screen and (max-height:480px) and (min-height:100px) {
    .pop_mx4 {
    margin-top:30%
}
.pop_mx4 {
    height:250px
}
#home .view_it {
    margin:5% 0
}
#casualty .title {
    margin:3% 0 1% 0
}
}@media only screen and (max-width:360px) and (min-width:321px) {
    body {
    font-size:15px
}
}@media only screen and (max-width:385px) and (min-width:361px) {
    body {
    font-size:15px
}
#home .view_it {
    margin:8% 0
}
.pop_mx4 {
    height:300px
}
#camera .lens_par {
    bottom:-10em
}
}@media only screen and (max-width:431px) and (min-width:386px) {
    body {
    font-size:14px
}
#camera .lens_par {
    bottom:-10.5em
}
.mx_num {
    padding-top:10%
}
.pop_mx4 {
    height:250px
}
#home .view_it {
    margin:8% 0
}
}@media only screen and (max-width:480px) and (min-width:431px) {
    body {
    font-size:18px
}
#network .title {
    height:49px
}
#screen .mx4_imgs {
    width:88%
}
.mx_num {
    padding-top:15%
}
#home .view_it {
    margin:8% 0
}
}@media only screen and (max-width:560px) and (min-width:480px) {
    }
@media only screen and (max-width:570px) and (min-width:560px) {
    }
@media only screen and (max-width:640px) and (min-width:570px) {
    }
@media only screen and (max-width:710px) and (min-width:640px) {
    #screen .p1 {
    font-size:2em
}
}@media screen and (min-width:765px) {
    .mx4_imgs {
    width:66%;
    position:absolute;
    bottom:0
}
#battery .po {
    width:45%
}
#camera .lens_par {
    bottom:-11em
}
#casualty .mx4_imgs {
    width:100%;
    margin:0 auto
}
#flyme .view_it {
    margin-top:10%
}
#flyme .flyme_img {
    margin-top:10%
}
#camera .lens_par {
    bottom:-11.4em
}
#screen .p1 {
    font-size:2em
}
}
</style>
{/literal}
<script src="/static/mobile/lib/jquery/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
var urls = {$urls};
var static_url = '{$static_url}';
$(function(){
    $.getJSON(urls.summary, function (json){
        $('.summary .days').text(json.result.days);
        $('.summary .endtime').text(json.result.endtime);
        $('.summary .jointime').text(json.result.jointime);
        $('.summary .membercount').text(json.result.membercount);
        $('.summary .regtime').text(json.result.regtime);
    });
    $.getJSON(urls.sign, function (json){
        $('.sign .days').text(json.result.days);
        $('.sign .rate').text(json.result.rate);
    });
    $.getJSON(urls.dailyreport, function (json){
        $('.dailyreport .count').text(json.result.count);
        $('.dailyreport .ranknum').text(json.result.ranknum);
    });
    $.getJSON(urls.project, function (json){
        $('.project .total').text(json.result.total);
        $('.project .complete').text(json.result.complete);
        $('.project .complete_rate').text(json.result.complete_rate);
    });
    $.getJSON(urls.meeting, function (json){
        $('.meeting .count').text(json.result.count);
        $('.meeting .daily').text(json.result.daily);
    });
});
</script>
<body>
    
    <div class="front_load">
        <div class="loading loading_bg"></div>
        <div class="imgs loading loading_img">
            <div class="spinner">
                <div class="spinner-container container1">
                    <div class="circle1"></div>
                    <div class="circle2"></div>
                    <div class="circle3"></div>
                    <div class="circle4"></div>
                </div>
                <div class="spinner-container container2">
                    <div class="circle1"></div>
                    <div class="circle2"></div>
                    <div class="circle3"></div>
                    <div class="circle4"></div>
                </div>
                <div class="spinner-container container3">
                    <div class="circle1"></div>
                    <div class="circle2"></div>
                    <div class="circle3"></div>
                    <div class="circle4"></div>
                </div>
            </div>
        </div>
    </div>
    

   
    <div id="part1" class="main mbig">
        <div id="home" class="section" style="top:0%;">
            <div class="imgs mx_num"><img src="/static/images/year2014/1-1.png" /></div>
            <div class="imgs view_it"><img src="/static/images/year2014/10-2.png" /></div> 
            <div class="imgs mx_home"><img src="/static/images/year2014/1.png" /></div>            
            <div class="section_child"></div>
        </div>

        <div id="screen" class="section summary">
            <p class="p1">回顾2014年</p>
            
            <p class="p2 screen_first"><span class="days"></span>天</p>
             <p class="p2 screen_middle"><span class="icon1"></span><span class="regtime"></span>  公司注册了畅移<br>
                <span class="icon1"></span><span class="jointime"></span>  我第一次使用畅移
            </p>
            <p class="p2 screen_last">截止至<span class="endtime"></span><br>
                <span class="icon1"></span>有<span class="membercount"></span>个同事和我一起用畅移办公
            </p>           
            <div class="section_child"></div>
        </div>

        <div id="function0" class="section sign">
            <div class="imgs"><img src="/static/images/year2014/2.png" /></div>
            <div class="border_info">
                <div class="p1"><img src="/static/images/year2014/1-2.png"></div>               
                <p class="p2">今年，我准时签到<span class="days"></span>天</p>
                <p class="p3">击败了<span><span class="rate"></span>%</span>的同事</p>
                <p class="p4">明年我会更努力哒!</p>
            </div>
            <div class="section_child"></div>
        </div>

        <div id="standard" class="section project">
            <div class="imgs"><img src="/static/images/year2014/3.png" /></div>
            <div class="border_info">
                <p class="p1"><img src="/static/images/year2014/1-3.png"></p>               
                <p class="p2">今年，我一共收到<span class="total"></span>条任务</p>
                <p class="p3">完成了<span class="complete"></span>条，完成率高达<span><span class="complete_rate"></span>%</span></p>
                <p class="p4">请叫我“任务小能手”!</p>
            </div>
            <div class="section_child"></div>
        </div>

        <div id="cpu" class="section dailyreport">
            <div class="imgs"><img src="/static/images/year2014/4.png" /></div>
            <div class="border_info">
                <div class="p1"><img src="/static/images/year2014/1-4.png"></div>               
                <p class="p2">今年，我总共发送了<span class="count"></span>条日报</p>
                <p class="p3">在全公司排行第<span class="ranknum"></span></p>
                <p class="p4">每天都有新的收获呢!</p>
            </div>
            <div class="section_child"></div>
        </div>

        <div id="network" class="section meeting">
            <div class="imgs"><img src="/static/images/year2014/5.png" /></div>
            <div class="border_info">
                <div class="p1"><img src="/static/images/year2014/1-5.png"></div>               
                <p class="p2">今年，我一共参与了<span class="count"></span>场会议</p>
                <p class="p3">平均<span class="daily"></span>天就有一场</p>
                <p class="p4">嗨，公司没我还真不行！</p>
            </div>
            <div class="section_child"></div>
        </div>

        <div id="battery" class="section">
            
            <div class="border_info">                              
                <p class="p2">2015年，我有什么期待？</p>
                <div class="p1"><img src="/static/images/year2014/6.png"></div> 
                <p class="p3">无论如何，未来，千千万万个日日夜夜，</p>
                <p class="p4">责任，畅移与你共同承担；<br>
喜悦，畅移与你一起分享。</p>
            </div>
            <div class="section_child"></div>
        </div>
        
        
        <div id="camera" class="section">           
            <div class="border_info">
                <div class="p1"><img src="/static/images/year2014/7.png"></div>               
                <p class="p2"><img src="/static/images/year2014/8.png"></p>
                
                            
            </div><p class="p3 mx4_imgs view_it" style="z-index:9996;"><img src="/static/images/year2014/9.png"></p> 
                <div class="pop_bg"></div>
                <div class="pop_share">
                    <a href="javascript:;" class="pop_close"></a>               
                </div>
             <div class="section_child">  </div>

        </div>
      
    </div>

    <div class="fullpage_option">
        <div class="next_screen on"><img src="/static/images/year2014/arrow.png" /></div>
        <div class="mx4_music"><img class="music_active" src="/static/images/year2014/music.png" /></div>
        <div style="display:none;"><audio src="/static/images/year2014/piano.mp3"></audio></div>
    </div>

</body>
{literal}
<script type="text/javascript">
function part1(callback) {
    this.touchStartY = 0;
    this.callback = callback;
    this.win_H = $(window).height();
    this.now_H = 0;
    this.isscrolling = true;
    this.now_screen = 1
}
part1.prototype.initialize = function() {
    this.isTouchDevice = navigator.userAgent.match(/(iPhone|iPod|iPad|Android|BlackBerry|Windows Phone)/);
    if (this.isTouchDevice) {
        this.addTouchHandler()
    } else {
        this.addMouseWheelHandler()
    }
};
part1.prototype.addTouchHandler = function() {
    var self = this;
    $(".section_child").on("touchstart",
    function() {
        self.touchStartHandler(arguments)
    });
    $(".section_child").on("touchmove",
    function() {
        self.touchMoveHandler(arguments)
    })
};
part1.prototype.addMouseWheelHandler = function() {
    var self = this;
    if (document.addEventListener) {
        document.addEventListener("mousewheel",
        function() {
            self.mouseWheelHandler(arguments)
        },
        false);
        document.addEventListener("wheel",
        function() {
            self.mouseWheelHandler(arguments)
        },
        false)
    } else {
        document.attachEvent("onmousewheel",
        function() {
            self.mouseWheelHandler(arguments)
        })
    }
};
part1.prototype.getEventsPage = function(e) {
    var events = new Array();
    if (window.navigator.msPointerEnabled) {
        events["y"] = e.pageY
    } else {
        events["y"] = e.touches[0].pageY
    }
    return events
};
part1.prototype.touchStartHandler = function(event) {
    var e = event[0].originalEvent;
    var touchEvents = this.getEventsPage(e);
    this.touchStartY = touchEvents["y"];
    touchEvents = null;
    e.preventDefault()
};
part1.prototype.touchMoveHandler = function(event) {
    var e = event[0].originalEvent;
    var touchEvents = this.getEventsPage(e);
    touchEndY = touchEvents["y"];
    var sy_len = document.body.clientHeight / 100 * 1;
    var ios_screen = navigator.userAgent.match(/(iPhone|iPod|iPad)/);
    if (ios_screen) {
        sy_len = document.body.clientHeight / 100 * 5
    }
    if (Math.abs(this.touchStartY - touchEndY) > sy_len) {
        if (this.touchStartY > touchEndY) {
            var len = $("#part1 .section").length;
            if (this.now_screen == len) {
                return false
            }
            this.changeScreen("down")
        } else {
            if (this.now_screen == 1) {
                return false
            }
            this.changeScreen("up")
        }
        this.touchStartY = touchEndY
    }
    e.preventDefault()
};
part1.prototype.mouseWheelHandler = function(event) {
    event = event[0];
    var delta = 0;
    if (!event) {
        event = window.event
    }
    if (event.wheelDelta) {
        delta = event.wheelDelta / 120;
        if (window.opera) {
            delta = -delta
        }
    } else {
        if (event.detail) {
            delta = -event.detail / 3
        }
    }
    if (delta) {
        if (delta < 0) {
            var len = $("#part1 .section").length;
            if (this.now_screen == len) {
                return false
            }
            this.changeScreen("down")
        } else {
            if (this.now_screen == 1) {
                return false
            }
            this.changeScreen("up")
        }
    }
};
part1.prototype.changeScreen = function(type) {
    if (this.isscrolling) {
        this.now_H += type == "down" ? -$(window).height() : $(window).height();
        this.now_screen += type == "down" ? 1 : -1;
        if (this.isTouchDevice) {
            var ios_screen = navigator.userAgent.match(/(iPhone|iPod|iPad)/);
            if (ios_screen) {
                $("#part1").css({
                    "-webkit-transition": "all 0.3s linear",
                    "-webkit-transform": "matrix(1, 0, 0, 1, 0, " + this.now_H + ")"
                })
            } else {
                $("#part1").css({
                    "-webkit-transform": "translate3d(0px, " + this.now_H + "px, 0px)",
                    "transform": "translate3d(0px, " + this.now_H + "px, 0px)"
                })
            }
        } else {
            $("#part1").animate({
                top: this.now_H + "px"
            },
            "normal")
        }
        var self = this;
        var t = setTimeout(function() {
            self.isscrolling = true
        },
        300);
        this.callback(this.now_screen)
    }
    this.isscrolling = false
};  
</script>

<script type="text/javascript">
window.onload = function() {
    $(".front_load").hide()
};
~function() {
    var iw = 0;
    var first_play = true;
    var fs = new part1(function(screen) {
        if (first_play) {
            var audio = document.getElementsByTagName("audio")[0];
            audio.addEventListener("ended",
            function() {
                this.currentTime = 0;
                $(".mx4_music img").removeClass("music_active");
                $(".mx4_music img").attr("src", "/static/images/year2014/music-stop.png")
            },
            false);
            document.getElementsByTagName("audio")[0].play();
            first_play = false
        }
        switch (screen) {
        case 3:
            setTimeout(function() {
                $(".imgs").addClass("active")
            },
            500);
            break;
        case 6:
            /*setTimeout(function() {
                document.getElementById("ns_1").style.width = "90%";
                document.getElementById("ns_2").style.width = "15%"
            },
            500);*/
            break;
        case 8:
            setTimeout(function() {
                $(".lens_par div").removeClass("lens_off")
            },
            500);
            $(".next_screen").removeClass("on");
            break;
        case 11:
            $(".next_screen").removeClass("on");
            break;
        default:
            $(".next_screen").addClass("on")
        }
    });
    fs.initialize();
    var user_is_close_audio = false;
    $(".mx4_music").on("touchstart",
    function() {
        if ($(".pop_act").length != 0) {
            return false
        }
        var that = $(this).children("img");
        if (that.hasClass("music_active")) {
            $("audio")[0].pause();
            that.attr("src", "/static/images/year2014/music-stop.png");
            that.removeClass("music_active");
            user_is_close_audio = true
        } else {
            that.addClass("music_active");
            $("audio")[0].play();
            that.attr("src", "/static/images/year2014/music.png");
            user_is_close_audio = false
        }
    });
    $(".view_it").click(function() {
        var that = $(".mx4_music img");
        $(this).siblings(".pop_share,.pop_bg").addClass("pop_act");
        var video = '';
        if ($(this).parent().attr("id") == "flyme") {
            video = ''
        }
        $(this).siblings(".pop_share,.pop_bg").find(".pop_mx4").html(video);
        $("audio")[0].pause();
        that.attr("src", "/static/images/year2014/music-stop.png")
    });
    $(".pop_bg").on("click",
    function() {
        var that = $(".mx4_music img");
        $(this).parents(".section").find(".pop_share,.pop_bg").removeClass("pop_act");
        if (!user_is_close_audio) {
            $("audio")[0].play();
            that.attr("src", "/static/images/year2014/music.png")
        }
        $(this).parents(".section").find(".pop_share,.pop_bg").find(".pop_mx4").html("")
    });
    
    var imgUrl = static_url+"images/year2014/share.png";
    var lineLink = urls.home_page;
    var descContent = "口说无凭，数据说话！这是我的2014，你敢进来看么？";
    var shareTitle = "我的2014，不服来战！";
    var appid = "wx89d721db047ee518";
    function shareFriend() {
        WeixinJSBridge.invoke("sendAppMessage", {
            "appid": appid,
            "img_url": imgUrl,
            "img_width": "200",
            "img_height": "200",
            "link": lineLink,
            "desc": descContent,
            "title": shareTitle
        },
        function(res) {})
    }
    function shareTimeline() {
        WeixinJSBridge.invoke("shareTimeline", {
            "img_url": imgUrl,
            "img_width": "200",
            "img_height": "200",
            "link": lineLink,
            "desc": descContent,
            "title": shareTitle
        },
        function(res) {})
    }
    function shareWeibo() {
        WeixinJSBridge.invoke("shareWeibo", {
            "content": descContent,
            "url": lineLink
        },
        function(res) {})
    }
    document.addEventListener("WeixinJSBridgeReady",
    function onBridgeReady() {
        WeixinJSBridge.on("menu:share:appmessage",
        function(argv) {
            shareFriend()
        });
        WeixinJSBridge.on("menu:share:timeline",
        function(argv) {
            shareTimeline()
        });
        WeixinJSBridge.on("menu:share:weibo",
        function(argv) {
            shareWeibo()
        })
    },
    false)
} ();
</script>
{/literal}
</html>
