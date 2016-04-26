{include file='mobile/header.tpl' navtitle="活动中心"}

<link rel="stylesheet" type="text/css" href="/misc/styles/oa_ght.css">
	
	<ul class="ui-list ui-border-tb ui-list-ght ui-list-text">
		<li class="ui-border-t ui-form-item-link">
			<a>
				<div class="ui-list-info">
					<h4>所属活动</h4>
				</div>
				<div id="actidValue" class="ui-list-action">全部</div>
				<select id="actid">
					<option value="0">全部</option>
				</select>
			</a>
		</li>
	</ul>
    <div class="ui-txt-muted">我的业绩</div>
    <div class="ui-form">
        <div class="ui-form-item ui-form-item-order ui-border-b">
            <label>分 享 数</label>
            <p id="share"></p>
        </div>

        <div class="ui-form-item ui-form-item-order ui-border-b">
            <label>被阅读数</label>
            <p id="hits"></p>
        </div>
        <div class="ui-form-item ui-form-item-order ui-border-b">
            <label>报名人数</label>
            <p id="regs"></p>
        </div>
        <div class="ui-form-item ui-form-item-order">
            <label>签到人数</label>
            <p id="signs"></p>
        </div>
    </div>
	
<script type="text/javascript">
{literal}
require(["zepto", "underscore", "showlist"], function($, _, showlist) {
	
	//加载活动select
	$.getJSON('/api/campaign/get/simplelist', function (json){
		for(k in json.result)
		{
			$('#actid option').eq(0).after('<option value="'+k+'">'+json.result[k]+'</option>');
		}
	});
	
	//根据活动id加载业绩
	$('#actid').change(function (){
		$('#actidValue').text($('#actid option:checked').text());
		load();
	}).change();
});
function load()
{
	//加载数据
	$.getJSON('/api/campaign/get/total', {actid: $('#actid').val()}, function (json){
		if(json.errcode == 0) {
			$('#share').text(json.result.share);
			$('#hits').text(json.result.hits);
			$('#regs').text(json.result.regs);
			$('#signs').text(json.result.signs);
		}else{
			$.tips({content: '加载数据失败:'+json.errmsg});
		}
	});
}

{/literal}
</script>

{include file='mobile/footer.tpl'}