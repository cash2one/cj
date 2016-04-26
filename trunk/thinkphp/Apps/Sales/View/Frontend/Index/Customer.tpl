<include file="Common@Frontend:Header" />

<form name="add_customer" id="add_customer" action="{$add_customer_curl}" method="post">
    客户名称（全称）
    <input type="text" name="sc_name" value="中国南翔"/><br><br>
    简称
    <input type="text" name="sc_short_name" value="南翔"/><br><br>
    地址
    <input type="text" name="sc_address" value="陕西省雁塔区"/><br><br>
    联系人
    <input type="text" name="sc_contacter" value="李磊"/><br><br>
    联系方式
    <input type="text" name="sc_phone" value="13800138000"/><br><br>
    
    <input type="submit" value="添加客户" name="add_ct_sbt" id="add_ct_sbt" />
</form>

<form name="edit_customer" id="edit_customer" action="{$edit_customer_curl}" method="post">
    客户ID
    <input type="text" name="sc_id" value="2"/><br><br>
    客户名称（全称）
    <input type="text" name="sc_name" value="中国南翔2"/><br><br>
    简称
    <input type="text" name="sc_short_name" value="南翔"/><br><br>
    地址
    <input type="text" name="sc_address" value="陕西省雁塔区"/><br><br>
    联系人
    <input type="text" name="sc_contacter" value="李磊"/><br><br>
    联系方式
    <input type="text" name="sc_phone" value="13800138000"/><br><br>
    
    <input type="submit" value="编辑客户" name="edit_ct_sbt" id="edit_ct_sbt" />
</form>

<form name="add_partner" id="add_partner" action="{$add_partner_curl}" method="post">
    客户ID
    <input type="text" name="sc_id" value="2"/><br><br>
    联合跟进人ID
    <input type="text" name="m_uids[]" value="523"/><br><br>
    联合跟进人ID
    <input type="text" name="m_uids[]" value="522"/><br><br>
    <input type="submit" value="添加联合跟进人" name="add_partner_sbt" id="add_partner_sbt" />
</form>

<form name="business_detail" id="business_detail" action="{$business_detail_curl}" method="get">
    商机id
    <input type="text" name="sb_id" value="2"/><br><br>
    <input type="submit" value="获取商机详情" name="business_detail_sbt" id="business_detail_sbt" />
</form>


<form name="list_business_modify_record" id="list_business_modify_record" action="{$list_business_modify_record_url}"
      method="get">
    商机状态变更记录列表查询<br/><br/>
    商机id
    <input type="text" name="sb_id"/><br><br>
    页码
    <input type="text" name="page"/><br><br>
    每页显示个数
    <input type="text" name="limit"/><br><br>

    <input type="submit" value="确定" name="list_business_modify_record_sbt"
           id="list_business_modify_record_sbt"/>
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
    $("#edit_ct_sbt").on('click', function(e) {
        var frm = $("#edit_customer");
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
    $("#add_partner_sbt").on('click', function(e) {
        var frm = $("#add_partner");
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
    $("#business_detail_sbt").on('click', function(e) {
        var frm = $("#business_detail");
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
</script>

<include file="Common@Frontend:Footer" />
