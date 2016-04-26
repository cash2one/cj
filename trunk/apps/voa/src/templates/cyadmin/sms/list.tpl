{include file='cyadmin/header.tpl'}

<div class="panel panel-default">
<div class="panel-heading">列表 <button type="button" class="close"><span class="glyphicon glyphicon glyphicon-chevron-down"></span></button></div>
  <div class="panel-body">

  <form   class="form-horizontal" action="" method="post">
  <div class="form-group ">
        
        <label class="control-label col-sm-1">手机号</label>
        <div class="col-sm-2">
            <input type="text" name="form[sms_mobile]" value="{$condi['form']['sms_mobile']}" placeholder="" class="input-sm form-control">
            <span class="help-block"></span>
        </div>
    
        <label class="control-label col-sm-1">状态</label>
        <div class="col-sm-2">
            <select class="input-sm form-control" name="form[sms_status]">
                <option value="">请选择</option> 
                <option value="1" {if $condi['form']['sms_status'] == '1'}selected{/if}>发送成功</option>
                <option value="2" {if $condi['form']['sms_status'] == '2'}selected{/if}>未成功</option>
                <option value="3" {if $condi['form']['sms_status'] == '3'}selected{/if}>已删除</option>
              </select>
            <span class="help-block"></span>
        </div>
       
        <button name="submit" value="1" type="submit" class="btn btn-primary  input-sm">检 索</button>
        </div>
        
    </div>
   
    </form>
<table class="table table-striped table-hover font12">
    <colgroup>
        <col />
        <col class="t-col-10" />
        <col class="" />
        <col class="" />
        <col class="t-col-10" />
        <col class="t-col-10" />
        
    </colgroup>
    <thead>
        <tr>
            <th>ID</th>
            <th>手机号码</th>
            <th>短信内容</th>
            <th>状态</th>
            <th>时间</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan="5" class="text-right">{$multi}</td>
        </tr>
    </tfoot>
    <tbody>
{foreach $list as $_ca_id=>$_ca}
            <td>{$_ca['sms_id']}</td>
            <td>{$_ca['sms_mobile']}</td>
            <td>{$_ca['sms_message']}</td>
            <td>
                {if $_ca['sms_status'] == 1}
                <span class="label label-success" >发送成功</span>
                {/if}
                {if $_ca['sms_status'] == 2}
                <span class="label label-danger" >未成功</span>
                {/if}
                {if $_ca['sms_status'] == 3}
                <span class="label label-danger" >已删除</span>
                {/if}
            <td>{$_ca['sms_created']}</td>
            
        </tr>
{foreachelse}
        <tr>
            <td colspan="9" class="warning">
                暂无
            </td>
        </tr>
{/foreach}
    </tbody>
</table>
</div>
{include file='cyadmin/footer.tpl'}