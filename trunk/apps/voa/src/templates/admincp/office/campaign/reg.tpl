{include file="$tpl_dir_base/header.tpl"}
<div class="table-light">
<div id="down">
<label for="excel" class="col-sm-2 control-label"><button class="btn btn-info" type="button" id="download">导出表格</button></label>
</div>
<form class="form-horizontal" role="form" method="post" action="javascript:;">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-bordered table-hover font12">
    <colgroup>
        <col class="t-col-8" />
        <col class="t-col-10" />
        <col class="t-col-10" />
        <col class="t-col-8" />
        <col class="t-col-8" />
    </colgroup>
    <thead>
        <tr>
            <th>姓名</th>
            <th>报名时间</th>
            <th>手机号</th>
            <th>状态</th>
            <th>自定义</th>
        </tr>
    </thead>
    {if $total > 0}
    <tfoot>
        <tr>
            <td colspan="5" class="text-right vcy-page">{$multi}</td>
        </tr>
    </tfoot>
{/if}
{if $act == null}
<tr>
            <td colspan="5">该活动没有报名数据</td>
</tr>
{else}
    <tbody>
{foreach $act as $_id=>$_data}
        <tr>
            <td>{$_data['name']}</td>
            <td>{$_data['created']}</td>
            <td>{$_data['mobile']}</td>
            <td>{if $_data['is_sign'] == 1}已签到{else}未签到{/if}</td>
            <td>{foreach $_data['custom'] as $k => $v}
                    {$v['name']}-->{$v['value']}<br>
                {/foreach}</td>
        </tr>
{/foreach}
    </tbody>
{/if}
</table>
</form>
</div>
<style>
#down{
    width:1000px;
    height:50px;
    float:rignt;
}
#download{
    margin-left:900px;
}
</style>
{literal}
<script>
var href = window.location.href;
var down = $('#download');
down.on('click',function(){
    window.location = href+'&ac=down';
});
</script>
{/literal}
{include file="$tpl_dir_base/footer.tpl"}