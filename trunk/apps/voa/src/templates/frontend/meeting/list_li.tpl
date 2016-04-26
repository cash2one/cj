{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_meeting_updated = '{$updated}';
</script>

{foreach $list as $v}
<li{if voa_d_oa_meeting::STATUS_CANCEL == $v['mt_status'] || $v['mt_endtime'] < $ts} class="closed"{/if}>
	<a href="/meeting/view/{$v['mt_id']}">
		<time datetime="{$v['_Y']}-{$v['_m']}-{$v['_d']}"><h2>{$v['_Y']|substr:-2}年{$v['_m']}月</h2><h1>{$v['_d']}</h1><h3>{$weeknames[$v['_w']]}</h3></time>
		<h1>{$v['mt_subject']}</h1>
		<time datetime="{$v['_h']}:{$v['_i']}">
			{if voa_c_frontend_meeting_base::ST_NORMAL == $v['_st']}
				{$v['_h']}:{$v['_i']} {$v['_A']}
			{else}
				{$st_tips[$v['_st']]}
			{/if}
		</time><p>{$rooms[$v['mr_id']]['mr_name']}</p>
	</a>
	<div class="mod_list_actions_btns" style="display:none;">
		<a href="#" class="rm">删除</a>
	</div>
</li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}