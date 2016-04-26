{include file='frontend/header.tpl'}
<body id="wbg_rc_detail">

<header>
	<h1>{$plan['_begin_at_y']}<i>年</i>{$plan['_begin_at_m']}<i>月</i>{$plan['_begin_at_d']}<i>日</i></h1>
	<a class="toggle" href="/plan/new"><!--<span>全部日程</span>--></a>
</header>

<div class="mod_common_list_style">
	<div class="detail">
		<figure style="background: no-repeat
			{if $plan['pl_type'] eq 0}#41c94f
			{elseif $plan['pl_type'] eq 1}#36BCF9
			{elseif $plan['pl_type'] eq 3}#FF99FF
			{else}#FFCC00
			{/if};
		">
			<img src="/misc/images/rc_type{$plan['pl_type'] + 1}.png" /><span>{$types[$plan['pl_type']]}</span>
		</figure>
		<time>{$plan['_begin_at_t']}<em>{$plan['_finish_at_t']}</em></time>
		<div><h1>{$plan['pl_subject']}</h1></div>
		<div>{$plan['pl_address']}</div>
		<ul>
			<li class="addr">{$plan['pl_address']}</li>
			<li class="clock">{$plan['_alarm_at']}</li>
			<li class="share">
				<b>分享人:</b>
				{foreach $ccusers as $users}
				<div class="mod_member_item"><img src="http://img1.cache.netease.com/sports/2009/goal/logo/player/70/50175.jpg" />{$users['m_username']}</div>
				{/foreach}
			</li>
		</ul>
	</div>
</div>

<div class="foot">
	<a href="/plan" class="return">返回</a>
	<a href="/plan/edit/{$plan['pl_id']}" class="edit">编辑</a>
	<a href="#" class="remove" onclick="handleDelete({$plan['pl_id']})">删除</a>
</div>

{include file='frontend/footer_nav.tpl'}

<script>
require(['dialog', 'business', 'template'], function () {
	$onload(function(){
		var $fg = $one('.detail>figure');
		$fg.style.height = $fg.parentNode.clientHeight + 'px';
	});
});
// 配合删除按钮
function handleDelete (id) {
	var ajaxLock = false;

	if (ajaxLock) return;
	ajaxLock = true;

	MLoading.show('正在更新...');
	$ajax(
		'/plan?ac=delete', 'GET',
		{
			'id': id
		},
		function(ajaxResult){

			if (ajaxResult.response === "success") {
				window.location.href = '/plan';
			} else {
				alert('something happend');
			}

			MLoading.hide();
			ajaxLock = false;
		},
		true
	);
}
</script>
{include file='frontend/footer.tpl'}
