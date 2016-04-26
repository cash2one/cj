<include file="Common@Frontend:Header"/>

<script>
    var groupuserIds = new Array();
    groupuserIds[0] = "502";
    groupuserIds[1] = "501";
    groupuserIds[1] = "500";
</script>

<form name="Change_manager" id="Change_manager" action="{$Change}" method="get">
    变更商机负责人？<br/><br/>
    商机ID
    <input type="text" name="sb_id"/><br><br>
    新的负责人ID
    <input type="text" name="m_uid"/><br><br>
    <input type="submit" value="提交" name="Change_cg_sbt" id="Change_cg_sbt"/>
</form>
<hr>

<form name="data_manager" id="data_manager" action="{$data}" method="get">
    获取数据管理<br/><br/>
    跟进人ID
    <input type="text" name="m_uid"/><br><br>
    年
    <input type="text" name="year"/><br><br>
    季度开始时间
    <input type="text" name="startdate"/><br><br>
    季度结束时间
    <input type="text" name="enddate"/><br><br>
    <input type="submit" value="获取" name="data_cg_sbt" id="data_cg_sbt"/>
</form>
<hr>

<form name="customer_manager" id="customer_manager" action="{$customer}"
      method="get">
    客户列表查询<br/><br/>
    客户来源
    <input type="text" name="sc_source"/><br><br>
    客户状态
    <input type="text" name="sc_type"/><br><br>
    客户名称（全称）
    <input type="text" name="sc_name"/><br><br>
    客户简称
    <input type="text" name="sc_short_name"/><br><br>
    跟进人（姓名）
    <input type="text" name="sc_m_username"/><br><br>
    创建开始日期
    <input type="text" name="s_created"/><br><br>
    创建结束日期
    <input type="text" name="e_created"/><br><br>
    更新开始日期
    <input type="text" name="s_updated"/><br><br>
    更新结束日期
    <input type="text" name="e_updated"/><br><br>
    销售人员m_uid数组
    <input type="text" name="m_uid"/><br><br>
    排序类型 (0 是默认排序 1是按客户名称排序 2根据时间倒序)
    <input type="text" name="sort_type"/><br><br>
    页码
    <input type="text" name="page"/><br><br>
    每页显示个数
    <input type="text" name="limit"/><br><br>

    <input type="submit" value="确定" name="customer_cg_sbt"
           id="customer_cg_sbt"/>
</form>
<hr>
<form name="info_manager" id="info_manager" action="{$info}" method="get">
    客户详情<br/><br/>
    客户id
    <input type="text" name="sc_id"/><br><br>
    <input type="submit" value="确定" name="info_cg_sbt" id="info_cg_sbt"/>
</form>