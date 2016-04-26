{include file='mobile/header.tpl' navtitle="编辑报名信息"}
<style>
#field {
	width: 95%;
}
.ui-poptips {
	z-index: 999;
}
#subject {
	overflow: hidden;
	height: 40px;
}
</style>
<link rel="stylesheet" type="text/css" href="/misc/styles/oa_ght.css">
	
	<div class="ui-top-border"></div>
	<div class="ui-form">
		<div class="ui-form-item ui-form-item-order ui-border-b">
			<label>活动标题</label>
			<p id="subject"></p>
		</div>
		<div class="ui-form-item ui-form-item-order ui-border-b">
			<label>截止日期</label>
			<p id="created"></p>
		</div>
		<div class="ui-form-item ui-form-item-order ui-form-item-link">
			<a href="/frontend/campaign/view?id={$id}&saleid={$saleid}&sharetime={$time}">
				<label>活动内容</label>
				<p class="ui-text-color-primary">详情</p>
			</a>
		</div>
	</div>
	<div class="ui-txt-muted">用户报名信息</div>
	<ul class="ui-form ui-grid-trisect">
		<li>
			<div class="ui-grid-trisect-img">
				<span class="ui-btn ui-btn-primary">姓名</span>
			</div>
		</li>
		
		<li>
			<div class="ui-grid-trisect-img">
				<span class="ui-btn ui-btn-primary">手机号</span>
			</div>
		</li>
		<li class="choose">
			<div class="ui-grid-trisect-img">
				<span class="ui-btn">孕期</span>
			</div>
		</li>
		<li class="choose">
			<div class="ui-grid-trisect-img">
				<span class="ui-btn">预产期</span>
			</div>
		</li>
		<li class="choose">
			<div class="ui-grid-trisect-img">
				<span class="ui-btn">行业</span>
			</div>
		</li>
		<li class="choose">
			<div class="ui-grid-trisect-img">
				<span class="ui-btn">备注</span>
			</div>
		</li>
		<li class="add">
			<div class="ui-grid-trisect-img">
				<span id="add" class="ui-btn">+</span>
			</div>
		</li>
	</ul>
	<div class="ui-txt-muted">注：用户报名信息为活动详情页 “报名表” 内容，可以根据需求自定义添加字段，以获取需要的信息。</div>
	<div class="ui-btn-group-tiled ui-btn-wrap">
		<button class="ui-btn-lg" onclick="location.href='/frontend/campaign/list'">取消</button>
		<button id="save" class="ui-btn-lg ui-btn-primary">保存</button>
	</div>

	<div class="ui-dialog">
		<div class="ui-dialog-cnt">
			<div class="ui-dialog-bd">
				<div>
					<h4>添加新字段</h4>
					<div><input id="field" type="text" placeholder="请输入报名项,10个字以内"/></div>
				</div>
			</div>
			<div class="ui-dialog-ft ui-btn-group">
				<button type="button" data-role="button" class="select" id="dialogButton1">取消</button>
				<button type="button" class="select" id="enter">确定</button>
			</div>
		</div>
	</div>
			
<script type="text/javascript">
var id = "{$id}";
{literal}
require(["zepto"], function($) {
	
	//活动详情
	$.getJSON('/api/campaign/get/custom?id='+id, function (json){
		if(json.errcode > 0) {
			$.tips({content: '获取活动详情错误:' + json.errmsg});
			return;
		}
		$('#subject').text(json.result.subject);
		$('#created').text(json.result._created);
		var custom = json.result.custom;
		if(!custom) return;
		$('li.choose').each(function (i, e){
			var v = $(e).text().trim();
			var index = $.inArray(v, json.result.custom);
			if(index != -1) {
				$(e).find('span').addClass('ui-btn-primary');
				delete custom[index];	//删除已选中的元素
			}
		});
		//添加custom中未选中的元素
		for(k in custom) {
			$('li.add').before('<li class="custom">\
				<div class="ui-grid-trisect-img">\
					<span class="ui-btn ui-btn-primary">'+custom[k]+'</span>\
				</div>\
			</li>');
		}
	});
	
	//用户报名信息处理
	$('ul').on('click', 'span.ui-btn', function (){
		if(this.id == 'add') {
			//添加字段
			$('.ui-dialog').dialog('show');
			$('#field').focus();
		}else{
			if(this.innerHTML == '姓名' || this.innerHTML == '手机号') {
				$.tips({content: '姓名和手机号不可删除'});
				return false;	
			}
			//选中/不选字段
			if($(this).hasClass('ui-btn-primary')) {
				$(this).removeClass('ui-btn-primary');
			}else{
				$(this).addClass('ui-btn-primary');
			}
		}
	});
	
	//添加报名字段
	$('#enter').click(function (){
		var v = $('#field').val();
		if(!v) {
			return $.tips({content: '请输入字段名称',stayTime: 3000});
		}
		if(v.length > 10) {
			return $.tips({content: '字段名长度不可超过10个字',stayTime: 3000});
		}
		$('li.add').before('<li class="custom">\
			<div class="ui-grid-trisect-img">\
				<span class="ui-btn ui-btn-primary">'+v+'</span>\
			</div>\
		</li>');
		$('.ui-dialog').dialog('hide');
		$('#field').val('');
	});
	
	//保存报名字段
	$('#save').click(function (){
		var custom = [{name: 'id', value: id}];
		$('li.choose, li.custom').each(function (){
			if($(this).find('span').hasClass('ui-btn-primary')) {
				var d = {name: 'custom[]', value: $(this).find('span').text()};
				custom.push(d);
			}
		});
		$.post('/api/campaign/post/custom', custom, function (json){
			if(json.errcode == 0) {
				$.tips({content: '保存成功'});
				setTimeout(function (){
					location.href = '/frontend/campaign/list';
				}, 1000);
			}
		}, 'json');
	});
});
{/literal}
</script>
{include file='mobile/footer.tpl'}