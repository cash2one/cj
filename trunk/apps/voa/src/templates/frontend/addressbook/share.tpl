{include file='frontend/header.tpl'}

<body id="wbg_addrbook_profile">

<header>
	<div class="mod_member_item"><img src="{$cinstance->avatar($user.m_uid)}" />{$user.m_username}</div>
</header>

<ul class="mod_common_list part1">
	<!--这里的样式名(后一个)将在doUpdateAjax作为type参数提交-->
	<li class="withicon department">
		<span class="m_icon"></span>
		<label>所属部门</label><i>{if !empty($department)}{$department.cd_name}{/if}</i>
	</li>
	<li class="withicon job">
		<span class="m_icon"></span>
		<label>职位</label><i>{if !empty($job)}{$job.cj_name}{/if}</i>
	</li>
</ul>

<ul class="mod_common_list part2">
	<li class="withicon mobile">
		<span class="m_icon"></span>
		<label>手机</label><a href="javascript:void(0)" class="update">√</a><input type="tel" value="{$member.m_mobilephone}" readonly required pattern="^1[3|4|5|8][0-9]\d{8}$" />
	</li>
	<!-- <li class="withicon phone">
		<span class="m_icon"></span>
		<label>座机</label><a href="javascript:void(0)" class="update">√</a><input type="tel" value="{$member_field.mf_telephone}" readonly required pattern="^[\d\-\s]+$" />
	</li> -->
	<li class="withicon email">
		<span class="m_icon"></span>
		<label>邮箱</label><a href="javascript:void(0)" class="update">√</a><input style="width:200px;" type="email" value="{$member.m_email}" readonly required pattern="^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{ldelim}2,3{rdelim}){ldelim}1,2{rdelim})$" />
	</li>
</ul>

<!-- 
<div class="foot">
	<a href="javascript:history.go(-1);" class="mod_button2">返回</a>
</div>
 -->

{literal}
<script>
require(['dialog'], function() {
	$onload(function() {
		$each('.part2 input', function(ipt) {
			ipt.parentNode.style.background = 'none';
			$one('.update', ipt.parentNode).style.width = '15px';
		});

	});
});
</script>
{/literal}

{include file='frontend/footer.tpl'}