{include file='mobile/header.tpl'}

<form id="content" method="post" action="/api/invite/post/insert">
	<input type="hidden" name="formhash" value="{$formhash}" /><br />
	<div style="text-align: left;color:#fe6b1b;position: relative;left: 16px;">
		<span>*身份验证信息</span>
	</div>
	<div class="ui-form">
		<input type="hidden" name="invite_uid" value="{$invite_uid}" />
		{cyoa_input_text attr_type='text'
			div_class="ui-form-item"
			attr_placeholder='请填写姓名' title='姓名' attr_name='name' attr_id='id-input'
			attr_class="invite-name" attr_maxlength="10" attr_required="required" attr_value=""
		}
		{cyoa_input_text attr_type='text'
		attr_placeholder='请输入手机号' title='手机号' attr_name='phone' attr_id='phone'
		attr_class="invite-phone" attr_maxlength="11" attr_value="" attr_required=$requiremobile
		}
	</div>
	<div style="text-align: left;color:#fe6b1b;position: relative;left: 16px;">
		<span>其他信息</span>
	</div>
	<div class="ui-form">
		<!-- <div class="ui-tips ui-tips-info">
		    <i></i><span>*身份验证信息(以下三种信息不可同时为空)</span>
		</div> -->
		{cyoa_select
		title='性别'
		attr_name='gender'
		attr_options=$sex
		attr_value=1
		div_class='ui-form-item ui-border-t ui-form-item-link'
		}
		{cyoa_input_text attr_type='text'
			div_class="ui-form-item"
			attr_placeholder='请输入邮箱地址' title='邮箱' attr_name='email' attr_id='email'
			attr_class="invite-email" attr_maxlength="100" attr_value=""
		}
		{cyoa_input_text attr_type='text'
			attr_placeholder='请输入微信号' title='微信号' attr_name='weixin_id' attr_id='weixin_id'
			attr_class="invite-weixin" attr_maxlength="45" attr_value=""
		}


	</div>

	<!--自定义字段部分-->
	<div class="ui-form">
		{cyoa_input_text attr_type='text'
			div_class="ui-form-item"
			attr_placeholder='请输入职位' title='职位' attr_name='position' attr_id='position'
			attr_class="invite-position" attr_maxlength="30" attr_value=""
		}
		{if null != $custom}
			{foreach $custom as $k => $v}
				{$name = 'custom['|cat:$k|cat:"][]"}
				{if $v['required'] == '0'}
					{$_required = null}
				{else}
					{$_required = 1}
				{/if}
				{if $v['required'] == '1'}
				{cyoa_input_text
				attr_required=$_required
				attr_placeholder='请输入（此处为必填)'
				title=$v['desc']
				attr_id=$k
				attr_name=$name
				}
				<input type="hidden" name="{$name}" value="{$v['desc']}" />
				{else}
				{cyoa_input_text
				attr_placeholder='请输入'
				title=$v['desc']
				attr_id=$k
				attr_name=$name
				}
				<input type="hidden" name="{$name}" value="{$v['desc']}" />
				{/if}
			{/foreach}
		{/if}
	</div>

	<div class="ui-btn-wrap">
		<button type="submit" class="ui-btn-lg ui-btn-primary" id="send">提交</button>

	</div>

</form>
<!--对话框啊-->
	<div class="ui-dialog">
	    <div class="ui-dialog-cnt" style="width:290px;height: 207px;">
	        <div class="ui-dialog-bd">
	            <div>
	            <div class="head">
	            	<img src="../../../include/../../../misc/images/wx_success.png" style="position:relative;left:63px;top:2px;" width="24" height="24" /><span style="font-size:16px;position: absolute;top:22px;left:111px;font-weight:bold;color:#333333;">信息提交成功!</span>
	       		</div>
		            <div style="margin-top:11px;margin-left:27px;color:#777777;font-size:14px;width:88%;">温馨提示：<br />
						您的信息已经提交并通知管理员，请耐心等候管理员审批吧!
						<hr style="position: relative;left: -19px;top: 8px;width: 106%;" />
		            </div>
	            </div>
	        </div>
	        <div class="" style="text-align:center;">
	            <!-- <button type="button" data-role="button"  class="select" id="dialogButton<%=i%>">关闭</button>  -->
	            <button id="w-close" class="ui-btn" style="background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0.5, #00a5ff), to(#00a5ff));color:#fff;width:70%;height:34px;border-radius: 6px;">确定</button>
	        </div>
	    </div>
	</div>

<script type="text/javascript">
	require(["zepto", "underscore", "submit", "frozen", "jweixin"], function($, _, submit, fz, wx) {
        {cyoa_jsapi list=['closeWindow'] debug=0}
{literal}
		$('#send').on('click',function(){
			var require = $("input[required='1']");

			var flag = true;
			require.each(function(i,e){
				if($(this).val() == ''){
					$.tips({content: '必填内容不可为空！'});
					flag = false;
					return false;
				}
			});
			return flag;
		});
		var sbt = new submit();
        sbt.init({"form": $("#content")}, {
        	'success' : function(o) {
        		if(o.result.success == 1){
        			$(".ui-dialog").dialog("show");
			        $.post('/api/common/post/sendmsg');
        		}else{
        			$.post('/api/common/post/sendmsg');
        			window.location.href = o.result.url;
        		}
        	}
        });


        // 绑定啊------
        var wx_loaded = false;
        // 微信接口加载完毕
        wx.ready(function () {
        	// 微信接口验证完毕
        	wx_loaded = true;
        });
        // 绑定点击关闭按钮动作
        $('#w-close').click(function () {
        	// loading
        	var __loading = $.loading({
        		"content": "请稍候……"
        	});
        	// 循环检查微信接口是否加载
        	var it = setInterval(function () {
        		// 如果已加载微信接口
        		if (wx_loaded) {
        			// 隐藏loading
        			__loading.loading("hide");
        			// 关闭窗口
        			wx.closeWindow();
        			// 停止定时器
        			clearInterval(it);
        			return false;
        		}
        	}, 501);
        });

	});
</script>
{/literal}





{include file='mobile/footer.tpl'}