{include file="$tpl_dir_base/header.tpl"}
<script>
var getUsersUrl = "{$getUsersUrl}";
var getRegionUrl = "{$getRegionUrl}";
var getShopUrl = "{$getShopUrl}";

{literal} 
$(function(){
$('.selectpicker-district').on('change', function(){
    $('#shop-list').find('option').each(function() {
        if ($(this).is(':selected') === false) {
           $(this).remove();
        }
    });
    var val = $(this).val();
    if (val) {
        $.ajax({
                    url: getShopUrl,
                    type: "POST",
                    data: 'districts='+val.join(','),
                    dataType: 'json',
                    success: function (data) {
                            var option = [];
                            var i = 0;
                            $.each(data, function (k, item) {
                                option[i++] = {value: k, text: item.csp_name}; 
                            });
                            $('#shop-list').multiSelect("addOption", option);
                            $('#shop-list').multiSelect("refresh");
                            
                        
                    }
                });
    }
    
});
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
 $('.it-start-date, .it-end-date').datepicker({
        todayHighlight: true
    });
    /*
$('.input-group.date input').each(function(e){
        var dp = $(this).datepicker({todayHighlight: true, todayBtn: "linked", autoclose: true})
          .data('datepicker');
        $(this).parent().find('button').click(function(e){
          dp.update(new Date());
        })
      });
$('.input-group.time').each(function(e){
          var el = $(this).find('input');
          var value = el.val();
          var tp = el.timepicker({
            minuteStep: 1,
            showSeconds: true,
            showMeridian: false,
            defaultTime: false
          }).data('timepicker');
          $(this).find('button').click(function(e){
            tp.$element.val("");
            tp.setDefaultTime('current');
            tp.update();
          })
        });*/
$(".token-m-username").tokenInput(getUsersUrl, {
        theme: "facebook",
        queryParam: 'kw',
        hintText: '请输入你要搜索的用户名',
        prePopulatefun:function (el) {
            var val = $(el).parents('div.panel').find('.users').val();
            var ids = $(el).parents('div.panel').find('.it-uid').val();
            if (val) {
                ids = ids.split(',');                
                var data = [];
                $.map( val.split(','), function(value, key) {
                    if (value) {
                        data[key] = { m_username: value, m_uid: ids[key]};
                    }
                });
                if (data) {
                    return data;
                } 
            }
            return false;
        },
        onAdd: function (item) {
            input = $(this).parents('div.panel').find('.it-uid');
            if (input.val()) {
                input.val(input.val()+','+item.m_uid);
            } else {
                input.val(item.m_uid);
            }
        },
        onDelete: function (item) {
            input = $(this).parents('div.panel').find('.it-uid');
            users = $(this).parents('div.panel').find('.users');
            var val = input.val();
            input.val('');
            var newval = '';
            $.map(val.split(','), function (value) {
                if (value) {
                    if (value != item.m_uid) {
                        newval = newval + ','+value;
                        input.val(newval);
                    }
                }
            });
            
        },
        propertyToSearch: 'm_username',
        minChars: 1,
        tokenLimit: 1,
    }); 
    
$('#shop-list').multiSelect({ 
    selectableHeader: "<div class='custom-header'>没有选择</div>",
    selectionHeader: "<div class='custom-header'>已经选择</div>",
    afterInit: function(ms){
        //$('.selectpicker').selectpicker();
    },
    selectableOptgroup: true });
});
{/literal} 
</script>
<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="">
<!--
    <div class="form-group">
        <label  class="col-sm-2 control-label">任务标题</label>
        <div class="col-sm-5">
            <div class="panel">
            <input type="text" name="item[it_title]" class="form-control task-title input-sm" value="{$data['it_title']|escape}" />
            </div>
        </div>
    </div>-->
    <div class="panel">
      <div class="panel-body">
    <div class="form-group">
        <label  class="col-sm-2 control-label">执行人</label>
        <div class="col-sm-6">
            <div class="">
                <input type="hidden" class="users" value="{$data['it_assign_users']}" >
                <input type="hidden" name="item[it_assign_uid]" class="it-uid" value="{$data['it_assign_uid']}" >
                <input type="text"   placeholder="" class="input-sm form-control token-m-username">
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="ca_password" class="col-sm-2 control-label">{$cache_config['title_city']}</label>
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading ">
                    <select title="选择{$cache_config['title_city']}" data-header="可以选择多个{$cache_config['title_city']}" class="selectpicker  selectpicker-city col-lg-5" data-live-search="true" multiple>
                    {foreach $region as $_id=>$_data}
                    <option value="{$_data['cr_id']}">{$_data['cr_name']}</option>
                    {/foreach}
                    </select>
                    <select title="选择{$cache_config['title_region']}" data-header="可以选择多个{$cache_config['title_region']}" class="selectpicker selectpicker-district  col-lg-5" data-live-search="true" multiple>
                    <option disabled>无</option>
                    </select>
                </div>
                <div class="panel-body">
                    <select name="item[it_csp_id_list][]"  id='shop-list' multiple='multiple'>
                    {if $data['it_csp_id_list']}
                    {foreach $data['it_csp_id_list'] as $key=>$val}
                    <option value="{$val['csp_id']}" selected>{$val['csp_name']}</option>
                    {/foreach}
                    {/if}
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group plan">
        <label for="ca_mobilephone" class="col-sm-2 control-label">重复执行计划</label>
        <div class="col-sm-10">
         <div class="radio">
            <label >
              <input type="radio" name="item[it_repeat_frequency]" value="no" {if $data['it_repeat_frequency']['no']}checked{/if}> 
              不重复
            </label>
          </div>
          <div class="radio">
            <label >
              <input type="radio" name="item[it_repeat_frequency]" {if $data['it_repeat_frequency']['day']}checked{/if} value="day"> 
              <div class="input-group  col-sm-3">
                  <div class="input-group-addon">每</div>
                  <input name="item[it_repeat_date_day]" value="{$data['it_repeat_frequency']['day']}" class="form-control input-sm " type="number" placeholder="1">
                  <div class="input-group-addon">天重复</div>
                </div>
            </label>
          </div>
          <div class="radio">
            <label >
              <input type="radio" name="item[it_repeat_frequency]"  {if $data['it_repeat_frequency']['week']}checked{/if} value="week"> 
              <div class="input-group  col-sm-2">
                  <div class="input-group-addon">每周</div>
                  <select name="item[it_repeat_date_week]" class="selectpicker" >
                  <option value="1" selected>一</option>
                  <option value="2">二</option>
                  <option value="3">三</option>
                  <option value="4">四</option>
                  <option value="5">五</option>
                  <option value="6">六</option>
                  <option value="0">日</option>
                  </select>
                  <div class="input-group-addon">重复</div>
                </div>
            </label>
          </div>
          <div class="radio">
            <label >
              <input type="radio" name="item[it_repeat_frequency]" value="mon"  {if $data['it_repeat_frequency']['mon']}checked{/if}> 
              <div class="input-group  col-sm-2">
                  <div class="input-group-addon">每月</div>
                  <select name="item[it_repeat_date_mon]" class="selectpicker">
                  <option value="1" selected>1</option>
                  {for $var=2 to 31} 
                  <option value="{$var}" {if $var == $data['it_repeat_frequency']['mon']}selected{/if}>{$var}</option>
                  {/for}
                  </select>
                  <div class="input-group-addon">号重复</div>
                </div>
            </label>
          </div>
        
        </div>
        </div>
    
    <div class="form-group">
        
        <label class="col-sm-2 control-label">开始日期</label>
        <div class="col-sm-6">
            <div class="">
            <div class="input-group date bootstrap-datepicker">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input class="date-field form-control it-start-date"  name="item[it_start_date]"  type="text" value="{$data['it_start_date']}" />                                
            </div>    
         	</div>
         </div>
    </div>
         
    <div class="form-group">
        
        <label class="col-sm-2 control-label">重复发送的截止日期</label>
        <div class="col-sm-6">
            <div class="">
            <div class="input-group date bootstrap-datepicker">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input type="text" class="date-field it-end-date form-control" value="{$data['it_end_date']}" name="item[it_end_date]">
                </div>    
         </div>
         </div>
    </div>
    <div class="form-group">
        
        <label class="col-sm-2 control-label">提醒的时间点</label>
        <div class="col-sm-6">
            <div class="">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                    <select class="form-control selectpicker" name="item[it_alert_time]" >
                      {for $var=0 to 23}
                      <option value="{sprintf('%02s:00', $var)}" {if $data['it_alert_time'] eq {sprintf('%02s:00:00', $var)}}selected{/if}>{sprintf('%02s:00', $var)}</option>
                      {/for}
                      </select>
                </div>    
         </div>
         </div>
    </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">执行状态</label>
            <div class="col-sm-6">
                <div class="">
                      <select name="item[it_execution_status]" class="selectpicker execution-status ">
                      <option value="1" {if $data['it_execution_status'] == 1}selected{/if}>未开始</option>
                      <option value="2" {if $data['it_execution_status'] == 2}selected{/if}>马上执行</option>
                      {if $data['it_execution_status'] > 1 }
                      <option value="3" {if $data['it_execution_status'] == 3}selected{/if}>{if $data['it_execution_status']==2}撤消{else}已撤消{/if}</option>
                      {/if}
                      </select>
                </div>
            </div>
        </div>
        <!--
        <div class="form-group">
            <label class="col-sm-2 control-label">任务说明</label>
            <div class="col-sm-5">
                <div class="panel">
                      <textarea name="item[it_description]" class="form-control ">{$data['it_description']}</textarea>
                </div>
            </div>
        </div>-->
   
</form>
</div>
<div class="panel-footer">
                  <input type="submit" name="submit" class="btn btn btn-primary" value="保存">
            &nbsp;&nbsp;
            <a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
                </div>
</div>
<script type="text/javascript">
$(function(){
    $('#form-adminer-edit').submit(function(){
    
        if ($('.it-uid').val() == 0) {
            alert('你没有指定执行人');
            return false;
        }
        if ($('#shop-list').val() == null) {
            alert('你没有指定商店');
            return false;
        }
        if ($('.execution-status').val() == '2') {
           
           
             if ($('.it-start-date').val() == null && $('.it-start-date').val() == 0) {
                alert('你没有指定开始时间，不能马上执行');
                return false;
            }
            
        }
        
        $('.btn-primary').attr("disabled", true);
        $.ajax({
            url: '',
            data: $(this).serialize()+'&submit=1',
            type: "post",
            dataType: "json",
            success: function(r){
                if (r.result.status == 100) {
                    alert('保存成功');
                    location.href="{$listUrl}"; 
                } else {
                    alert('保存错误请重新提交');
                    $('.btn-primary').attr("disabled", false);
                }
            }
        });
        return false;
    });
});
</script>

{include file="$tpl_dir_base/footer.tpl"}
