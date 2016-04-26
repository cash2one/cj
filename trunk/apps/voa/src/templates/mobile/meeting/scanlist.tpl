{include file='mobile/header.tpl' navtitle="待参加会议"}
	<ul class="ui-list ui-border-tb">
	{foreach $list as $meet}
		<li>
			<div class="ui-list-info">
				<h4 ><a href="/meeting/view/{$meet.mt_id}">{$meet.mt_subject}</a></h4>
				<p class="ui-nowrap">{$meet.m_username} {$meet.bthi} — {$meet.enhi}</p>
			</div>
			<div class="ui-list-action ui-list-status">
				{if $meet.finish}已结束{/if}
				{if $meet.no}未开始{/if}
				{if $meet.ing}进行中{/if}
			</div>
		</li>
		<li class="ui-border-b" rel="{$meet.mt_id}">
			{if !$meet.finish}
			<div class="ui-btn-group-right">
				{if $meet.m_uid == $meet.mem.m_uid}
					{if $meet.no}<button class="cancel ui-btn clearfix" type="button">取消会议</button>{/if}
					{if $meet.ing}<button class="finish ui-btn clearfix" type="button">提前结束</button>{/if}
				{/if}
				{if $meet.mem.mm_confirm}
					<button class="ui-btn disabled ui-btn-primary">已签到</button>
				{else}	
					<button class="sign ui-btn ui-btn-primary" type="button">签到</button>
				{/if}
			</div>
			{/if}
		</li>
	{/foreach}
	</ul>

	
<script type="text/javascript">
require(["zepto"], function($) {
	//签到
	$('button.sign').click(function (){
		var id = $(this).closest('li').attr('rel');
		location.href = '/frontend/meeting/scan?act=sign&mt_id=' + id;
	});
	//取消
	$('button.cancel').click(function (){
		var ul = $('ul.ui-list');
		var count = ul.find('li').length;
		var li = $(this).closest('li');
		var id = li.attr('rel');
		$.getJSON('/frontend/meeting/cancel?mt_id=' + id, function (json){
			if(json.state) {
				$.tips({
			        content:'取消会议成功'
			    });
			    //如果只有一个会议,取消后跳回列表页
				if(count == 2) {
					setTimeout(function (){
						location.href = '/meeting/list';
					}, 1000);
				}else{
					li.prev('li').remove();
			    	li.remove();
				}
			}else{
				$.tips({
			        content:'取消会议失败 : ' + json.info,
			        type: 'warn',
			        stayTime: 5000
			    });
			}
		});
	});
	//结束
	$('button.finish').click(function (){
		var li = $(this).closest('li');
		var prevli = li.prev('li');
		var id = li.attr('rel');
		$.getJSON('/frontend/meeting/scan?act=finish&mt_id=' + id, function (json){
			if(json.state) {
				$.tips({
			        content:'提前结束成功'
			    });
			    prevli.find('.ui-list-status').text('已结束');
			    li.find('.ui-btn-group-right').remove();
			}else{
				$.tips({
			        content:'提前结束失败 : ' + json.info,
			        type: 'warn',
			        stayTime: 5000
			    });
			}
		});
	});
});
</script>

{include file='mobile/footer.tpl'}