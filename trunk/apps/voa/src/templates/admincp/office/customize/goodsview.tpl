{include file="$tpl_dir_base/header.tpl"}

<div class="">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel" id="edit">
                <div class="panel-heading">
                    <span class="panel-title">查看</span>
                    <div class="panel-heading-controls">
                        <a href="#/edit/{$goods.dataid}"
                           class="btn btn-xs btn-success btn-outline"> <i class="fa fa-pencil"></i>
                        </a>
                        <button class="js-btn-del btn btn-xs btn-danger btn-outline">
                            <span class="fa fa-trash-o"></span>
                        </button>
                    </div>
                </div>
                <div class="panel-body">
                <table id="user" class="table table-bordered table-striped" style="margin-bottom:0;">
                    <tbody>
                     <tr>
                        <td>分类</td>
                        <td>{$classname}</td>
                     </tr>
                     <tr>
                         <td>权限</td>
                         <td>
                         {if !empty($goods.uids)}
                            {foreach $goods.uids as $_id=>$_data}
                                <span>{$_data.name}</span>&nbsp;
                            {/foreach}
                         {/if}
                         </td>
                     </tr>
                     <tr>
                        <td>商品规格(尺寸)</td>
                        <td>
                        {foreach $goods.styles as $_id=>$_data}
                            <div class="row ">
                                <div class="col-xs-6">规格：{$_data.stylename}</div>
                                <div class="col-xs-3">价格:{$_data.price}</div>
                                <div class="col-xs-3">库存:{$_data.amount}</div>
                            </div>
                       {/foreach}
                       </td>
                     </tr>
                     
                     
                     {foreach $columnlist as $_id=>$_data} 
                     <tr>
                        <td width="150" nowrap="nowrap">
                            {$_data.fieldname}
                        </td>
                        <td>
                        {if $_data['ct_type'] == 'attach'}
                            {if is_array($goods['_'+$_data['tc_id']])}
                            sfsfs
                            {/if}
                        {else}
                        	{if $_data['ct_type'] == 'checkbox' || $_data.ct_type == 'radio'}
                        	11223322
                        	{else}
                        		{$goods['_'+$_data['tc_id']]}
                        	{/if}
                        {/if}   
						</td>
                    </tr>
                    {/foreach}
                    </tbody>
            </table>
        </div>

        <div class="panel-footer">
            <a href="#" class="btn btn-default">返回</a>
        </div>
    </div>
</div>
</div>
</div>

{include file="$tpl_dir_base/footer.tpl"}
