{include file="$tpl_dir_base/header.tpl"}

<style type="text/css">

.datepicker-orient-top {
	z-index: 999999 !important;
}

.timepicker-orient-top {
	z-index: 999999 !important;
}

.fi_tr {
	background: #cccccc;
	line-height: 60px;
	height: 50px;
}

.me_title {
	font: 18px Î¢ÈíÑÅºÚ;
	text-align: center;
	color: #FFFFFF;
}
</style>

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title font12"><strong>消息详情</strong></h3>
	</div>
	<div class="panel-body">
		<form class="form-horizontal font12" role="form" id="edit-form"  method="post" action="{$formActionUrl}">
		<table class="table" id="project">

		</table>

		</form>
	</div>
</div>


<script>
	$(function(){

		$('#project').html(txTpl('tpl-list', {$return}));
	});
</script>
{literal}
<script type="text/template" id="tpl-list">


		<tr class = "fi_tr">
					<td colspan="3" class = "me_title"><%=data['title']%></td>
				</tr>
				<tr>

					<td	colspan="3" style="word-wrap:break-word;word-break:break-all;text-align:left;padding:5px 10px;">
						<div><%=data['content']%></div>
					
					</td>

				</tr>
				<% if(data['imgurl'] != null){%>
				<tr>
					<td colspan=3><img src="<%=data['imgurl']%>" width=540></td>
				</tr>
				<% }%>
				<tr>
					<td></td>
					<td></td>
					<td><span>作者：&nbsp;&nbsp;</span><span class="label label-success"><%=data['author']%></span> </td>
				</tr>
				
				<tr style="padding-top:15px;">
					<td></td>
					<td></td>
					<td  class="col-sm-1 text-center" style="position:relative;top:10px;width:235px;"><span>发送日期：&nbsp;&nbsp;</span><span class="label label-danger"><%=data['_created']%></span></td>
				</tr>
				<tr>
					{/literal}
					<td colspan="3" class="text-center" style="position:relative;top:20px;"><a href="{$list_url}" role="button" class="btn btn-primary">返回</a></td>
				</tr>

</script>
{include file="$tpl_dir_base/footer.tpl"}
