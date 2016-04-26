{include file="$tpl_dir_base/header.tpl"}
<style>
#regTable {
	width: 400px;
	margin: 0 auto;
	font-size: 16px;
}
#regTable td{
	height: 36px;
	text-align: left;
}
#regTable td input {
	width: 300px;
}
#regTable td.right{
	height: 32px;
	text-align: right;
}
.padding-sm h3{
	text-align: center;
}
#content {
	text-align: center;
}
#order td {
	height: 32px;
}
</style>
<div class="stat-panel">
    <div class="stat-row">
        <!-- Small horizontal padding, bordered, without right border, top aligned text -->
        <div class="stat-cell col-sm-9 padding-sm-hr bordered no-border-r valign-top">
            <!-- Small padding, without top padding, extra small horizontal padding -->
            <h4 class="padding-sm no-padding-t padding-xs-hr text-center">{$act['subject']|escape}</h4>
            <!-- Without margin -->
            <div class="text-default" style="text-align:center;">
	            <span>活动类型：&nbsp;{$act['catname']}<span class="space"></span>
	            活动截止时间：{$act['_overtime']}<span class="space"></span>
	            分享：{$act['share']}<span class="space"></span>
	            浏览量：{$act['hits']}
	            </span>
            </div>
            <hr>
            <div class="padding-sm">
                <div id="content">{$act.content}</div>
                <h3>报名表</h3>
				<table id="regTable">
					<tr>
						<td class="right">姓名：</td>
						<td><input class="form-control" type="text"/></td>
					</tr>
					<tr>
						<td class="right">手机号：</td>
						<td><input class="form-control" type="text"/></td>
					</tr>
				</table>
            </div>
        </div> <!-- /.stat-cell -->
        <!-- Primary background, small padding, vertically centered text -->
        <div class="stat-cell col-sm-3 bordered padding-sm">
            <div id="hero-graph" class="graph text-info" ><h4 class="fa fa-list"> 签到排行</h4></div>
        	<table id="order">
        		{foreach $sign_order as $k => $v}
        		<tr>
        			<td>{$v._sale}　</td>
        			<td>报名人数 {$v.regs}　</td>
        			<td>签到人数 {$v.signs}　</td>
        		</tr>
        		{/foreach}
        	</table>
        </div>
    </div>
</div>




{include file="$tpl_dir_base/footer.tpl"}