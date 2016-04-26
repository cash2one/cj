<include file="Common@Frontend:Header" />
<script>
    var groupuserIds=new Array();
    groupuserIds[0]="502";
    groupuserIds[1]="501";
    groupuserIds[1]="500";
</script>

<form name="add_customer" id="add_customer" action="{$dcurl}" method="get">
    <br /><br />
    <label><input name="sc_ids[]" type="checkbox" value="1" />客户1 </label>
    <label><input name="sc_ids[]" type="checkbox" value="2" />客户2 </label>
    <label><input name="sc_ids[]" type="checkbox" value="3" />客户3 </label>
    <label><input name="sc_ids[]" type="checkbox" value="4" />客户4 </label><br><br>
    <input type="submit" value="删除" name="quit_cg_sbt" id="quit_cg_sbt" />
</form>
<br><br><br>
<hr />
<form name="quit_cg" id="quit_cg" action="{$bcurl}" method="get">
    客户来源
    <input type="text" name="sc_source"/><br><br>
    销售阶段
    <input type="text" name="st_type" /><br><br>
    销售人
    <input type="text" name="m_uid" /><br><br>
    <input type="submit" value="轨迹查询" name="quit_cg_sbt" id="quit_cg_sbt" />
</form>
<br><br><br>
<hr />

<form name="send_msg" id="send_msg" action="{$ccurl}" method="post">
    轨迹 ID
    <input type="text" name="st_id" /><br><br>
    客户 ID
    <input type="text" name="sc_id"/><br><br>
    工作日报
    <input type="text" name="st_content" /><br><br>
    地址
    <input type="text" name="st_address" /><br><br>
    客户状态
    <input type="text" name="st_type"/><br><br>
    附件
    <input type="text" name="ad_its"/><br><br>
    <input type="submit" value="编辑轨迹" name="send_msg_sbt" id="send_msg_sbt" />
</form>
<br><br><br>
<hr />

<form name="edit_cg" id="edit_cg" action="{$acurl}" method="get">
    类型配置列表（客户来源、客户状态、操作类型...
    <input type="text" name="stp_type"/><br><br>

    <input type="submit" value="查询" name="edit_sbt" id="edit_sbt" />
</form>
<br><br><br>
<hr />
<br><br><br>
<include file="Common@Frontend:Footer" />
