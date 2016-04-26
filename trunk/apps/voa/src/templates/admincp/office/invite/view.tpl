{include file="$tpl_dir_base/header.tpl"}

	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title font12"><strong>基本信息</strong></h3>
		</div>
		<div class="panel-body">
		<dl class="dl-horizontal font12 vcy-dl-list" style="margin-bottom:0">
			<dt>姓名：</dt>
			<dd>{$result['name']|escape} &nbsp;&nbsp;
			<strong class="label label-primary font12">{$result['gz_state']}</strong></dd>
			<dt>性别：</dt>
			<dd>{$result['gender']|escape}</dd>
			<dt>手机：</dt>
			<dd>{$result['phone']|escape}</dd>
			<dt>微信号：</dt>
			<dd>{$result['weixin_id']|escape}</dd>
			<dt>邮箱：</dt>
			<dd>{$result['email']|escape}</dd>
			<dt>邀请人：</dt>
			<dd><strong class="label label-primary font12" style="background-color:blue ;">{$result['invite_uid']|escape}</strong></dd>
			<dt>申请时间：</dt>
			<dd><strong class="label label-primary font12" style="background-color:Grey;">{$result['created']|escape}</strong></dd>
			{if $result['approval_state'] != "无需核审"}
			<dt>审批状态：</dt>
			<dd>{$result['approval_state']|escape}</dd>
			{/if }
			{if $result['custom'] != ''}
			<!-- <dt>自定义信息：</dt> -->
			{foreach $result['custom'] as $k => $v}
				<dt>{$v[1]}:</dt>
				<dd>{$v[0]}</dd>
			{/foreach}
			{/if}
		</dl>
		</div>
		
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-9">
			<div class="row">
				<div class="col-md-4" ><a href="javascript:history.go(-1);" class="btn btn-info form-small form-small-btn margin-left-12" style="background-color:blue ;">返回</a></div>
			</div>
		</div>
	</div>
	
{include file="$tpl_dir_base/footer.tpl"}