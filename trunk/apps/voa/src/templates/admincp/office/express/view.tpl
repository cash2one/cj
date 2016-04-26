{include file="$tpl_dir_base/header.tpl"}


<div class="panel panel-default">
	<div class="panel-body">
		<dl class="dl-horizontal font12 vcy-dl-list" style="margin-bottom: 0">
			<dt>快递状态：</dt>
			<dd>
				<strong class="label label-primary font12">{if $express['flag'] == 1}待领取{else}已领取{/if}</strong>
			</dd>
			<dt>快递代收人：</dt>
			<dd>
				<strong>{$express['r_username']}</strong>
			</dd>
			<dt>接收时间：</dt>
			<dd><abbr><span class="badge">{$express['_created']}</span></abbr></dd>
			<dt>收件人：</dt>
		    <dd>
                <strong>{$express['username']}</strong>
            </dd>
            <dt>领取时间：</dt>
            <dd><abbr><span class="badge">{$express['_get_time']}</span></abbr></dd>
            {if !empty($express['c_username'])}
            <dt>代领人人：</dt>
            <dd>
                <strong>{$express['c_username']}</strong>
            </dd>
            {/if}
			<dt>快递详情：</dt>
			<dd>
			<div class="col-xs-2">
			{foreach $attachs as $ach}
				<a href="/attachment/read/{$ach.aid}" target="_blank" class="thumbnail"  style="margin-top:10px">
                <img alt="" src="/attachment/read/{$ach.aid}">
                </a>
            {/foreach}
            </div>
			</dd>
		</dl>
	</div>
</div>


{include file="$tpl_dir_base/footer.tpl"}
