{include file='frontend/header.tpl'}
{literal}
<style type="text/css">
html {
	color:#666;
	background:#fff;
	overflow-y:scroll;
}

body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,textarea,p,blockquote,th,td,hr,button,article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section {
    margin:0;
    padding:0;
}

body,button,input,select,textarea {
    font:24px arial,Microsoft Yahei,sans-serif;
}
input,select,textarea {
    font-size:100%;
}
textarea {
	resize:none;
}
ins,a {text-decoration:none;}
.fl 		{ float:left; }
.fr 		{ float:right; }
.page 	{ width:600px; margin:0 auto; background:#f7f7f7; padding-bottom:100px; }
.pageHead 	{ height:254px; }
.titleH2 	{ font-size:46px; color:#333; padding-top:20px; font-weight:600; text-align:center; line-height:1.5; }
.pageBar 	{ margin:20px auto 0; width:440px; height:58px; }
.pageBar span 	{ display:block; width:207px; height:58px; background:url(/misc/images/bg_pagebar1.png) 0 0 no-repeat; font-size:24px; color:#fff; text-align:center; line-height:58px; }
.pageBar span.gray 	{ background:url(/misc/images/bg_pagebar1.png) 0 0 no-repeat; }
.pageBar span.blue 	{ background:url(/misc/images/bg_pagebar2.png) 0 0 no-repeat; }
.pageBar span.blueap { background:url(/misc/images/bg_pagebar3.png) 0 0 no-repeat; } 

.loginBox 	{  }
.loginBox p	{ background:#fff; height:90px; padding:10px 20px; border:1px solid #adadad; border-left:0; border-right:0; margin-bottom:20px; }
.loginBox p img { vertical-align:middle; }
.loginBox p input 	{ border:0; height:70px; line-height:70px; padding:10px; width:80%; }
.btnBox 	{ margin-top:50px; }
.btnBox a 	{ display:block; border-radius:5px; background:#0099ff; color:#fff; font-size:40px; text-align:center; line-height:90px; height:90px; }

.pageFoot 	{  bottom:0; }
.pageFoot h5 	{ font-size:28px; color:#666; text-align:center; }
.pageFoot h5 a 	{ color:#0099ff; }
.pageFoot p 	{ text-align:center; margin-top:20px; font-size:24px; color:#999; }
.Validform_checktip{
	margin-left:8px;
	line-height:20px;
	height:20px;
	overflow:hidden;
	color:#999;
	font-size:12px;
}
.Validform_right{
	color:#71b83d;
	padding-left:20px;
}
.Validform_wrong{
	color:red;
	padding-left:20px;
	white-space:nowrap;
}
.Validform_loading{
	padding-left:20px;
}
.Validform_error{
	background-color:#ffe7e7;
}
</style>
{/literal}
<body>
<script src="{$wbs_javascript_path}/MOA.memberselect.js"></script>
<script src="{$wbs_javascript_path}/jquery-1.9.1.min.js"></script>
<script src="{$wbs_javascript_path}/Validform_v5.3.2_min.js"></script>
<div class="page">
	<div class="pageHead">
		<h2 class="titleH2">体验畅移销售通</h2>
		<div class="pageBar">
			<a href=""><span class="fl blue">1、填写资料</span></a>
			<a href=""><span class="fr gray">2、扫码关注</span></a>
		</div>
	</div>
	<div class="loginBox">
		<form method="post" action="/frontend/experience/home" id="myform" name="myform" class="myform">
			<p>
				<label for="textfield"><img src="/misc/images/icn_name.png"></label>
				<input type="text" value="" name="account" class="inputxt" datatype="z2-4|s4-16|n4-16" nullmsg="请填写姓名！" errormsg="姓名中不可包含标点符号！" placeholder="请输入姓名" />
				<span class="Validform_checktip" ></span>
			</p>
			<p>
				<label for="textfield2"><img src="/misc/images/icn_phone.png"></label>
				<input type="text" name="phone" class="inputxt" datatype="m" nullmsg="请填写手机号" errormsg="手机号格式不正确！" placeholder="请输入手机号" />
				<span class="Validform_checktip"></span>
			</p>
			<div class="btnBox"><a href="/frontend/experience/attention">下一步</a></div><span id="msg" style="margin-left:30px;"></span>
		</form>
	</div>
	<div class="pageFoot">
		<h5 class="titleH2">如有问题，请拨打<a href="tel:4008606961">400-860-6961</a>免费客服电话</h5>
	</div>
</div>
{literal}
<script>

$(function() {
	$(".myform").Validform({
		tiptype:function(msg, o, cssctl) {
			if (o.obj.is("form")) {
				var objtip = o.obj.find("#msg");
				cssctl(objtip, o.type);
			} else {
				var objtip = o.obj.siblings(".Validform_checktip");
				cssctl(objtip, o.type);
				objtip.text(msg);
			}
		},
		ajaxPost:true,
		callback:function(data) {
			if (data.status == 'y') {
				return true;
			} else {
				//Validform_wrong
				alert(data.info);
			}

            return false;
        },
        showAllError:true,
        datatype:{
			"z2-4" : /^[\u4E00-\u9FA5\uf900-\ufa2d]{2,4}$/
		},
	});
});
</script>
{/literal}

{include file='frontend/footer.tpl'}
