{include file='mobile/header.tpl' navtitle=$m['mt_subject']}

    <div class="ui-top-box">
        <h2>{$m.mt_subject}</h2>
        <p>{$room.mr_name}</p>
    </div>
    <div class="ui-form">
    	<div class="ui-form-item ui-form-item-show ui-conten-more">
            <label class="ui-icon add">地点</label>
            <p>{$m['mt_address']|escape}</p>
        </div>
        <div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
            <label class="ui-icon date">日期</label>
            <p>{$m.date} (星期{$m.week})</p>
        </div>
        <div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
            <label class="ui-icon duration">时长</label>
            <p>{$m.length}</p>
        </div>
        <div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
            <label class="ui-icon time">时间</label>
            <p>{$m.time}</p>
        </div>
    </div>

    <div class="ui-form">
	<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
            <label>会议状态</label>
            <p>{$meeting_status_string}</p>
        </div>
        <div class="ui-form-item ui-border-t ui-form-contacts">
            <label>参会人员</label>
            <div class="select-contact clearfix">
                <div class="ui-border-b">共邀请{$total}人</div>
                {if $ct_sign_users}
                <div class="contact-num">签到{$ct_sign_users}人</div>
                <div class="select-box ui-border-b clearfix">
               		{foreach $sign_users as $k => $v}
                    <div class="ui-badge-wrap">
                        <div class="ui-avatar-s">
                            <span style="background-image:url({$cinstance->avatar($k)})"></span>
                        </div>
                        <div class="name">{$v}</div>
                    </div>
                    {/foreach}
                </div>
                {/if}
                {if $confirm_users}
                <div class="contact-num">确认{$ct_confirm_users}人</div>
                <div class="select-box ui-border-b clearfix">
               		{foreach $confirm_users as $k => $v}
                    <div class="ui-badge-wrap">
                        <div class="ui-avatar-s">
                            <span style="background-image:url({$cinstance->avatar($k)})"></span>
                        </div>
                        <div class="name">{$v}</div>
                    </div>
                    {/foreach}
                </div>
                {/if}
                {if $unconfirm_users}
                <div class="contact-num">未确认{$ct_unconfirm_users}人</div>
                <div class="select-box clearfix">
                    {foreach $unconfirm_users as $k => $v}
                    <div class="ui-badge-wrap">
                        <div class="ui-avatar-s">
                            <span style="background-image:url({$cinstance->avatar($k)})"></span>
                        </div>
                        <div class="name">{$v}</div>
                    </div>
                    {/foreach}
                </div>
                {/if}
                {if $absence_list}
                <div class="contact-num">取消{$ct_absence_list}人</div>
                <div class="select-box clearfix">
                    {foreach $absence_list as $k => $v}
                    <div class="ui-badge-wrap">
                        <div class="ui-avatar-s">
                        	<span style="background-image:url({$cinstance->avatar($k)})"></span>
                        </div>
                        <div class="name">{$v.m_username}</div>
                    </div>
                    {/foreach}
                </div>
                {/if}
            </div>
        </div>

    </div>
    {if !$meeting_closed}
	    {if $is_main}
		   {if !$meeting_ing}
			    <div class="ui-btn-wrap">
			        <button id="cancel" class="ui-btn-lg">取消会议</button>
			    </div>
		    {/if}
		    <div class="ui-dialog">
				<div class="ui-dialog-cnt">
					<form action="javascript:;" method="POST">
						<input type="hidden" name="mt_id" value="{$m.mt_id}"/>
						<div class="ui-dialog-bd">
							<div>
								<h4>确认信息?</h4>
								<div>确定取消会议!</div>
							</div>
						</div>
						<div class="ui-dialog-ft ui-btn-group">
							<button type="button" data-role="button" class="select" id="dialogButton1">关闭</button>
							<button type="button" class="select" id="submit2">确定</button>
						</div>
					</form>
				</div>
			</div>
	    {else}
	    	{if $mms[$wbs_uid]['mm_status'] == voa_d_oa_meeting_mem::STATUS_NORMAL}
		    <div class="ui-btn-group-tiled ui-btn-wrap">
		        <button id="absence" class="ui-btn-lg">不参加</button>
		        <button id="join" class="ui-btn-lg ui-btn-primary">参加</button>
		    </div>
		    {/if}
		    <div class="ui-dialog">
				<div class="ui-dialog-cnt">
					<form action="javascript:;" method="POST">
						<input type="hidden" name="mt_id" value="{$m.mt_id}"/>
						<div class="ui-dialog-bd">
							<div>
								<h4>你确定不参数会议吗?</h4>
								<div>原因: <input name="message" id="message" type="text" placeholder="必须填写原因"/></div>
							</div>
						</div>
						<div class="ui-dialog-ft ui-btn-group">
							<button type="button" data-role="button" class="select" id="dialogButton1">关闭</button>
							<button type="button" class="select" id="submit">确定</button>
						</div>
					</form>
				</div>
			</div>
	    {/if}
    {/if}
<br />
{include file='mobile/footer.tpl'}
<script>
require(["zepto"], function($) {
	//拒绝参加
	$('#absence').click(function (){
		$('.ui-dialog').dialog('show');
	});
	$('#submit').click(function(){
		if(!$('#message').val()) {
			$.tips({
		        content:'请填写拒绝原因',
		        type: 'warn',
			    stayTime: 3000
		    });
		    return false;
		}
		var post = $('form').serialize();
		$.post('/frontend/meeting/absence/', post, function (json){
			if(json.state) {
				$.tips({
			        content:'拒绝成功'
			    });
			    setTimeout(function (){
			    	location.reload();
			    }, 1000);
			}else{
				$.tips({
			        content:'提交失败 : ' + json.info,
			        type: 'warn',
			        stayTime: 5000
			    });
			}
		}, 'json');
	});
	
	
		
	//参加
	$('#join').click(function (){
		$.getJSON('/frontend/meeting/confirm/?mt_id={$m.mt_id}', function (json){
			if(json.state) {
				$.tips({
			        content:'操作成功'
			    });
			    $('.ui-btn-group-tiled').remove();
			    setTimeout(function (){
			    	location.reload();
			    }, 1000);
			}else{
				$.tips({
			        content:'操作失败 : ' + json.info,
			        type: 'warn',
			        stayTime: 5000
			    });
			}
		});
	});
	
	//取消会议
	$('#cancel').click(function (){
		$('.ui-dialog').dialog('show');
	});
	$('#submit2').click(function(){
		$('.ui-dialog').dialog('hide');
		$.getJSON('/frontend/meeting/cancel/?mt_id={$m.mt_id}', function (json){
			if(json.state) {
				$.tips({
			        content:'操作成功'
			    });
			    $('.ui-btn-wrap').remove();
			    setTimeout(function (){
			    	location.href = '/meeting/list/';
			    }, 1000);
			}else{
				$.tips({
			        content:'操作失败 : ' + json.info,
			        type: 'warn',
			        stayTime: 5000
			    });
			}
		});
	});
	
	//人员图标最右侧去掉右边距
	$('.select-box').each(function (i, e){
		$(e).find('.ui-badge-wrap').eq(-1).css('margin-right', 0);
	});
});
</script>
<style>
.ui-poptips {
	z-index: 9999;
}
</style>