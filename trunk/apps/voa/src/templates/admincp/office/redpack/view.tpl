{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title font12"><strong>{$redpack['wishing']|escape}</strong></h3>
    </div>
    <div class="panel-body">
        <dl class="dl-horizontal font12 vcy-dl-list" style="margin-bottom:0">
            <dt>发起人：</dt>
            <dd>{$redpack['m_username']|escape}&nbsp;&nbsp;<abbr title="发起时间">{$redpack['_created']}</abbr></dd>
            <dt>分配方式</dt>
            <dd>{$redpack['_type']}</dd>
            <dt>总金额：</dt>
            <dd>{$redpack['_total']}元</dd>
            <dt>已领取金额：</dt>
            <dd>{$redpack['_left']}元</dd>
            <dt>总个数：</dt>
            <dd>{$redpack['redpacks']}</dd>
            <dt>已被领取个数：</dt>
            <dd>{$redpack['times']}</dd>
        </dl>
    </div>
</div>

<table class="table table-striped table-hover table-bordered font12 table-light">
    <colgroup>
        <col class="t-col-6" />
        <col class="t-col-10" />
        <col class="t-col-12" />
        <col class="t-col-18" />
        <col />
    </colgroup>
    <thead>
    {if !empty($multi)}
    <tfoot>
    <tr>
        <td colspan="5" class="text-right vcy-page">{$multi}</td>
    </tr>
    </tfoot>
    {/if}
    <tr>
        <th>领取人</th>
        <th>金额(单位:元)</th>
        <th>是否已发送</th>
        <th>IP</th>
        <th>操作时间</th>
    </tr>
    </thead>
    <tbody>
    {foreach $rplist as $_id => $_v}
    <tr>
        <td>{$_v['m_username']|escape}</td>
        <td>{$_v['_money']}</td>
        <td>{$_v['_sendst']}</td>
        <td>{$_v['ip']}</td>
        <td>{$_v['_created']}</td>
    </tr>
    {foreachelse}
    <tr class="warning">
        <td colspan="5">该红包暂时还未有人员领取记录</td>
    </tr>
    {/foreach}
    </tbody>
</table>


{include file="$tpl_dir_base/footer.tpl"}