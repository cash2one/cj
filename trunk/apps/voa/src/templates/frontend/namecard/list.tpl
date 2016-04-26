{include file='frontend/header.tpl'}

<body id="wbg_crm_profile">

<header class="mod_profile_header type_A">
	<div class="center">
		<figure><img src="{$cinstance->avatar($uid)}" /></figure>
		<h1>{$wbs_username}<span>{$jobs[$wbs.cj_id].cj_name}</span></h1>
		<h2>畅移云工作－CRM</h2>
	</div>
	<ul>
		<li><a href="/namecard"><b>{$ct_namecard}</b>名片夹</a></li>
		<li><a href="/namecard/folder"><b>{$ct_folder}</b>群组管理</a></li>
		<li><a href="/namecard/new"><i class="icon build"></i>新的名片</a></li>
	</ul>
</header>

{if empty($ncfs)}
<em class="mod_empty_notice"><span>您还没有名片夹信息</span></em>
{else}
<div style="position:relative"><div class="mod_members_panel namecards">
	{foreach $ncfs as $f}
	<div{if $f@first} class="opened"{/if}>
		<h1>{$f['ncf_name']}<span>({$f['ncf_num']})</span></h1>
		<ul{if $f@first}{else} style="display: none;"{/if}>
			{foreach $listbyfolder[$f['ncf_id']] as $c}
			<li>
				<a href="/namecard/view/{$c['nc_id']}" class="go"><img src="{$cinstance->avatar(0)}" />{$c['nc_realname']}<em>{$ncjs[$c['ncj_id']]['ncj_name']}</em></a>
				<div class="mod_list_actions_btns" style="display:none;">
					<a href="javascript:;" rel="/namecard/delete/{$c['nc_id']}" class="rm">删除</a>
				</div>
			</li>
			{/foreach}
		</ul>
	</div>
	{/foreach}
</div></div>
{/if}

<script>
{literal}
$onload(function(){
	$each( '.namecards>div', function(d, didx, divs){
		$one('h1', d).addEventListener('touchend', function(e2){
			if ( $hasCls(d, 'opened') ){
				$rmCls(d, 'opened');
				$hide( $one('ul', d));
				return;
			}

			$each( divs, function(div){
				$rmCls(div, 'opened');
				$hide( $one('ul', div) );
			});
			$addCls(d, 'opened');
			$show( $one('ul', d));
		});
	});
});
{/literal}
</script>

{include file='frontend/footer.tpl'}