{foreach $list as $fp}
<li>
	<a class="m_link item" href="javascript:void(0)">
		<div><h1>{$fp['m_username']}</h1><h2><em{if $type_done == $fp['fp_type']} class="done"{/if}>{if empty($types[$fp['fp_type']])}{$fp['fp_type']}{else}{$types[$fp['fp_type']]}{/if}</em>{$fp['_subject']}</h2></div>
		<i>{$fp['_address']}</i>
		<time>{$fp['_visittime_u']}</time>
	</a>
	<dl style="display:none;">
		{include file='frontend/mod_img_list.tpl' attachs=$fp_attachs[$fp['fp_id']]}
		<a href="javascript:void(0)" class="commentBtn" rel="/footprint/reply/{$fp['fp_id']}?handlekey=post"><span>回复</span></a>
		<ul class="mod_comment_list simple">
			{foreach $fp_posts[$fp['fp_id']] as $p}
			<li>
				<h1>{$p['m_username']}:</h1>
				<p>{$p['_message']}</p>
				<time>{$p['_created_u']}</time>
			</li>
			{/foreach}
		</ul>
	</dl>
</li>
{/foreach}