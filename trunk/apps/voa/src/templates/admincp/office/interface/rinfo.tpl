{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title font12"><strong>{$interface['name']|escape}：{$interface['cp_name']|escape}</strong></h3>
    </div>
    <div class="panel-body">
        <dl class="dl-horizontal font12 vcy-dl-list" style="margin-bottom:0">
            <dt>应用名称：</dt>
            <dd>
                <strong class="label label-primary font12">{$interface['name']|escape}</strong>
            </dd>
            <dt>接口名称：</dt>
            <dd>{$interface['cp_name']}</dd>
            <dt>流程名：</dt>
            <dd>{$interface['f_name']|escape}</dd>
            <dt>流程描述：</dt>
            <dd>{$interface['f_desc']|escape}</dd>
            <dt>请求：</dt>
            <dd>{$interface['method']|escape}</dd>
            <dt>请求参数：</dt>
            <dd>{$interface['_params']|escape}</dd>
            <dt>返回参数：</dt>
            <dd>{$interface['_msg']|escape}</dd>
        </dl>
    </div>
</div>

{include file="$tpl_dir_base/footer.tpl"}