<include file="Common@Frontend:Header" />

<script>
    var groupuserIds=new Array();
    groupuserIds[0]="502";
    groupuserIds[1]="501";
    groupuserIds[1]="500";
</script>
<br /><br />
<table id="cutomer_list">
    <foreach name="list" item="v">
        <tr>
            <!--<td>'客户ID:'{$v['sc_id']}&nbsp;</td>-->
            <td>'客户名称(全称):'{$v['sc_name']}&nbsp;</td>
            <td>'简称:'{$v['sc_short_name']}&nbsp;</td>
            <td>'地址:'{$v['sc_address']}&nbsp;</td>
            <td>'联系人:'{$v['sc_contacter']}&nbsp;</td>
            <td>'联系方式:'{$v['sc_phone']}&nbsp;</td>
            <td>'创建时间:'{$v['_created']}</td>
        </tr>
    </foreach>
</table>
{$multi}
<br />

<form name="add_customer" id="add_customer" action="{$acurl}" method="post">
    客户名称（全称）
    <input type="text" name="sc_name" value="中国南翔"/><br><br>
    简称
    <input type="text" name="sc_short_name" value="南翔"/><br><br>
    地址
    <input type="text" name="sc_address" value="陕西省雁塔区"/><br><br>
    联系人
    <input type="text" name="sc_contacter" value="李磊"/><br><br>
    联系方式
    <input type="text" name="sc_contacter" value="13800138000"/><br><br>
    
    <input type="submit" value="添加客户" name="add_ct_sbt" id="add_ct_sbt" />
</form>
<br><br><br>
<form name="quit_cg" id="quit_cg" action="{$quiturl}" method="post">
    群 组 ID
    <input type="text" name="cg_id"/><br><br>
    群成员ID
    <input type="text" name="m_uid" /><br><br>
    <input type="submit" value="退出群聊" name="quit_cg_sbt" id="quit_cg_sbt" />
</form>

<hr />

<form name="send_msg" id="send_msg" action="{$sendMsgurl}" method="post">
    群 组 ID
    <input type="text" name="group_id"/><br><br>
    聊天内容
    <input type="text" name="chat_content" /><br><br>
    附件ID
    <input type="text" name="chat_attachment" /><br><br>
    <input type="submit" value="发送消息" name="send_msg_sbt" id="send_msg_sbt" />
</form>

<hr />

<form name="edit_cg" id="edit_cg" action="{$editurl}" method="post">
    群 组 ID
    <input type="text" name="group_id"/><br><br>
    群名称
    <input type="text" name="group_name"/><br><br>
    新成员ID
    <input type="text" name="new_uids[]" /><br><br>
    新成员ID
    <input type="text" name="new_uids[]" /><br><br>
    移除成员ID
    <input type="text" name="del_uids[]" /><br><br>
    移除成员ID
    <input type="text" name="del_uids[]" /><br><br>

    <input type="submit" value="编辑群组" name="edit_sbt" id="edit_sbt" />
</form>

<hr />

<form name="get_msg" id="get_msg" action="{$getMsgurl}" method="post">
    群 组 ID
    <input type="text" name="group_id"/><br><br>
    获取数量
    <input type="text" name="limit" /><br><br>
    最大消息ID
    <input type="text" name="max_record_id" /><br><br>
    最小消息ID
    <input type="text" name="min_record_id" /><br><br>
    <input type="submit" value="获得聊天信息" name="quit_cg_sbt" id="quit_cg_sbt" />
</form>
<script>
    // 监听提交按钮 click 事件
    $("#add_ct_sbt").on('click', function(e) {
        var frm = $("#add_customer");
        // ajax 提交
        $.post(frm.attr('action'), frm.serialize(), function(data) {
            // 群组成功
            if (0 == data.errcode) {
             <!--   $("#gb_list").append("<tr>\
			<td>'群组名称:'"+data.result.cg_name+"</td>\
			<td>'群聊单聊:'"+data.result._type+"</td>\
			<td>'创建人姓名:'"+data.result.m_username+"</td>\
			<td>'创建时间:'"+data.result._created+"</td>\
			</tr>");
                $("#message").val('');
                --->
                alert('新增群组成功');
            } else { // 群组失败
                alert(data.errmsg);
            }
        });

        return false;
    });

    // 监听提交按钮 click 事件
    $("#quit_cg_sbt").on('click', function(e) {
        var frm = $("#quit_cg");
        // ajax 提交
        $.post(frm.attr('action'), frm.serialize(), function(data) {
            // 退出群组成功
            if (0 == data.errcode) {
                $("#gb_list").append("<tr><td>"+data.result.username+"</td><td>"+data.result._created+"</td><td>"+data.result.message+"</td></tr>");
                $("#message").val('');
                alert('退出群组成功');
            } else { // 退出群组失败
                alert(data.errmsg);
            }
        });

        return false;
    });
</script>

<include file="Common@Frontend:Footer" />
