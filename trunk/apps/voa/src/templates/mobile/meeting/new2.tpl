{include file='mobile/header.tpl' navtitle="发起会议2/3"}

<div class="ui-top-border"></div>
{foreach $rooms as $i => $rs}
	<a name="{$i}"></a>
	<div class="ui-txt-muted">{$i}楼</div>
	<ul class="ui-list ui-list-text">
		{foreach $rs as $room}
		<li class="ui-border-t hys" rel="{$room.mr_id}">
			<div class="ui-list-info">
				<h4 class="ui-nowrap">{$room.mr_name}</h4>
				<p>
					{if $room.mr_galleryful}<span class="ui-icon people" style="height:20px;width:100%;overflow:hidden;word-break:keep-all;white-space:nowrap;">{$room.mr_galleryful}人</span>{/if}
					{if $room.mr_device}<span class="ui-icon device" style="height: 20px;width:100%;overflow:hidden;word-break:keep-all;white-space:nowrap;">{$room.mr_device}</span>{/if}
				</p>
			</div>
		</li>
		{/foreach}
	</ul>
{/foreach}

{if 3 <= count($rooms)}
<div class="ui-navbar-right">  
	<label class="ui-icon">楼层</label>
	{foreach $rooms as $i => $rs}
	<a href="#{$i}">{$i}</a>
	{/foreach}
</div>
{/if}

<script>
var post = {$post};	//第一步传过来的post
require(["zepto"], function($) {
	$('li.hys').click(function (){
		var id = $(this).attr('rel');
		location.href = '?step=3&id='+id+'&date='+post.date+'&length='+post.length+'&time='+post.time;
	});
});
</script>
{include file='mobile/footer.tpl'}