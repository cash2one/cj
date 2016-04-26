<!-- 模板列表 -->
{include file="$tpl_dir_base/header.tpl" }
<style>
	.stat-panel{ cursor:pointer }
	.temp-defined{
		position:relative;
	}
	.temp-defined .temp-img{
		font-size: 64px;
		color:#dcdcdc;
	}
	.temp-defined .temp-text{
		height:12px;
		line-height: 12px;
		font-size: 12px;
		margin-bottom: 10px;
		margin-top: 10px;
		color:#565656;
	}
	.temp-more:before, .temp-more:after{
		height: 52px;
		right: 0;
		top: 0;
		z-index: 9;
		display: block;
	}
	.temp-more:before{
		content:"";
		width:0;
		height:0;
		display:block;
		position:absolute;
		z-index:2;
		border-top: 52px solid #50beda;
		border-left: 52px solid transparent;
	}
	.temp-more:after{
		position: absolute;
		width: 52px;
		text-align: right;
		line-height: 26px;
		font-size: 12px;
		color: #fff;
		padding-right: 5px;
		content: '多条';
	}
</style>
<!--拷贝这里的内容-->
<div class="row">
	<h2 class="col-xs-12 text-center text-left-sm">自主新建</h2>
	<div class="col-xs-3">
		<a href="{$add_url}">
			<div class="temp-defined text-center bordered">
				<div class="temp-img"><i class="fa fa-plus"></i></div>
				<div class="temp-text">添加单条</div>
			</div>
		</a>
	</div>
	<div class="col-xs-3">
		<a href="{$madd_url}">
			<div class="temp-defined text-center bordered temp-more">
				<div class="temp-img"><i class="fa fa-plus"></i></div>
				<div class="temp-text">添加多条</div>
			</div>
		</a>
	</div>
	<h2 class="col-xs-12 text-center text-left-sm">选择模板</h2>
	{if !empty($list)}
		{foreach $list as $val}
		<div class="col-xs-4">
			<div class="stat-panel text-center bordered" data-href="{$tem_add}{$val.ne_id}">
				<div class="stat-row">
					<!-- Dark gray background, small padding, extra small text, semibold text -->
					<div class="stat-cell padding-sm text-left" style="word-wrap:break-word;word-break:break-all;">
						<!-- Big text -->
						<div class="text-bg" style="height:22px;overflow:hidden;"><strong>{$val.title}</strong></div>
						<!-- Extra small text -->
						<span class="text-xs text-muted">时间</span>
					</div>
				</div> <!-- /.stat-row -->
				<div class="stat-row bg-info">
					<!-- Bordered, without top border, without horizontal padding -->
					<div class="stat-cell panel-padding" style="word-wrap:break-word;word-break:break-all;">
						<i class="fa {$val['icon']} text-slg" style="position:absolute;left:50%;margin-left:-70px;;"></i><div class="pull-left text-left"  style="margin-left:50%;"><h4 class="padding-sm no-padding padding-xs-hr text-left" style="height:18px;overflow:hidden;">{$val.title}</h4><p>ANNOUNCEMENT</p></div>

					</div>
				</div>
				<div class="padding-sm text-left">
					<p class="text-xs text-muted" style="height:16px; overflow:hidden">{$val.summary}</p>
					<div class="text-xs text-muted"><a href="{$tem_add}{$val.ne_id}">查看全文</a></div>
				</div>
			</div>
		</div>

		{/foreach}
	{/if}
</div>
<!--拷贝这里的内容结束-->
{literal}
<script type="text/javascript">
	$(function(){
		$('.stat-panel').on('click',function(){
			var href = $(this).data('href');
			window.location.href = href;
		})
	})
</script>
{/literal}
{include file="$tpl_dir_base/footer.tpl"}