{include file='admincp/header.tpl'}
<script>
var add_url = "{$addUrlBase}";
var upload_url = "{$uploadUrl}";
var download_url = "{$downloadUrl}";
var getRegionUrl = "{$getRegionUrl}";
var getShopUrl = "{$getShopUrl}";
{literal} 
$(function () {


$('.selectpicker-city').on('change', function(){
    var val = $(this).val();
    if (val.length > 0) {
        $.ajax({
                    url: getRegionUrl,
                    type: "POST",
                    data: 'parent='+val.join(','),
                    dataType: 'json',
                    success: function (data) {
                       
                            $('.selectpicker-district').find('option').each(function() {
                                $(this).remove();
                            });
                            $.each(data, function (k, item) {
                                $('.selectpicker-district').append('<option value="'+k+'">'+item.cr_name+'</option>');
                            });
                            $('.selectpicker-district').selectpicker('refresh');
                            
                        
                    }
                });
    }
});
$(".token-shop").tokenInput(getShopUrl, {
        theme: "facebook",
        queryParam: 'kw',
        hintText: '请输入你要搜索的用户名',
        prePopulatefun:function (el) {
            var val = $('#shopfilter').find('.csp-names').val();
            var ids = $('#shopfilter').find('.csp-ids').val();
            if (val) {
                ids = ids.split(',');                
                var data = [];
                $.map( val.split(','), function(value, key) {
                    if (value) {
                        data[key] = { csp_name: value, csp_id: ids[key]};
                    }
                });
                if (data) {
                    return data;
                } 
            }
            return false;
        },
        onAdd: function (item) {
            input = $('#shopfilter').find('.csp-ids');
            if (input.val()) {
                input.val(input.val()+','+item.csp_id);
            } else {
                input.val(item.csp_id);
            }
        },
        onDelete: function (item) {
            input = $('#shopfilter').find('.csp-ids');
            
            var val = input.val();
            input.val('');
            var newval = '';
            $.map(val.split(','), function (value) {
                if (value) {
                    if (value != item.csp_id) {
                        newval = newval + ','+value;
                        input.val(newval);
                    }
                }
            });
            
            
        },
        propertyToSearch: 'csp_name',
        minChars: 1,
        tokenLimit: 10,
    }); 

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        dataType: 'json',
        url: upload_url,
        maxFileSize: 5000000,
        maxNumberOfFiles : 1,
        //acceptFileTypes: /(\.|\/)(csv|jpe?g|png)$/i,
        acceptFileTypes: /(\.|\/)(xls)$/i,
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $(this).find('.fileinput-button').find('span').eq(0).text('正在上传中，进度：'+progress + '%');
            if (progress == 100) {
                $(this).find('.fileinput-button').find('span').eq(0).text('上传完成，正在导入数据请稍等。。。');
            }
        },
        done: function (e, data) {
            if (data.result.status == 100) {
                $(this).find('.fileinput-button').find('span').eq(0).text('导入成功! 如需继续请点击。');
                var msg = '成功导入:'+data.result.successtotal+"条数据\n 错误: "+data.result.errortotal+"条数据\n";
                if (data.result.error.length > 0) {
                    var error = '';
                    $.each(data.result.error, function (index, value) {
                        error += '行数:'+value.num+', 字段: '+value.key+", 错误信息: "+value.msg+"\n";  
                    });
                    msg +="\n----------------------------\n错误详情：\n-------------------------\n"+error;
                }
                alert(msg);
                location.refresh();
            } else {
                $(this).find('.fileinput-button').find('span').eq(0).text('导入失败! 如需继续请点击。');
                alert(data.result.error);
            }
         
        }
    });


   $('#btn-add').click(function () {
        location.href = add_url;
   });   
   $('#btn-download').click(function () {
        location.href = download_url;
   });
    

});
{/literal} 
</script>

<div>
                <form  id="fileupload" method="POST" class="form-inline"  enctype="multipart/form-data">
            
                <div class="control-group pull-right">
                     <span id="btn-add" class="btn btn-default">
                     <i class="fa fa-plus"></i>
                    <botton>新增</botton>
                    </span>
                     <span id="btn-download" class="btn btn-default">
                       <i class="fa fa-download"></i>
                    <botton>Excel模版下载</botton>
                    </span>
                    <span  class="btn btn-default fileinput-button">
                    <i class="fa fa-upload"></i>
                    <span>上传需要导入的Excel文件</span>
                    <input type="file" name="files" multiple>
                    </span>
                    
                </div>
                </form>
         </div>
        
 <div class="clearfix"></div>
<form   class="form-horizontal panel panel-default" action="" method="post">
  <div class="form-group panel-body">
        
        <label class="control-label col-sm-1">{$cache_config['title_city']}</label>
        <div class="col-sm-5">
            <select name="search[city][]" title="选择{$cache_config['title_city']}" data-header="可以选择多个{$cache_config['title_city']}" class="selectpicker  selectpicker-city col-lg-5" data-live-search="true" multiple>
                    {foreach $region as $_id=>$_data}
                    <option value="{$_data['cr_id']}" {if $search['city'] && in_array($_data['cr_id'], $search['city'])}selected{/if}>{$_data['cr_name']}</option>
                    {/foreach}
                    </select>
                    <select name="search[district][]" title="选择{$cache_config['title_region']}" data-header="可以选择多个{$cache_config['title_region']}" class="selectpicker selectpicker-district  col-lg-5" data-live-search="true" multiple>
                    <option disabled>无</option>
                    {foreach $search['district_org'] as $_id=>$_data}
                    <option value="{$_data['cr_id']}" {if $search['district'] && in_array($_data['cr_id'], $search['district'])}selected{/if}>{$_data['cr_name']}</option>
                    {/foreach}
                    </select>
            <span class="help-block"></span>
        </div>
  
        <label class="control-label col-sm-1">门店名称</label>
        <div class="col-sm-2" id="shopfilter">
            <input type="hidden" class="csp-names" value="{$search['csp_names']}" >
            <input type="hidden" name="search[csp_ids]" class="csp-ids" value="{$search['csp_ids']}" >
            <input type="text" placeholder="" class="input-sm form-control token-shop">
            <span class="help-block"></span>
        </div>
        <button name="submit" value="1" type="submit" class="btn btn-primary  input-sm">检 索</button>
             
        
    </div>
   
    </form>   
<form class="form-horizontal" role="form" method="post" action="{$deleteUrlBase}">
<table class="table table-striped table-hover font12">
    <colgroup>
        <col class="t-col-5" />
        <col class="t-col-20" />
        <col class="t-col-10" />
        <col class="t-col-10" />
                <col />
        
        <col class="t-col-12" />
    </colgroup>
    <thead>
        <tr>
            <th><label class="vcy-label-none"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase} disabled="disabled"{/if} />删除</label></th>
            <th>门店名称</th>
            <th>{$cache_config['title_city']}</th>
            <th>{$cache_config['title_region']}</th>
            <th>具体地址</th>
            <th>操作</th>
        </tr>
    </thead>
{if $total > 0}
    <tfoot>
        <tr>
            <td colspan="2">{if $deleteUrlBase}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
            <td colspan="7" class="text-right vcy-page">{$multi}</td>
        </tr>
    </tfoot>
{/if}
    <tbody>
{foreach $list as $_id=>$_data}
        <tr>
            <td class="text-left"><input type="checkbox" name="delete[{$_id}]" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /></td>
            <td>{$_data['csp_name']|escape}</td>
            <td>{$_data['cr_name_parent']|escape}</td>
            <td>{$_data['cr_name']}</td>
            <td>{$_data['csp_address']}</td>
            <td>
                {$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="_delete"')} | 
                {$base->linkShow($editUrl, $_id, '编辑', 'fa-edit', '')}
            </td>
        </tr>
{foreachelse}
        <tr>
            <td colspan="9" class="warning">{if $issearch}未搜索到指定条件的职务信息{else}暂无职务信息{/if}</td>
        </tr>
{/foreach}
    </tbody>
</table>
</form>

{include file='admincp/footer.tpl'}