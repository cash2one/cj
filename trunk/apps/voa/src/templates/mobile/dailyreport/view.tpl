{include file='mobile/header.tpl'}
<div class="ui-top-box">
	<div class="ui-top-left">
		<h2>{$dailyreport['m_username']}的{$dailyType[$dailyreport['dr_type']][0]}</h2>
		<p>{$dailyreport['_created_u']} 发出</p>
	</div>
	<div class="ui-top-right">
		<div class="ui-top-date">{$dshow[1]}</div>
		<div class="ui-top-day">{$dshow[2]}</div>
		<div class="ui-top-week">{$dshow[3]}</div>
	</div>
</div>

<div class="ui-form">
	<div class="ui-form-item ui-form-item-show ui-conten-more">
		{$report_type}时间<i style="margin-left: 10px;">{$report_time}</i>
	</div>
	<div class="ui-form-item ui-form-item-show ui-conten-more ui-border-b">
		<label>报告内容</label>
	</div>
	<div class="ui-form-item ui-form-item-show ui-conten-more ui-border-b">
		{foreach $dailyreport['_message_li'] as $msg}
		<div class="daily-content">
			<p>{$msg}</p>
		</div>
		{/foreach}
	</div>
	<!-- 上传图片 -->
	{cyoa_view_image
		attachs=$attachs
		bigsize = 0
	}
	<!-- 接收人 -->
	{cyoa_user_show
		users=$tousers
		title='接收人'
		styleid=2
	}
</div>

<div class="ui-btn-group-tiled ui-btn-wrap">
	<button class="ui-btn-lg" id="history"  {if $is_recv} onclick="location.href='/dailyreport/so/recv/?sotext={$dailyreport['m_username']}'" {else}onclick="location.href='/dailyreport/so/?sotext={$dailyreport['m_username']}'"{/if}>查看往期</button>
	<button class="ui-btn-lg ui-btn-primary" id="forward">转发报告</button>
</div>

<form name="forward_post" id="forward_post" method="post" action="/frontend/dailyreport/forward?handlekey=post">
	<input type="hidden" name="dr_id" value="{$dailyreport['dr_id']}" />
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div id="forward_div" hidden="true">
		<div class="ui-form" >
 <!--报告内容 -->
			{cyoa_textarea
				title='备注'
				attr_placeholder='可填写备注'
				attr_id='message'
				attr_name='message'
				attr_maxlength=100
				attr_value={$message}
			}
<!-- 转发人 -->
			{cyoa_user_selector
				title='转发人'
				user_input=carboncopyuids
			}
		</div>
		<div class="ui-btn-group-tiled ui-btn-wrap">
			<button class="ui-btn-lg" id="forward_cancel">取消</button>
			<button class="ui-btn-lg ui-btn-primary" id="fpost">提交</button>
		</div>
	</div>
</form>

<form name="frmpost" id="frmpost" method="post" action="/dailyreport/reply/{$dr_id}?handlekey=post">
<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="ui-txt-muted">评论 ({$postsize})</div>
	<div class="ui-comment-box">
		<div class="ui-textarea-comment">
			<textarea placeholder="评论..." name="message" id="dis_message"></textarea>
		</div>
		<div class="ui-textarea-btn">
			<button class="ui-btn ui-btn-primary ui-btn-sent" id="reply">发布</button>
		</div>
	</div>
</form>

<ul class="ui-list ui-border-no">
{foreach $posts as $p}
	<li class="ui-border-t">
		<div class="ui-avatar-s"><img src="{$cinstance->avatar($p['m_uid'])}" /></div>
		<div class="ui-list-info">
			<h4>{$p['m_username']}</h4>
			<p class='bq'>{$p['_message']}</p>
		</div>
		<div class="ui-list-right">
			<h4 class="ui-nowrap">{$p['_created_u']}</h4>
		</div>
	</li>
{/foreach}
</ul>

<script type="text/javascript">
{literal}
function errorhandle_post(url, msg) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg) {
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}

function show_dialog(content){
	var dia=$.dialog({
		title:'温馨提示',
		content:content,
		button:["确认","取消"]
	});

	dia.on("dialog:action",function(e){
		//console.log(e.index)
	});
	dia.on("dialog:hide",function(e){
		//console.log("dialog hide")
	});
}
require(["zepto", "underscore", "submit", "frozen", 'expression'], function($, _, submit, fz, expression) {
	$(function(){
		//var exp = new expression();//评论提交
		$('.bq').expression();
	});
	var sbt = new submit();//评论提交
	sbt.init({"form": $("#frmpost")});

 	var forward_sbt = new submit();//转发报告提交
	forward_sbt.init({"form": $("#forward_post")});

});


require(["zepto", "frozen"], function($, fz) {
	$(document).ready(function() {

		//查看往期
		$("#history").on('click',function(e){
		});

		//转发报告
		$("#forward").on('click',function(e){
			var forward_div  = $("#forward_div");
			if($("#forward").hasClass("ui-btn-primary")){
				$("#forward").removeClass("ui-btn-primary");
				$("#forward").addClass("disabled clearfix");
				$("#history").addClass("disabled clearfix");
				forward_div.removeAttr('hidden');//显示转发（内容、转发人）
			}
			$("#reply").addClass('disabled');
			$("#history").attr('disabled','disabled');

		});

		//取消转发
		$("#forward_cancel").on('click',function(e){
			var forward_div  = $("#forward_div");
			forward_div.attr('hidden','true');
			$("#forward").addClass("ui-btn-primary");
			$("#forward").removeClass("disabled clearfix");
			$("#history").removeClass("disabled clearfix");
			$("#reply").removeClass("disabled");
			$("#history").removeAttr('disabled');
			return false;
		});

		//提交转发报告
		$('#fpost').on('click', function(e) {
			var uids = $.trim($('#carboncopyuids').val());
			if(uids.length == 0){
				show_dialog('请选择转发人！');
				return false;
			}
		});
		//评论
		$('#reply').on('click', function(e) {
			if($("#reply").hasClass("disabled")){
				return false;
			}else{
				var contentTa =$.trim($('#dis_message').val());
				if(contentTa.length == 0){
					show_dialog('请填写评论内容！');
					return false
				}
			}
		});


	});
});

{/literal}
</script>

{include file='mobile/footer.tpl'}