<include file="Common@Frontend:Header" />

<br /><br />
<table id="gb_list">
<foreach name="list" item="v">
<tr>
	<td>{$v['username']}</td>
	<td>{$v['_created']}</td>
	<td>{$v['message']}</td>
</tr>
</foreach>
</table>
{$multi}
<br />

<form name="add_gb" id="add_gb" action="{$acurl}" method="post">
<textarea name="message" id="message"></textarea>
<input type="submit" value="提交" name="gb_sbt" id="gb_sbt" />
</form>

<script>
// 监听提交按钮 click 事件
$("#gb_sbt").on('click', function(e) {
	
	var frm = $("#add_gb");
	// ajax 提交
	$.post(frm.attr('action'), frm.serialize(), function(data) {
		
		// 留言成功
		if (0 == data.errcode) {
			$("#gb_list").append("<tr><td>"+data.result.username+"</td><td>"+data.result._created+"</td><td>"+data.result.message+"</td></tr>");
			$("#message").val('');
			alert('新增留言成功');
		} else { // 留言失败
			alert(data.errmsg);
		}
	});
	
	return false;
});

// 读取列表
$(document).ready(function(e) {
	
	$.get('{$listurl}', function(data) {
		
		// 如果出错了
		if (0 < data.errcode) {
			alert(data.errmsg);
			return false;
		}
		
		var trs = '';
		var list = data['result']['list'];
		for (var k in list) {
			trs += "<tr><td>"+list[k].username+"</td><td>"+list[k]._created+"</td><td>"+list[k].message+"</td></tr>";
		}
		
		if ('' == trs) {
			alert('还没有任何留言');
		} else {
			$('#gb_list').append(trs);
		}
		
		return true;
	});
});
</script>

<include file="Common@Frontend:Footer" />
