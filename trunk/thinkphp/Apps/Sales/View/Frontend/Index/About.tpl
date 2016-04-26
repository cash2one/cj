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
<hr/>
<form name="add_cg" id="add_cg" action="{$Business}" method="post">
    商机名称
    <input type="text" name="sb_name" /><br><br>
    客户ID
    <input type="text" name="sc_id" value="1"/>
    客户进展
    <input type="text" name="sb_type" value="1"/>
    预计金额
    <input type="text" name="sb_amount" value="100"/>
    备注
    <input type="text" name="sb_comments"/>
    <br><br>
    <!--<a href="http://www.w3school.com.cn">选择群成员</a><br><br><br><br>-->
    <input type="submit" value="添加商机" name="add_cg_sbt" id="add_cg_sbt" />
</form>
<hr/>
编辑商机
<form name="add_cg" id="add_cg" action="{$Edit_Business}" method="post">
    商机ID
    <input type="text" name="sb_id"  value="1" /><br><br>
    商机名称
    <input type="text" name="sb_name" /><br><br>
    客户进展
    <input type="text" name="sb_type" value="1"/><br><br>
    预计金额
    <input type="text" name="sb_amount" value="100"/><br><br>
    备注
    <input type="text" name="sb_comments"/>
    <br><br>
    <!--<a href="http://www.w3school.com.cn">选择群成员</a><br><br><br><br>-->
    <input type="submit" value="编辑商机" name="add_cg_sbt" id="add_cg_sbt" />
</form>
<hr/>
新增轨迹
<form name="add_cg" id="add_cg" action="{$Add_track}" method="post">
    客户ID
    <input type="text" name="sc_id" value="1"/><br><br>
    客户进展
    <input type="text" name="st_type" value="1"/><br><br>
    地址
    <input type="text" name="st_address" value="不知道是哪里"/><br><br>
    备注
    <input type="text" name="st_content"/>
    附件
    <input type="text" name="at_ids[]"/>
    <input type="text" name="at_ids[]"/>
    <input type="text" name="at_ids[]"/>


    <br><br>
    <!--<a href="http://www.w3school.com.cn">选择群成员</a><br><br><br><br>-->
    <input type="submit" value="新增轨迹" name="add_cg_sbt" id="add_cg_sbt" />
</form>
<hr/>
商机列表查询
<form name="add_cg" id="add_cg" action="{$List_Business}" method="get">
    客户状态
    <input type="text" name="sb_type" value="1"/><br><br>
    成交开始时间
    <input type="text" name="st_type" value="1438658161"/><br><br>
    成交结束时间
    <input type="text" name="st_type" value="1438658161"/><br><br>
    客户名称
    <input type="text" name="sc_name" value="傻"/><br><br>
    负责人ID集合
    <input type="text" name="m_uids[]"/>
    <input type="text" name="m_uids[]"/>
    <input type="text" name="m_uids[]"/>
    排序类型（默认什么排序 1：签单可能性 2：销售金额 3：跟进时间）
    <input type="text" name="orderby"/>


    <br><br>
    <!--<a href="http://www.w3school.com.cn">选择群成员</a><br><br><br><br>-->
    <input type="submit" value="商机列表查询" name="add_cg_sbt" id="add_cg_sbt" />
</form>

<include file="Common@Frontend:Footer" />