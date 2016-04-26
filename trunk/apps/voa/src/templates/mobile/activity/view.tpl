{include file='mobile/header.tpl' css_file='app_activity.css'}
<div style="display: none">
{* 默认的分享图标 *}
<img src="{$share_data['imgUrl']}" alt="" />
</div>

{*先判断是不是允许外部人参与的活动*}
{if $data['not-allow-out-people'] == '0'}

	<div class="ui-top-box hd-ui-top-box">
    <h2 id="datatitle" style="margin:0 auto; font-size:24px; padding: 10px 36px 0 36px;">{$data['title']}</h2>
    <p style="margin-top:-10px;">{$data['uname']} &nbsp;&nbsp;{$data['time']}</p>
{if $data['ctype'][1] == 1}
<div class="hd-ui-sign hd-ui-sign-start">已<br />开<br />始</div>
{/if}
{if $data['ctype'][1] == 2}
<div class="hd-ui-sign hd-ui-sign-not-start">未<br />开<br />始</div>
{/if}
{if $data['ctype'][1] == 3}
<div class="hd-ui-sign hd-ui-sign-end">已<br />结<br />束</div>
{/if}
</div>

<div class="ui-main-content ui-list news-content clearfix">
		<span id="datacontent">{$data['content']}</span>
		{cyoa_view_image attr_id='upload_image2' name='atids2' attachs=$data['image']}
</div>

<div class="ui-form">
    <div class="ui-form-item ui-form-item-show ui-conten-more">
        <label>活动地点</label>
        <p>{$data['address']}</p>
    </div>
    <div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
        <label>活动开始</label>
        <p>{$data['start_time']}</p>
    </div>
    <div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
        <label>活动结束</label>
        <p>{$data['end_time']}</p>
    </div>
    <div class="ui-form-item ui-border-t">
        <label>限制人数</label>
		{if $data['np'] == 0}<p>无限制</p>{else}
        <p>{$data['np']}&nbsp;&nbsp;人</p>
		{/if}
    </div>
{if !empty($data['users1']) || !empty($data['dps1'])}
    {cyoa_user_show
					title='邀请人员'
					userids=$data['users1']
					dpids=$data['dps1']
					styleid=2
	}
{/if}
{if $data['m_uid']}
	<div id="interior" class="ui-form-item ui-border-t ui-form-item-link">
{else}
	<div class="ui-form-item ui-border-t">
{/if}
		<label>报名人员</label>
		<p>{$data['anp']}&nbsp;&nbsp;人</p>
	</div>
</div>
{if $data['ctype'][1] == 1 || $data['ctype'][1] == 2}
	{if $data['join'] == 1}
		<p class="hd-ui-wrap">距离活动报名剩余时间</p>
		<div class="ui-btn-lg ui-btn-primary disabled clearfix hd-ui-color">{$data['times']}</div>
	{/if}
	{if $data['ctype'][1] == 1}
		<div class="ui-btn-lg ui-btn-primary disabled clearfix hd-ui-color">活动已开始</div>
	{/if}

	<div class="hd-invite-person">
	<div class="ui-btn-wrap">
	{if $data['outsider'] == '1' && $data['m_uid'] == ''}
		<button class="ui-btn-lg ui-btn-margin" id="out_get_qrcode">报过名了，获取报名凭证</button>
	{/if}
	{*如果外部人员没有报名显示报名按钮*}
	{if $data['outside_is_apply'] != 1 && $data['in'] != 0 && $data['join'] == 1}
		{if $data['snp'] > 0}
			<button class="ui-btn-lg ui-btn-primary" id="{if $data['m_uid'] != ''}sign{else}out_sign{/if}">我要报名{if $data['np'] != 0 }(剩余{$data['snp']}人){/if}</button>
		{else}
			<button class="ui-btn-lg ui-btn-primary"">报名人数已满</button>
		{/if}
	{/if}
	{*内部已报名用户显示凭证*}
	{if $data['in'] == 0}
		<button class="ui-btn-lg ui-btn-primary" id="see">查看报名凭证</button>
	{/if}
	{if $data['join'] == 1}
		{if $data['outsider'] == 1}
		{*显示分享按钮*}
		<button class="ui-btn-lg ui-btn-margin" onclick="wxshare()" id="btn-share">分享给好友</button>
		{/if}
	{/if}
	{*判断是否可以修改*}
	{if $data['edit'] == 1 && $data['ctype'][1] == 2}
		<button class="ui-btn-lg ui-btn-margin" id="edit">修改</button>
	{/if}
	{if $data['in'] == 0 && $data['join'] == 1}
		<button class="ui-btn-lg ui-btn-margin" id="cancel">{if $data['cancel'] == 1}申请退出{else}再次申请退出{/if}</button>
	{/if}
	</div>
	</div>
	<div class="ui-dialog ui-dialog-bg" id="wxshare" onclick="wxshare()"></div>
{else}
	<div class="ui-btn-lg ui-btn-primary disabled clearfix hd-ui-color">活动已经结束</div>
	<br/>
{/if}
{*
{if $data['ctype'][1] == 1}
	{if $data['join'] == 1}
	<p class="hd-ui-wrap">距离活动报名剩余时间</p>
	<div class="ui-btn-lg ui-btn-primary disabled clearfix hd-ui-color">{$data['times']}</div>
	{/if}
	{if $data['join'] == 0}
	<div class="ui-btn-lg ui-btn-primary disabled clearfix hd-ui-color">活动已开始</div>
		{if $data['in'] == 0}
		<button style="margin-top: 25px;" class="ui-btn-lg ui-btn-margin" id="see">查看报名凭证</button>
		{/if}
	{/if}
{/if}
{if $data['ctype'][1] == 2}
	<p class="hd-ui-wrap">距离活动报名剩余时间</p>
	<div class="ui-btn-lg ui-btn-primary disabled clearfix hd-ui-color">{$data['times']}</div>
{/if}
{if $data['ctype'][1] == 3}
	<div class="ui-btn-lg ui-btn-primary disabled clearfix hd-ui-color">活动已经结束</div>
{/if}
<div class="hd-invite-person">

{if $data['ctype'][1] == 1}
	<div class="ui-btn-wrap">
		{if $data['join'] == 1}
			{if $data['in'] == 1}
				{if $data['outsider'] == '1' && $data['m_uid'] == ''}
					<button class="ui-btn-lg ui-btn-margin" id="out_get_qrcode">获取报名凭证（已报名）</button>
				{/if}
				{if $data['outside_is_apply'] != 1}
				{if $data['snp'] > 0}
				<button class="ui-btn-lg ui-btn-primary" id="{if $data['m_uid'] != ''}sign{else}out_sign{/if}">我要报名{if $data['np'] != 0 }(剩余{$data['snp']}人){/if}</button>
				{else}
					<button class="ui-btn-lg ui-btn-primary"">报名人数已满</button>
				{/if}
				{/if}
				<button class="ui-btn-lg ui-btn-margin" onclick="wxshare()" id="btn-share">分享给好友</button>
			{/if}
			<button class="ui-btn-lg ui-btn-primary" id="see">查看报名凭证</button>
			<button class="ui-btn-lg ui-btn-margin" id="cancel">{if $data['cancel'] == 1}申请退出{else}再次申请退出{/if}</button>
		{/if}
	</div>
{/if}
{if $data['ctype'][1] == 2}
	<div class="ui-btn-wrap">
		{if $data['in'] == 1}
			{if $data['outsider'] == '1' && $data['m_uid'] == ''}
				<button class="ui-btn-lg ui-btn-margin" id="out_get_qrcode">获取报名凭证（已报名）</button>
			{/if}
			{if $data['outside_is_apply'] != 1}
			{if $data['snp'] > 0}
			<button class="ui-btn-lg ui-btn-primary" id="{if $data['m_uid'] != ''}sign{else}out_sign{/if}">我要报名{if $data['np'] != 0 }(剩余{$data['snp']}人){/if}</button>
			{else}
				<button class="ui-btn-lg ui-btn-primary"">报名人数已满</button>
			{/if}
			{/if}
			<button class="ui-btn-lg ui-btn-margin" onclick="wxshare()" id="btn-share">分享给好友</button>
		{/if}
		{if $data['edit'] == 1}
			<button class="ui-btn-lg ui-btn-margin" id="edit">修改</button>
		{/if}
		{if $data['in'] == 0}
			<button class="ui-btn-lg ui-btn-margin" onclick="wxshare()" id="btn-share">分享给好友</button>
			<button class="ui-btn-lg ui-btn-primary" id="see">查看报名凭证</button>
			<button class="ui-btn-lg ui-btn-margin" id="cancel">{if $data['cancel'] == 1}申请退出{else}再次申请退出{/if}</button>
		{/if}
	</div>
{/if}
*}



<!--取消报名弹出框-->
<div id="celbao" class="ui-dialog">
    <div class="ui-dialog-cnt">
    	 <form name="frmpost" id="frmpost" method="post" action="">
        <div class="ui-dialog-bd">
				<input type="hidden" name="m_uid" value="{$data['m_uid']}" />
	            <input type="hidden" id="acid" name="acid" value="{$data['acid']}"/>
				<input type="hidden" id="ac" name="ac" value=""/>
				<textarea name="message" placeholder="请输入取消报名的原因！" id="message"></textarea>
        </div>
        <div class="ui-dialog-ft ui-btn-group">
            <button type="button"   class="select" id="message_cancel">取消</button>
            <button type="submit"  class="select" id="message_sure">提交</button>
        </div>
        </form>
    </div>
</div>

<!--报名添加备注弹出框-->
<div id="remark" class="ui-dialog">
	<div class="ui-dialog-cnt">
		<div class="ui-dialog-bd">
			<h4>备注</h4>
			<textarea name="remarks" id="remarks" style="margin: 0px; height: 100px;" placeholder="输入内容，如无备注可直接提交"></textarea>
		</div>
		<div class="ui-dialog-ft ui-btn-group">
			<button type="button"   class="select" id="remark_cancel">取消</button>
			<button type="submit"  class="select" id="remark_sure">提交</button>
		</div>
	</div>
</div>

<!--签到二维码弹出框-->
<div id="qrcode" class="ui-dialog" style="z-index: 99999;">
	<div class="ui-dialog-cnt">
		<div class="ui-dialog-bd">
			<h4>{$data['title']}</h4>
			{if $data['in'] == 0}
				<img src="/frontend/activity/view?acid={$data['acidm']}&ac=getcode&npe={$data['npe']}" width="230" height="230" />
			{/if}
		</div>
		<div class="ui-dialog-bd" style="margin-top:-40px;">
		<p>可长按图片保存</p>
		<p>现场签到时请出示</p>
		</div>
		<div class="ui-dialog-ft ui-btn-group" style="margin-top:-10px;">
			<button id="sureqr" type="button">确定</button>
		</div>
	</div>
</div>

	<input type="hidden" id="exacid" value="{$data['exacid']}"/>
	<input type="hidden" id="acid" value="{$data['acid']}"/>
	<input type="hidden" id="joinin" value="{$data['in']}"/>
	<input type="hidden" id="now_user" value="{$data['now_user']}"/>
	<input type="hidden" id="can_join" value="{$data['can_join']}"/>
	<input type="hidden" id="allow_outsider" value="{$data['outsider']}"/>

	{else}
	<section class="ui-notice ui-notice-fail"> <i></i>
		<h2>对不起</h2>
		<p>您所查看的是内部活动</p>
		<div class="ui-btn-wrap">
			<button class="ui-btn-lg" onclick="javascript:history.go(-1);">返回</button>
		</div>
	</section>
{/if}


<script type="text/javascript">
require(["zepto", "underscore", "submit", "wxshare"], function($, _, submit, WXShare) {

	var sbt = new submit();
	sbt.init({
		"form": $("#frmpost")
	});

	$('#sign').on('click', function () {
		$('#remark').dialog("show");
	});
	$('#remark_cancel').on('click', function() {
		$('#remarks').val('');
		$('#remark').dialog("hide");
	});
//  内部人员报名时弹出输入备注的弹出框
	$('#remark_sure').on('click', function () {
		var remarks = $('#remarks').val();
		$('#remarks').val('');
		$('#remark').dialog("hide");
		$('#ac').attr('value','join');
		$('#message').val(remarks);
		$('#frmpost').attr('action', '/api/activity/post/sign');
		$('#frmpost').submit();
	});
//  跳转外部人员查看自己的二维码
	$('#out_get_qrcode').on('click', function () {
		window.location.href = '/frontend/activity/outqcode?ac=recode&acid='+$('#exacid').val();
	});
//  跳转内部人员查看报名人员
	$('#interior').on('click', function() {
		window.location.href = '/frontend/activity/view?ac=interior&acid='+$('#acid').val();
	});
//  跳转外部人员报名
	$('#out_sign').on('click', function () {
		var snp = {$data['snp']};
		if (snp < 1) {
			$.tips({
				content:'报名人数已满'
			});
			return false;
		}
		window.location.href = '/frontend/activity/outsign?ac=out-sign&acid='+$('#exacid').val();
	});
	$('#cancel').on('click', function() {
		$("#celbao").dialog("show");
	});
	$('#message_cancel').bind('click',function(){
		$("#celbao").dialog("hide");
	});
	$('#edit').tap(function (e) {
		 window.location.href = '{$editurl}';
	});
	$('#message_sure').bind('click',function(){
		var apply_reason = $.trim($('#message').val());
		if (apply_reason == '') {
			$.tips({
				content:'请输入取消报名的原因'
			});
			$('#message').val('');
			return false;
		}
		$('#ac').attr('value','apply');
		$('#frmpost').attr('action', '/api/activity/post/sign');
	});
	$('#see').on('click', function() {
		$("#qrcode").dialog("show");
		$('body').css("overflow","hidden")
	});

	$('#sureqr').on('click', function() {
		$("#qrcode").dialog("hide");
	});

	{* 调用分享接口 *}
	{$_cyoa_jsapi_[] = 'onMenuShareTimeline'}
	{$_cyoa_jsapi_[] = 'onMenuShareAppMessage'}
	{$_cyoa_jsapi_[] = 'onMenuShareQQ'}
	{$_cyoa_jsapi_[] = 'onMenuShareWeibo'}
	var wxshare = new WXShare();
	wxshare.load({rjson_encode($share_data)});

});

//  显示分享给好友
	function wxshare(){
		$("#wxshare").toggleClass("show");
	}

</script>

{literal}
<script type="text/javascript">
require(["zepto"], function($) {
	var can_join = $('#can_join').val();
	var allow_outsider = $('#allow_outsider').val();
	if (can_join == '1') {
		var tips_message = '报名人数超过限制';
	}
	if (can_join == '2') {
		var tips_message = '没有邀请任何内部人员';
	}
	if (can_join == '3') {
		var tips_message = '没有被邀请';
	}
	$('#sign').on('click', function() {
		if (can_join != '0') {
			$('#sign').unbind();
				$.tips({content:tips_message});
			$('#sign').bind('click', function() {
				$.tips({content:tips_message});
			});
		}
	});
});
</script>
{/literal}

{include file='mobile/footer.tpl'}