{include file="$tpl_dir_base/header.tpl"}

<link href="{$CSSDIR}app-crm.css" rel="stylesheet" type="text/css">
<form class="form-horizontal" id="newform" role="form" method="post" action="{$index_url}?act=update">
<input type="hidden" name="formhash" value="{$formhash}" />
<input type="hidden" name="tiid" value="{$index['tiid']}" />
<input type="hidden" name="refer" value="{$refer}" />
<input type="hidden" id="target" name="target" value="" />
<input type="hidden" id="cover_id" name="cover_id" value="" />
<div class="row app-crm" id="inspect-config">
	<script>
		init.push(function () {
			$('#phone-frame').slimScroll({ height: 496});
		});
	</script>
	<div class="col-md-4" id="dashboard-recent">
		<div class="mobile_view panel-body clearfix">
			<div class="slimScrollDiv" id="phone-frame">
				<div id="phone"></div>
				<div class="btn-add-pic">
					<button class="btn btn-info" id="addnewphoto">添加图片广告</button>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-7 col-md-offset-1">
		<div class="panel tl-body ">
			<div class="form-group">
				<label class="control-label col-sm-3" for="subject">公告标题*</label>
				<div class="col-sm-8">
					<input type="text" class="form-control form-small" id="subject" name="subject" placeholder="最多输入32个字符" value="{$index['subject']}" maxlength="64" required="required" />
				</div>
			</div>
			{if 1 != $index['tiid'] || empty($index)}
			<div class="form-group" style="margin-bottom:0">
				<label class="control-label col-sm-3">选择人员</label>
				<div class="col-sm-8">
					{include
						file="$tpl_dir_base/common_selector_member.tpl"
						input_type='radio'
						input_name='uid'
						selector_box_id='users_container'
						allow_member=true
						allow_department=false
						default_data=$default_user
					}
				</div>
			</div>
			{/if}
			<hr />
			<div class="form-group">
				<label class="control-label  col-sm-3" for="id_author">选择类型</label>
				<div class="col-sm-3">
					<input type="radio" class=" form-small" id="single_r" name="single_m" value="1" checked />&nbsp;&nbsp;<label for="single_r">大图展示</label>
				</div>
				<div class="col-sm-3">
					<input type="radio" class=" form-small" id="multi_r" name="single_m" value="2" />&nbsp;&nbsp;<label for="multi_r">多图展示</label>
				</div>
			</div>
			<div class="form-group no-margin-b">
				<label class="control-label  col-sm-3" for="id_author">图片上传</label>
				<div class="col-sm-9">
					<div class="uploader_box">
						{cycp_upload_multi
							inputname='cover_id'
							callback='upload_succ'
							hidedelete=1
							tip='(推荐尺寸 480x230)'
							showimage=0
						}
					</div>
					<div class="padding-xs-vr" id="preview"></div>
				</div>
			</div>
		</div>
		<div class="panel-padding">
			<button class="btn btn-lg btn-success" id="save">保存</button>&nbsp;&nbsp;<button class="btn btn-lg" id="cancel">取消</button>
		</div>
	</div>
</div>
</form>

<div id="myModal" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<button type="button" class="close padding-sm-hr" data-dismiss="modal" aria-hidden="true">×</button>
			<div class="padding-sm no-padding-b">
				<ul class="nav nav-tabs nav-tabs-sm">
					<li class="active"><a href="javascript:;" class="tabcard" data-tab="goods">产品详情</a></li>
					<li><a href="javascript:;" class="tabcard" data-tab="class">产品分类</a></li>
					<li><a href="javascript:;" class="tabcard" data-tab="material">活动页</a></li>
				</ul>
			</div>
			<div class="modal-body"></div>
		</div>
	</div>
</div>


{literal}

<script type="text/template" id="container_tpl">
<div class="show_photo single" id="show_<%=index%>" data-index="<%=index%>">
	<div class="content">
		<%for (var index in list) {%>
		<img id="show_img_<%=list[index].posi%>" src="<%=list[index].img%>" data-posi="<%=list[index].posi%>" data-href="<%=list[index].href%>" data-subject="<%=list[index].subject%>" />
		<%};%>
	</div>
	<div class="btn-group btn-group-xs">
		<button type="button" class="btn edit_show">编辑</button>
		<button type="button" class="btn btn-danger del_show">删除</button>
	</div>
</div>
</script>

<script type="text/template" id="single_show_tpl">
<img id="show_img_<%=posi%>" src="<%=img%>" data-posi="<%=posi%>" data-href="<%=href%>" data-subject="<%=subject%>" />
</script>

<script type="text/template" id="single_preview_tpl">
<div class="row padding-xs-vr photo" id="preview_<%=posi%>">
	<div class="col-xs-4 col-sm-3">
		<a href="javascript:;" target="_blank">
			<label class="btn btn-xs btn-reset label_upload" for="upload_btn" data-posi="<%=posi%>">重设</label>
			<img id="preview_img_<%=posi%>" src="<%=img%>" />
		</a>
	</div>
	<div class="col-xs-8 col-sm-9 href_div">
		<%if ('undefined' == typeof(subject) || "" == subject) {%>
		<a href="javascript:;" data-toggle="modal" data-target="#myModal" class="select_url">设置链接到的页面地址</a>
		<%} else {%>
		链接：<a href="javascript:;" data-href="<%=href%>"><%=subject%></a>
		<div class="padding-xs-vr">
			<a href="javascript:;" class="btn btn-info select_url" data-toggle="modal" data-target="#myModal">更改</a>
			&nbsp;
			<button class="btn btn-danger" class="del_select_url">删除</button>
		</div>
		<%}%>
	</div>
</div>
</script>

<script type="text/template" id="link_y_tpl">
链接：<a href="javascript:;" data-href="<%=href%>"><%=subject%></a>
<div class="padding-xs-vr">
	<a href="javascript:;" class="btn btn-info select_url" data-toggle="modal" data-target="#myModal">更改</a>
	&nbsp;
	<button class="btn btn-danger" class="del_select_url">删除</button>
</div>
</script>

<script type="text/template" id="link_n_tpl">
<a href="javascript:;" data-toggle="modal" data-target="#myModal" class="select_url">设置链接到的页面地址</a>
</script>

{/literal}

<script type="text/javascript">
//
var indexs = {$indexs};
// 当前索引
var indexid = 1;
// 改变label中的图片
var change_label = null;
// 是否初始化
var is_init = false;
// 弹出框的3个选项卡
var tabs = {
	"goods": "{$index_url}?act=goods",
	"class": "{$index_url}?act=goodsclass",
	"material": "{$index_url}?act=material"
};
// 当前tab
var cur_tab = "goods";
// 当前选择url
var cur_sel = null;
// 上传按钮
var upload_btn = "upload_btn";

{literal}

for (var i in indexs) {
	var curi = 0;
	for (var k in indexs[i]) {
		if (0 == k) {
			curi = indexid;
			add_new_photo(indexs[i][k]);
			continue;
		}

		var target = $("#show_" + curi);
		// 清除单选和多选
		target.removeClass("single");
		target.removeClass("multi");
		// 增加多选
		target.addClass("multi");

		target.find(".content").append(txTpl("single_show_tpl", indexs[i][k]));
	}
}

// 为上传按钮赋一个id
$("._cycp_uploader_multi").attr("id", upload_btn);

// 判断显示区是否有对象, 如果没有, 则新建一个
if (0 >= $("#phone").find(".show_photo").size()) {
	add_new_photo();
}

$("#addnewphoto").on("click", function(e) {
	add_new_photo();
	return false;
});

// 新增一个图片广告
function add_new_photo(data) {

	if ('undefined' == typeof(data)) {
		data = {"posi": indexid, "img": "/admincp/static/images/nopic.png", "href": "javascript:;", 'subject': ''};
	}

	if ("undefined" == typeof(data["subject"])) {
		data["subject"] = "";
	}

	if ("undefined" == typeof(data["message"])) {
		data["message"] = "";
	}

	$("#phone").append(txTpl("container_tpl", {'type': 1, 'list': [data], 'index': indexid}));
	$("#target").val("show_" + indexid);
	$("#preview").empty();
	indexid ++;
}

// 编辑指定图片广告
$("#phone").on("click", ".del_show", function(e) {
	var item = $(this).parents(".show_photo");
	// 如果是最后一个, 则不执行删除操作
	if (1 == item.parent().find(".show_photo").size()) {
		return true;
	}

	// 删除右侧操作区的广告图片
	item.find("img").each(function(index) {
		$("#preview_" + $(this).data("posi")).remove();
	});
	// 删除广告区
	item.remove();
});

// 编辑指定广告
$("#phone").on("click", ".edit_show", function(e) {
	var item = $(this).parents(".show_photo");
	$("#preview").empty();
	if (item.hasClass("single")) {
		$("#single_r").click();
	} else {
		$("#multi_r").click();
	}

	item.find("img").each(function(index) {
		var data = {
			"posi": $(this).data("posi"),
			"img": $(this).attr("src")
		};
		if ('undefined' == typeof($(this).data("subject"))) {
			data["subject"] = "";
			data["href"] = "";
		} else {
			data["subject"] = $(this).data("subject");
			data["href"] = $(this).data("href");
		}

		$("#preview").append(txTpl("single_preview_tpl", data));
	});

	$("#target").val("show_" + item.data("index"));
});

// 监听label事件
$("#preview").on("click", ".label_upload", function(e) {
	change_label = $(this);
	return true;
});

// 数据读取并展示
function show_data(url) {
	$.get(url, function(data) {
		$("#myModal").find(".modal-body").html(data);
	});
}

// 翻页
$("#myModal").on("click", "ul.pagination a", function(e) {
	if ($(this).attr("title") == $(this).text()) {
		show_data($(this).attr("href"));
	}

	return false;
});

// 选项卡事件
$("#myModal").on("click", ".tabcard", function(e) {
	// 如果点击的是当前的tab
	if (cur_tab == $(this).data("tab")) {
		return;
	}

	cur_tab = $(this).data("tab");
	show_data(tabs[cur_tab]);
});

// 监听关联事件
$("#myModal").on("click", ".relate", function(e) {
	var id = $(this).data("id");
	var data = {
		"href": $(this).data("href"),
		"subject": $("#" + cur_tab + "_name_" + id).text()
	};
	var href_div = cur_sel.parents('.href_div');
	href_div.html(txTpl("link_y_tpl", data));
	// 把 href, subject 写入图片属性
	var posi = get_id_from_id(href_div.parent().attr("id"));
	$("#show_img_" + posi).data("href", data.href);
	$("#show_img_" + posi).data("subject", data.subject);
	// 隐藏选择框
	$("#myModal").modal('hide');
});

// 监听url选择事件
$("#preview").on("click", ".select_url", function(e) {
	cur_sel = $(this);
	$("#myModal").modal('show');
	if (false == is_init) {
		show_data(tabs[cur_tab]);
	}

	return false;
});

// 监听删除链接事件
$("#preview").on("click", ".del_select_url", function(e) {
	$(this).parent().html(txTpl("link_n_tpl", {}));
});

// 监听添加广告图片按钮
$("#newad").on("click", function(e) {
	var id = "divtr_" + indexid ++;
	// 手机展示区
	$("#phone").append(txTpl("container_tpl", {"id": id}));
	// 默认单选
	$("#single_r").prop("checked", true);
	// 显示操作区
	$("#preview").show();
	// 当前操作的目标id
	$("#target").val(id);
	change_label = null;
	$("#preview").empty();
});

// 选择了单选
$("#single_r").on("click", function(e) {
	var target = $("#" + $("#target").val());
	// 清除单选和多选
	target.removeClass("single");
	target.removeClass("multi");
	// 增加单选
	target.addClass("single");
	// 清除多余的图片
	target.find("img").each(function(index) {
		if (0 == index) {
			return true;
		}

		$(this).remove();
	});

	$("#preview").find(".photo").each(function(index) {
		if (0 == index) {
			return true;
		}

		$(this).remove();
	});

	$("#" + upload_btn).parent().find("em").text('(推荐尺寸 640x260)');
	return true;
});

// 选择了多选
$("#multi_r").on("click", function(e) {
	var target = $("#" + $("#target").val());
	// 清除单选和多选
	target.removeClass("single");
	target.removeClass("multi");
	// 增加多选
	target.addClass("multi");
	$("#" + upload_btn).parent().find("em").text('(推荐尺寸 204x208)');
	return true;
});

function upload_succ(result) {
	var url = result.list[0].url;
	// 展示区
	var target = $("#target").val();
	var content = $("#" + target).find(".content");
	var showtpl, previewtpl;
	var id = 0;
	if (null != change_label) {
		id = change_label.data("posi");
		$("#show_img_" + id).attr("src", url);
		$("#preview_img_" + id).attr("src", url);
		change_label = null;
		return true;
	}

	// 展示区
	id = indexid;
	var data = {"posi": id, "img": url, "href": "javascript:;", "subject": ""};
	if (true == $("#single_r").prop("checked")) {
		$("#preview").html(txTpl("single_preview_tpl", data));
		content.html(txTpl("single_show_tpl", data));
	} else {
		$("#preview").append(txTpl("single_preview_tpl", data));
		content.append(txTpl("single_show_tpl", data));
	}

	indexid ++;
}

// 取消
$("#cancel").on("click", function(e) {
	history.go(-1);
	return false;
});

// 保存
$("#save").on("click", function(e) {
	var action = $("#newform").attr("action");
	var ipts = ['formhash', 'tiid', 'uid', 'subject', 'refer'];
	var data = {};
	// 获取 input
	$("input").each(function(index) {
		var name = $(this).attr("name");
		if (-1 === ipts.indexOf(name)) {
			return true;
		}

		data[name] = $(this).val();
	});

	data['message'] = [];
	// 获取所有图片
	var index = 0;
	$("#phone").find(".show_photo").each(function(index) {
		var imgdata = [];
		$(this).find("img").each(function(ind) {
			var img = $(this);
			if (!is_attach(img.attr("src"))) {
				return true;
			}

			imgdata.push({
				'href': img.data("href"),
				'subject': img.data("subject"),
				'img': img.attr("src"),
				'posi': index ++
			});
		});

		// 如果没有图片
		if (0 == imgdata.length) {
			return true;
		}

		// 图片
		data['message'].push(imgdata);
	});

	$.ajax({
		type: "POST",
		url: action,
		data: data,
		dataType: 'json',
		success: function(msg) {
			if (0 < msg["errcode"]) {
				alert(msg["errmsg"]);
				return false;
			}

			window.location.href = data["refer"];
		}
	});

	return false;
});

function is_attach(url) {
	var reg = /attachment\/read\/\d+/i;
	return reg.test(url);
}

function get_id_from_id(id) {

	var ar = id.split("_");
	return ar.pop();
}

// 初始化
$("#" + $("#target").val()).find(".edit_show").click();
{/literal}
</script>

{include file="$tpl_dir_base/footer.tpl"}
