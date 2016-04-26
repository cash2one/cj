{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
<div class="panel-heading">编辑</div>

<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="">
<input type="hidden" id="insi_id" name="id" value="{$form['insi_id']}" />
<div class="panel-body">
    {if !$pid}
    <div class="form-group">
        <label class="col-sm-2 control-label">打分项名称</label>
        <div class="col-sm-6">
            <input type="text" class="form-control" id="insi_name" name="form[insi_name]" placeholder="打分项名称" value="{$form['insi_name']}" />
        </div>
    </div>
    {/if}
    <div class="form-group">
        <label class="col-sm-2 control-label">打分项说明</label>
        <div class="col-sm-6">
            <input type="text" class="form-control" id="insi_describe" name="form[insi_describe]" placeholder="打分项说明" value="{$form['insi_describe']}" />
        </div>
    </div>
    {if $pid}
    <div class="form-group">
        <label class="col-sm-2 control-label">打分详细规则</label>
        <div class="col-sm-6">
            <input type="text" class="form-control diy_title async_insi_rules" data-async="insi_rules" name="form[insi_rules]" placeholder="打分详细规则" value="{$form['insi_rules']}" />
        </div>
    </div>
    {/if}
    <div class="form-group">
        <label class="col-sm-2 control-label">该项分数</label>
        <div class="col-sm-6">
            <input type="text" class="form-control diy_title async_insi_score" data-async="insi_score" name="form[insi_score]" placeholder="该项分数" value="{$form['insi_score']}" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">排序值</label>
        <div class="col-sm-6">
            <input type="text" class="form-control" name="form[insi_ordernum]" placeholder="排序值, 越大越靠前" value="{$form['insi_ordernum']}" />
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <input type="submit" name="submit" class="btn btn-primary" value="保存">
            &nbsp;&nbsp;
            <a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
        </div>
    </div>
</div>
</form>
</div>

<!-- 巡店流程 -->
{if !empty($insi_parent_id)}
<div class="row" id="inspect-config">
	<div class="col-md-6">
	    <div class="title">
	        <h6>设置流程详情</h6>
	    </div>
	    <div class="clearfix inspect-frame">
	        <div class="tab-left">
	            <div class="panel">
	                <div class="panel-body">
	                    <ul id="diy_step_switch" class="nav nav-pills nav-stacked">
	                        <li class="active">
	                            <label class="checkbox-inline">
	                                <input type="checkbox" id="ins_rules" value="1" class="px" checked="checked" disabled />
	                                <span class="lbl"></span>
	                            </label>
	                            <a href="#panel-001" data-toggle="tab" class="btn inspect-tab btn-outline">小贴士</a>
	                        </li>
	                        {if $cache_config['score_rule_diy'] == 0}
	                        <li>
	                            <label class="checkbox-inline">
	                                <input type="checkbox" id="ins_score" value="1" class="px" checked="checked" disabled />
	                                <span class="lbl"></span>
	                            </label>
	                            <a href="#panel-002" data-toggle="tab" class="btn inspect-tab btn-outline">打分</a>
	                        </li>
	                        <li>
	                            <label class="checkbox-inline">
	                                <input type="checkbox" name="form[insi_hasselect]" id="insi_select" value="1" class="px step_switch"{if !empty($form['insi_hasselect'])} checked="checked"{/if} />
	                                <span class="lbl"></span>
	                            </label>
	
	                            <a href="#panel-003" data-toggle="tab" class="btn inspect-tab btn-outline">单选题</a>
	                        </li>
	                        {/if}
	                        <li>
	                            <label class="checkbox-inline">
	                                <input type="checkbox" name="form[insi_hasatt]" id="insi_att" value="1" class="px step_switch"{if !empty($form['insi_hasatt'])} checked="checked"{/if} />
	                                <span class="lbl"></span>
	                            </label>
	                            <a href="#panel-004" data-toggle="tab" class="btn inspect-tab btn-outline">照片</a>
	                        </li>
	                        <li>
	                            <label class="checkbox-inline">
	                                <input type="checkbox" name="form[insi_hasfeedback]" id="insi_fb" value="1" class="px step_switch"{if !empty($form['insi_hasfeedback'])} checked="checked"{/if} />
	                                <span class="lbl"></span>
	                            </label>
	                            <a href="#panel-005" data-toggle="tab" class="btn inspect-tab btn-outline">文本框</a>
	                        </li>
	                    </ul>
	                </div>
	            </div>
	        </div>
	
	        <div class="tab-right">
	
	            <div class="tab-content" id="diy_div">
	                <div class="tab-pane active" id="panel-001">
	                    <!-- Default -->
	                    <div class="form-group">
	                        <label class="control-label">标题</label>                        
	                        <input type="text" class="form-control diy_title" id="insi_rules_title" name="form[insi_rules_title]" placeholder="输入标题" value="{$form['insi_rules_title']}" />
	                    </div>
	                    <div class="form-group">
	                        <label class="control-label">详情</label> 
	                        <textarea class="form-control diy_title async_insi_rules" data-async="insi_rules" id="insi_rules" name="form[insi_rules]" rows="3" placeholder="输入详情">{$form['insi_rules']}</textarea>
	                    </div>
	                </div>
	                {if $cache_config['score_rule_diy'] == 0}
	                <div class="tab-pane" id="panel-002">
	                    <div class="form-group">
	                        <label class="control-label">标题</label>                        
	                        <input type="text" class="form-control diy_title" id="insi_score_title" name="form[insi_score_title]" placeholder="输入标题" value="{$form['insi_score_title']}" />
	                    </div>
	                    <div class="form-group">
	                        <label class="control-label">分数</label>                       
	                        <input type="text" class="form-control diy_title async_insi_score" data-async="insi_score" id="insi_score" name="form[insi_score]" placeholder="输入分数" value="{$form['insi_score']}" />                    
	                    </div>
	                </div>
	                <div class="tab-pane" id="panel-003">                    
	                    <div class="form-group">
	                        <label class="control-label">标题</label>                        
	                        <input type="text" class="form-control diy_title" id="insi_select_title" name="form[insi_select_title]" placeholder="输入标题" value="{$form['insi_select_title']}" />
	                    </div>
	                    <div class="form-group" style="margin-bottom:0px;">
	                        <label class="control-label" for="inputError-4">单选项设置</label>
	                    </div>
	                    {foreach $options as $_opt name=option}
	                    <div class="form-group ori_option">
	                        <div class="input-group">
	                            <input type="text" class="form-control diy_title" name="options[{$_opt['inso_id']}]" placeholder="选项值" value="{$_opt['inso_optvalue']}" />
	                            <div class="input-group-btn add" style="cursor:pointer;">
	                                {if $smarty.foreach.option.last}<i class="fa fa-plus-circle diy_select"></i>{else}<i class="fa fa-minus-circle diy_select"></i>{/if}
	                            </div>
	                        </div>
	                    </div>
	                    {foreachelse}
	                    <div class="form-group ori_option">
	                        <div class="input-group">
	                            <input type="text" class="form-control diy_title" name="newopts[]" placeholder="选项值" value="" />
	                            <div class="input-group-btn add" style="cursor:pointer;">
	                                <i class="fa fa-plus-circle diy_select"></i>
	                            </div>
	                        </div>
	                    </div>
	                    {/foreach}
	                </div>
	                {/if}
	                <div class="tab-pane" id="panel-004">
	                     <div class="form-group">
	                        <label class="control-label">标题</label>                        
	                        <input type="text" class="form-control diy_title" id="insi_att_title" name="form[insi_att_title]" placeholder="输入标题" value="{$form['insi_att_title']}" />
	                    </div>
	                    <p class="help-block">用户在控件中最多上传5张照片</p>
	                </div>
	                <div class="tab-pane" id="panel-005">
	                     <div class="form-group">
	                        <label class="control-label">标题</label>                        
	                        <input type="text" class="form-control diy_title" id="insi_fb_title" name="form[insi_feedback_title]" placeholder="输入标题" value="{$form['insi_feedback_title']}" />
	                    </div>
	                    <p class="help-block">一般用在问题的反馈</p>
	                </div>
	            </div>
	            <div class="form-group footer-submit">
	                <button type="submit" id="submit_step" class="btn btn-primary">提交</button>
	                <button type="reset" id="reset_step" class="btn btn-outline">重置</button>
	            </div>

	        </div>
	    </div>
	</div>
	<!-- 手机预览滚动 -->
	<script>
	    init.push(function() {
	        $('#dashboard-recent .panel-body > div').slimScroll({ height: 496, alwaysVisible: true, color: '#888',allowPageScroll: true });
	    });
	</script>
	<!-- / Javascript -->
	<div class="col-md-4 col-md-offset-2" id="dashboard-recent">
	    <div class="mobile_view panel-body">
	        <div>
	            <h4 id="insi_rules_title_show">评估标准</h4>
	            <div>
	                <textarea class="form-control" id="insi_rules_show" rows="3" placeholder="输入详情"></textarea>
	            </div>
	            {if $cache_config['score_rule_diy'] == 0}
	            <h4 id="insi_score_title_show">评分</h4>
	            <div class="item-score">
	                <i class="fa fa-star"></i>
	                <i class="fa fa-star"></i>
	                <i class="fa fa-star"></i>
	                <i class="fa fa-star"></i>
	                <i class="fa fa-star"></i>
	                <span id="insi_score_show">5</span>
	                <small>分</small>
	            </div>
	            <h4 id="insi_select_title_show">单选</h4>
	            <div id="insi_select_content" class="single-selection">
	                <label class="radio" id="mb_option" style="display:none;">
	                    <input type="radio" name="styled-r1" class="px" checked="checked" />
	                    <span class="lbl"></span>
	                </label>
	            </div>
	            {/if}
	            <h4 id="insi_att_title_show">照片</h4>
	            <div id="insi_att_content" class="photo"></div>
	            <div></div>
	            <h4 id="insi_fb_title_show">问题</h4>
	            <div id="insi_fb_content">
	                <textarea class="form-control" rows="3" placeholder="输入详情"></textarea>
	            </div>
	        </div>
	    </div>
	</div>
</div>
{/if}
<script type="text/javascript">
var max_opt = {$max_opt};
$(function() {
	var opt_index = 0;
	// 增加新选项
	function newoption(ipt) {

		// 设置id
		ipt.attr('id', 'option_' + opt_index);
		// 获取选项模板
		var optra = $('#mb_option');
		var newoptra = optra.clone(true);
		var lbl = newoptra.find('.lbl');
		newoptra.css('display', 'block');
		newoptra.removeAttr('id');
		lbl.attr('id', 'option_' + opt_index + '_show');
		lbl.html(ipt.val() + '&nbsp;');
		// 插入选项
		optra.before(newoptra);
		opt_index ++;
	}
	
	function deloption(ipt) {
		
		$('#' + ipt.attr('id') + '_show').parent().remove();
	}
	
	function toggle_step(self) {
		
		if (true == self.prop('checked')) {
			$('#' + self.attr('id') + '_title_show').show();
			$('#' + self.attr('id') + '_content').show();
		} else {
			$('#' + self.attr('id') + '_title_show').hide();
			$('#' + self.attr('id') + '_content').hide();
		}
	}
	
	// 步骤选项
	$('#diy_step_switch .step_switch').each(function() {
		toggle_step($(this));
		$(this).click(function() {
			toggle_step($(this));
		});
	});
	
	// 详情配置
	$('.diy_title').each(function() {
		var self = $(this);
		self.data('rcd', self.val());
		$('#' + self.attr('id') + '_show').text(self.val());
		
		$(this).keyup(function() {
			var self = $(this);
			// 更新对应的文本信息
			$('#' + self.attr('id') + '_show').text(self.val());
			if ('undefined' == self.data('async')) {
				return true;
			}
			
			$('.async_' + self.data('async')).each(function() {
				if ($(this).attr('id') == self.attr('id')) {
					return true;
				}
				
				$(this).val(self.val());
				$('#' + $(this).attr('id') + '_show').text(self.val());
			});
		});
	});
	
	// 增减单选
	$('#diy_div .diy_select').each(function() {
		var self = $(this);
		// 选项输入框
		var ipt = self.parent().parent().find('input');
		newoption(ipt);
		
		// 监听 click 事件
		$(this).click(function() {
			var self = $(this);
			var selgroup = self.parent().parent().parent();
			// 删除当前 dom
			if (self.hasClass('fa-minus-circle')) {
				if (selgroup.hasClass('ori_option')) {
					selgroup.hide();
					selgroup.find('input').attr('disabled', true);
				} else {
					selgroup.remove();
				}
				
				deloption(self.parent().parent().find('input'));
				return true;
			}
			
			// 复制当前 dom
			if (self.hasClass('fa-plus-circle')) {
				// 选项值数目判断
				var num = 0;
				$('div.ori_option').each(function() {
					if ('none' == $(this).css('display')) {
						return true;
					}
					
					num ++;
				});
				if (max_opt < num + 1) {
					alert('选项数目不能大于' + max_opt + '个');
					return false;
				}
				
				var newsel = selgroup.clone(true);
				newsel.find('input').attr('name', 'newopts[]').val('');
				self.removeClass('fa-plus-circle').addClass('fa-minus-circle');
				selgroup.after(newsel);
				newsel.addClass('new_ori_option');
				newoption(newsel.find('input'));
			}
		});
	});
	
	// 重置
	$('#reset_step').click(function() {
		var li = $('#diy_step_switch li.active');
		// 重置文本
		$(li.find('a').attr('href') + ' .diy_title').each(function() {
			var self = $(this);
			self.val(self.data('rcd'));
			self.keyup();
		});
		
		// 重置选项
		$(li.find('a').attr('href') + ' div.ori_option').each(function() {
			var self = $(this);
			var ipt = self.find('input');
			if (self.hasClass('new_ori_option')) {
				self.remove();
				deloption(ipt);
			} else {
				deloption(ipt);
				self.show();
				self.find('input').attr('disabled', false);
				newoption(ipt);
				ipt.keyup();
			}
		});
		
		$(li.find('a').attr('href') + ' i.diy_select:last').removeClass('fa-minus-circle').addClass('fa-plus-circle');
	});
	
	// 步骤配置
	$('#submit_step').click(function() {
		var data = '';
		var li = $('#diy_step_switch li.active');
		var btn = $(this);
		var avg = 0;
		
		// 如果打分输入框存在, 则
		if (0 < $('#insi_score').length) {
			avg = $('#insi_score').val() / 5;
			if (avg != parseInt(avg)) {
				alert("分数只能为 5 的倍数");
				return false;
			}
		}
		
		btn.attr('disabled', true);
		// 重置文本
		data = $(li.find('a').attr('href') + ' .diy_title').serialize() + '&'
			 + $('input.step_switch').serialize() + '&'
			 + $('#form-adminer-edit').serialize() + '&submit=1';
		$.ajax({
			'url': '',
			'data': data,
			'type': 'post',
			'dataType': 'json',
			'success': function(res) {
				btn.attr('disabled', false);
				if (0 != res['errcode']) {
					alert(res['errmsg']);
					return false;
				}
				
				// 如果是新增
				if (0 < res['result']['insi_id']) {
					$('#insi_id').val(res['result']['insi_id']);
				}
				
				alert('保存成功');
			},
			'error': function(xhr) {
				btn.attr('disabled', true);
				alert(xhr);
			}
		});
	});
	
	// 打分项基本配置
    $('#form-adminer-edit').submit(function() {
        {if $pid}
        if ($.trim($('#insi_describe').val()) == '') {
            alert('打分项说明不能为空');
            return false;
        }
        {else}
        if ($.trim($('#insi_name').val()) == '') {
            alert('打分项名称不能为空');
            return false;
        }
        {/if}
       	var avg = 0;
   		
   		// 如果打分输入框存在, 则
   		if (0 < $('#insi_score').length) {
   			avg = $('#insi_score').val() / 5;
   			if (avg != parseInt(avg)) {
   				alert("分数只能为 5 的倍数");
   				return false;
   			}
   		}
   		
        $('#form-adminer-edit>.btn-primary').attr("disabled", true);
        var data = '';
		// 重置文本
		data = $('.diy_title').serialize() + '&'
			 + $('input.step_switch').serialize() + '&'
			 + $(this).serialize() + '&submit=1';
        $.ajax({
            'url': '',
            'data': data,
            'type': "post",
            'dataType': "json",
            'success': function(r) {
                if (r.errcode == 0) {
                    alert('保存成功');
                    location.href="{$defaultUrl}"; 
                } else {
                    alert(r.errmsg);
                    $('#form-adminer-edit>.btn-primary').attr("disabled", false);
                }
            }
        });
        return false;
    });
});
</script>

{include file="$tpl_dir_base/footer.tpl"}
