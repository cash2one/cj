{include file="$tpl_dir_base/header.tpl"}

<form id="form-servicetype" class="form-horizontal font12" role="form" method="post" action="{$application_update_submit_url}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="panel panel-danger">
		<div class="panel-heading"><strong style="font-size:16px;">删除 “<span style="color:#000">{$plugin['cp_name']|escape}</span>” 应用</strong></div>
		<div class="panel-body">
		
		删除该应用后将无法发送消息，也不能在微信中访问和使用该应用，同时该应用的相关数据也会<strong class="text-danger">被删除</strong>，本操作不可恢复，请慎重处理。
	<!--
	<h5><strong class="font12">请登录微信企业平台（qy.weixin.qq.com）的“应用中心”，选择对应的应用“{$plugin['cp_name']|escape}”，“删除”该应用，<a href="{$IMGDIR}help/delete_01.png" target="_blank" title="点击查看"><strong><i class="fa fa-external-link"></i> 如图所示 <i class="fa fa-image"></i></strong></a>。</strong></h5>
	-->
		<p>&nbsp;</p>
		<div class="form-group">
			<div class="col-sm-offset-3 col-sm-8">
				<button type="submit" class="btn btn-danger">确定删除</button>
				<span class="space"></span><span class="space"></span>
				<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
			</div>
		</div>
		</div>
	</div>
</form>


{include file="$tpl_dir_base/footer.tpl"}