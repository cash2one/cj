<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title></title>
	<script type="text/javascript" src="{$JSDIR}jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="/misc/ueditor/dialogs/internal.js"></script>
</head>
<body>
	{literal}
	<style>
		table{width: 100%}
		td,th{padding: 6px!important;text-align:center;border-bottom:1px solid #f5f5f5; }
		.padding-20{padding: 20px;}
	</style>
	{/literal}
	<div class="padding-20">
	<table>
		<tr>
			<th width="30">ID</th>
			<th>产品名称</th>
			<th>价格</th>
			<th>创建时间</th>
		</tr>
		{foreach $list as $_v}
		<tr data-id="{$_v['dataid']}" style="cursor: pointer;">
			<td>{$_v['dataid']}</td>
			<td>{$_v['subject']}</td>
			<td>{$_v['price']}</td>
			<td>{rgmdate($_v['created'])}</td>
		</tr>
		{/foreach}
	</table>
	</div>
	<script type="text/javascript">
$('tr').on('click', function(e) {
	var id = parseInt($(this).data("id"));
	if (isNaN(id) || 1 > id) {
		return true;
	}
	
	editor.execCommand('link', {
	    href: '/frontend/travel/index?dataid=' + id,
	    target: '_blank'
	});
});
</script>
</body>
</html>