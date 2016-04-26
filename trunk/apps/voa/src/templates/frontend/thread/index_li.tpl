{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

{foreach $threads as $tid => $thread}
<li id="{if 0 < $thread['t_displayorder']}t{else}b{/if}_{$thread['t_updated']}">
	<a href="/thread/viewthread/{$thread['t_id']}">
		<h1>{$thread['t_subject']}</h1>
		<time>{$thread['m_username']} {$thread['_created']} 发布</time>
		<p class="re">{$thread['t_replies']}回复</p>
		{if 0 < $threads['t_displayorder']}<p>置顶</p>{/if}
	</a>
	<div class="mod_list_actions_btns" style="display:none;">
		<a href="javascript:;" rel="/thread/delete/{$thread['t_id']}" class="rm">删除工作</a>
		{if 0 < $thread['t_displayorder']}
		<a href="javascript:;" rel="/thread/displayorder/{$thread['t_id']}?ac=cancel" class="untop">取消置顶</a>
		{else}
		<a href="javascript:;" rel="/thread/displayorder/{$thread['t_id']}?ac=up" class="top">置顶</a>
		{/if}
	</div>
</li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}