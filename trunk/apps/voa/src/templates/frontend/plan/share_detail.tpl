{include file='frontend/header.tpl'}
<body id="wbg_rc_detail">

<header>
	<h1>{$plan['_begin_at_y']}<i>年</i>{$plan['_begin_at_m']}<i>月</i>{$plan['_begin_at_d']}<i>日</i></h1>
	<h2>{$plan['m_username']}</h2>
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
		<div>{$plan['m_username']}</div>
		<ul>
			<li class="addr">{$plan['pl_address']}</li>
		</ul>
	</div>
</div>

<div class="foot">
	<a href="/plan/share" class="return">返回</a>
	<a href="#" class="remove" onclick="handleDelete({$plan['plm_id']})">删除</a>
</div>

{include file='frontend/footer_nav.tpl'}

<script>
require(['dialog', 'business', 'template'], function () {
	$onload(function(){
		var $fg = $one('.detail>figure');
		$fg.style.height = $fg.parentNode.clientHeight + 'px';
	});
	// 配合删除按钮
	window.handleDelete = function(id) {
		var ajaxLock = false;

		if (ajaxLock) return;
		ajaxLock = true;

		MLoading.show('正在更新...');
		$ajax(
			'/plan/share/detail', 'GET',
			{
				'ac': 'delete',
				'id': id
			},
			function(ajaxResult){

				if (ajaxResult.response === "success") {
					window.location.href = '/plan/share';
				} else {
					alert('something happend');
				}

				MLoading.hide();
				ajaxLock = false;
			},
			true
		);
	}
});
</script>

{include file='frontend/footer.tpl'}


