{include file='cyadmin/header.tpl'}
<div class="panel panel-default">
    <div class="panel-body">

   <form   class="form-horizontal" action="" method="post">
    <div class="form-group ">
        <label class="col-sm-2 control-label">支付金额：</label>
        <div class="col-sm-2">
            <input type="text" name="profile[ep_name]" value="{$condi['profile']['ep_name']}" placeholder="" class="input-sm form-control">
            <span class="help-block"></span>
        </div>
        <label class="col-sm-2 control-label">元</label>
    </div>
    <div class="form-group ">
        <label class="col-sm-2 control-label">购买期限：</label>
        <div class="col-sm-2">
            <select class="input-sm form-control" name="nobeginning">
                <option value="">三个月</option>
                <option value="ep_statuswx" {if $condi['nobeginning'] == 'ep_statuswx'}selected{/if}>六个月</option>
                <option value="ep_statuswx" {if $condi['nobeginning'] == 'ep_statuswx'}selected{/if}>一年</option>
                <option value="ep_statuswx" {if $condi['nobeginning'] == 'ep_statuswx'}selected{/if}>两年</option>
                <option value="ep_statusmail" {if $condi['nobeginning'] == 'ep_statusmail'}selected{/if}>三年</option>
            </select>
            <span class="help-block"></span>
        </div>
    </div>
    <div class="form-group ">
        <label class="col-sm-2 control-label">开通时间：</label>
        <div class="col-sm-2">
            <input type="time" name="profile[ep_name]" value="{$condi['profile']['ep_name']}" placeholder="" class="input-sm form-control">
            <span class="help-block"></span>
        </div>
    </div>
    <div class="form-group ">
        <label class="col-sm-2 control-label">到期时间：</label>
        <div class="col-sm-2">
            <input type="data" name="profile[ep_name]" placeholder="" class="input-sm form-control">
            <span class="help-block"></span>
        </div>
    </div>
    <div class="form-group ">
        <label class="col-sm-2 control-label">购买空间：</label>
        <div class="col-sm-2">
            <input type="text" name="profile[ep_name]" value="{$condi['profile']['ep_name']}" placeholder="" class="input-sm form-control">
            <span class="help-block"></span>
        </div>
        <label class="col-sm-2 control-label">GB</label>
    </div>

    </form>
</div>
</div>

{include file='cyadmin/footer.tpl'}