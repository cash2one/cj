{include file='frontend/header.tpl'}

<body id="wbg_addrbook_profile">

<header>
	<div class="mod_member_item"><img src="{$cinstance->avatar($user.m_uid)}" />{$user.m_username}</div>
</header>

<ul class="mod_common_list part1">
	<!--这里的样式名(后一个)将在doUpdateAjax作为type参数提交-->
	<li class="withicon department" style="background-image:none;margin-left:25px;">
		<table style="width:100%;margin-left:0">
			<tr>
				<th style="width:10px;white-space:nowrap;vertical-align: top;"><span class="m_icon" style="left:-15px"></span><label style="margin-left:13px;padding-left:0;">部门</label></th>
				<td style="padding-left:10px;padding-right:10px"><div style="overflow:auto;width:100%;color:#797979;text-align:right;line-height: 20px;">{if !empty($department)}{$department.cd_name}{/if}</div></td>
			</tr>
		</table>
<!--
		<span class="m_icon"></span>
		<label>部门</label><i>{if !empty($department)}{$department.cd_name}{/if}</i>
-->
	</li>
	<li class="withicon job" style="background-image:none;margin-left:25px;">
		<table style="width:100%;margin-left:0">
			<tr>
				<th style="width:10px;white-space:nowrap;vertical-align: top;"><span class="m_icon" style="left:-15px"></span><label style="margin-left:13px;padding-left:0">职位</label></th>
				<td style="padding-left:10px;padding-right:10px"><div style="overflow:auto;width:100%;color:#797979;text-align:right;line-height: 20px;">{if !empty($job)}{$job.cj_name}{/if}</div></td>
			</tr>
		</table>
<!--
		<span class="m_icon"></span>
		<label>职位</label><i>{if !empty($job)}{$job.cj_name}{/if}</i>
-->
	</li>
</ul>

<ul class="mod_common_list part2" style="list-style-position:inside;overflow:hidden">
	<li class="withicon mobile" style="background-image:none;margin-left:25px;">
		<table style="width:100%;margin-left:0">
			<tr>
				<th style="width:10px;white-space:nowrap"><span class="m_icon" style="left:-15px"></span><label style="margin-left:13px;padding-left:0">手机</label></th>
				<td style="padding-left:10px;padding-right:10px"><div style="overflow:auto;width:100%;text-align:right;color:#797979"><a style="display:block" {if {$user.m_mobilephone}} href="tel:{$user.m_mobilephone}" {else}href="javascript:;"{/if}>{$user.m_mobilephone}</a></div></td>
			</tr>
		</table>
<!--
		<span class="m_icon"></span>
		<label>手机</label><a {if {$user.m_mobilephone}} href="tel://{$user.m_mobilephone}" {else}href="javascript:;"{/if}><input type="tel" value="{$user.m_mobilephone}" readonly required pattern="^1[3|4|5|8][0-9]\d{8}$" /></a>
-->
	</li>
<!--
	<li class="withicon phone" style="background-image:none">
		<span class="m_icon"></span>
		<label>座机</label><input type="tel" value="{$member_field.mf_telephone}" readonly required pattern="^[\d\-\s]+$" />
	</li>
-->
	<li class="withicon email" style="background-image:none;margin-left:25px;">
		<table style="width:100%;margin-left:0">
			<tr>
				<th style="width:10px;white-space:nowrap"><span class="m_icon" style="left:-15px"></span><label style="margin-left:13px;padding-left:0">邮箱</label></th>
				<td style="padding-left:10px;padding-right:10px;"><div style="overflow:auto;width:100%;color:#797979;text-align:right;">{$user.m_email}</div></td>
			</tr>
		</table>
<!--
		<span class="m_icon"></span>
		<label>邮箱</label><a href="{if $user.m_email}mailto:{$user.m_email}{else}javascript:;{/if}"><input type="email" value="{$user.m_email}" readonly required pattern="^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{ldelim}2,3{rdelim}){ldelim}1,2{rdelim})$" /></a>
-->
	</li>
</ul>

<ul class="mod_common_list part2" style="list-style-position:inside;overflow:hidden">
{if $user['m_gender']}
	<li class="withicon" style="background-image:none;margin-left:25px;">
		<table style="width:100%;margin-left:0">
			<tr>
				<th style="width:10px;white-space:nowrap"><span class="m_icon" style="left:-15px"></span><label style="margin-left:13px;padding-left:0">性别</label></th>
				<td style="padding-left:10px;padding-right:10px;"><div style="overflow:auto;width:100%;color:#797979;text-align:right;">
				{if $user['m_gender'] == 1}男{else}女{/if}
				</div></td>
			</tr>
		</table>
	</li>
{/if}
{if $user['m_weixinid']}
	<li class="withicon" style="background-image:none;margin-left:25px;">
		<table style="width:100%;margin-left:0">
			<tr>
				<th style="width:10px;white-space:nowrap"><span class="m_icon" style="left:-15px"></span><label style="margin-left:13px;padding-left:0">微信号</label></th>
				<td style="padding-left:10px;padding-right:10px;"><div style="overflow:auto;width:100%;color:#797979;text-align:right;">
				{$user['m_weixinid']}
				</div></td>
			</tr>
		</table>
	</li>
{/if}
{foreach $field_data as $_v}
	<li class="withicon" style="background-image:none;margin-left:25px;">
		<table style="width:100%;margin-left:0">
			<tr>
				<th style="width:10px;white-space:nowrap"><span class="m_icon" style="left:-15px"></span><label style="margin-left:13px;padding-left:0">{$_v['name']}</label></th>
				<td style="padding-left:10px;padding-right:10px;"><div style="overflow:auto;width:100%;color:#797979;text-align:right;">
				{$_v['value']}
				</div></td>
			</tr>
		</table>
	</li>
{/foreach}
</ul>

<div class="foot">
	<a href="javascript:history.go(-1);" class="mod_button2">返回</a>
</div>

{literal}
<script>
require(['dialog'], function() {
	var isSelf = false;

	$onload(function(){

		var onIptClick = function(e){
			if ( !isSelf ) return;
		
			var ipt = e.currentTarget;
			if ( !ipt.hasAttribute('readonly') ) return;
			
			var ubtn = $prev(ipt);
			ipt.removeAttribute('readonly');
			$addCls(ubtn, 'show');
			ipt.focus();
			try{
				ipt.setSelectionRange(ipt.value.length, ipt.value.length);
			}catch(ex){}
			
			$data(ipt, 'oldvalue', $trim(ipt.value));
		};
		var onBtnClick = function(e){
			var ubtn = e.currentTarget,
				ipt = $next(ubtn);
			if ( ipt.hasAttribute('readonly') ) return;
			
			var type = ubtn.parentNode.className.replace('withicon ', ''),
				cfg = fieldsCfg[type],
				rqr = ipt.hasAttribute('required'),
				re = ipt.hasAttribute('pattern') ? new RegExp(ipt.pattern): null,
				v = $trim(ipt.value);
			if (rqr && v === ''){
				alert(cfg.requiredNotice);
				return;
			}
			if (re && !re.test(v)){
				alert(cfg.patternNotice);
				return;
			}
			
			if (v !== $data(ipt, 'oldvalue')){
				doUpdateAjax(type, v);
			}
			ipt.setAttribute('readonly', true);
			$rmCls(ubtn, 'show');
		};
		$each('.part2 input', function(ipt){
			if (!isSelf){
				ipt.parentNode.style.background = 'none';
				$one('.update', ipt.parentNode).style.width = '15px';
			}else{
				ipt.addEventListener('click', onIptClick);
				$prev(ipt).addEventListener('click', onBtnClick);
			}
		});

	});
});
</script>
{/literal}

{include file='frontend/footer.tpl'}