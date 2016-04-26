{include file="$tpl_dir_base/header.tpl"}
<div class="row" id="superreport_template">
	<div class="col-md-6">
		<ul class="list-group">
			<li class="list-group-item">选择一个初始报表做为设计模版</li>
			<li class="list-group-item text-center">
				<button class="btn btn-info" id="direct_to_add">创建空白报表</button>
			</li>
{if $templates}
			<li class="list-group-item">你可以根据使用场景，选择适用的报表模版</li>
			<li class="list-group-item">
				<ul class="nav nav-pills nav-stacked">
				{foreach $templates as $k => $template}
					<li class="{if $k == 0} active {/if}" stc_id="{$template['stc_id']}">
						<a href="#panel-{$template['stc_id']}" data-toggle="tab" class="btn inspect-tab btn-outline">{$template['title']}</a>
					</li>
				{/foreach}	
				</ul>

			</li>
{/if}			
		</ul>
	</div>
	<div class="col-md-6">
		<div class="stat-panel">
			<div class="stat-row">
				<!-- Bordered, without right border, top aligned text -->
				<div class="stat-cell col-sm-4 bordered padding-sm-hr valign-top">
					<div class="tab-content">
{if $templates}		
	{foreach $templates as $key => $val}			
						<div class="tab-pane {if $key == 0} active {/if}" id="panel-{$val['stc_id']}">
							<h4 class="padding-sm no-padding-t padding-xs-hr text-center"> <i class="fa fa-bar-chart-o text-primary"></i>
								&nbsp;&nbsp;{$val['templates']['title']}
							</h4>
		{if $val['templates']['content']}					
							<ul class="list-group no-margin">
			{foreach $val['templates']['content'] as $content}				
								<li class="list-group-item no-border-hr no-border-b padding-xs-hr no-border">
									{$content['fieldname']}
									<span class="pull-right">{$content['unit']}</span>
								</li>
			{/foreach}					
							</ul>
		{/if}
						</div>
	{/foreach}
						<div class="tab-pane" id="panel-myself">
{if $fields}
							<h4 class="padding-sm no-padding-t padding-xs-hr text-center"> <i class="fa fa-bar-chart-o text-primary"></i>
								&nbsp;&nbsp;销售日报表
							</h4>
							<ul class="list-group no-margin">
			{foreach $fields as $field}				
								<li class="list-group-item no-border-hr no-border-b padding-xs-hr no-border">
									{$field['fieldname']}
									<span class="pull-right">{$field['unit']}</span>
								</li>
			{/foreach}					
							</ul>
{/if}
						</div>
{/if}						
					</div>
					<div class="text-center"><a class="btn" id="select_template">选用此模板</a></div>
				</div>

			</div>
		</div>
	</div>
</div>
<!--在此制作超级报表的模板end-->
<script type="text/javascript">
$(function(){
	//点击创建空白报表
	$('#direct_to_add').bind('click',function(){
		$('#panel-myself').addClass('active').siblings().removeClass('active');
		$('.nav-stacked').children().removeClass('active');
	});
	//点击选用此模板
	$('#select_template').bind('click',function(){
		var stc_id = $('.nav-stacked').find('.active').attr('stc_id');
		if (stc_id == undefined && $('#panel-myself').hasClass('active')) {
			stc_id = 0;
		}
		window.location.href = '{$add_url}?stc_id='+stc_id;
	});
})
	
</script>
{include file="$tpl_dir_base/footer.tpl"}