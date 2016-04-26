{include file="$tpl_dir_base/header.tpl"}

<style>
.inline {
	display: inline;
	vertical-align: middle;
}

.input-inline {
    display: inline-block;
    width: auto;
    vertical-align: middle;
}

.block {
	display: block;
}

#persons {
	display: none;;
}

#persons ul {
	list-style: none;
	padding: 0;
}

#persons li {
	float: left;
}

#persons .item {
    position: relative;
    margin: 5px 10px 5px 0px;
    min-width: 100px;
    max-width: 300px;
    height: 32px;
    border-radius: 2px;
    padding-right: 5px;
    background-color: #EEEEEE;
}

#persons .item label {
    max-width: 255px;
    max-height: 32px;
    font-weight: normal;
    line-height: 32px;
    overflow: hidden;
}

#persons .item .department, #persons .item .tag {
    float: left;
    margin: 0 5px;
    color: #46b8da;
    font-size: 20px;
    line-height: 32px;
}

#persons .item .person {
	float: left;
	margin-right: 5px;
	width: 32px;
	height: 32px;
	border-radius: 100%;
}

#persons .item .remove {
    position: absolute;
    top: -15px;
    right: -4px;
    color: red;
    font-size: 20px;
    cursor: pointer;
}

.share-url {
	display: none;
}

.form-title {
	padding: 5px;
	background-color: #4b8df8;
	border-radius: 2px;
	color: #FFFFFF;
	text-align: center;
}

.custom-form {
	margin-bottom: 40px;
}

#form-view {
	min-height: 100px;
}

#form-view ul {
	list-style: none;
	padding: 0;
}

#form-view li {
    border: 2px solid #FFFFFF;
    cursor: pointer;
}

#form-view li:hover {
    border: 2px solid #d9edf7;
}

#form-view li.active {
    border: 2px solid #d9edf7;
    background-color: #d9edf7;
}

#form-view li.placeholder {
    border: 2px dashed #c2c2c2;
}

#form-view .field {
    position: relative;
    margin: 5px 50px 5px 10px;
}

#form-view .field .required::before {
	color: red;
	content: "*";
}

#form-view .field .radio .lbl, #form-view li .checkbox .lbl {
	font-weight: normal;
}

#form-view .field .option-other {
	display: none;
}

#form-view .field .image {
	color: #C2C2C2;
	font-size: 30px !important;
}

#form-view .field .remove {
    display: none;
    position: absolute;
    top: -23px;
    right: -55px;
    color: red;
    font-size: 20px;
}

#form-view li.active .field .remove {
    display: block;
}

#form-view .field .order {
    display: none;
    position: absolute;
    top: 30%;
    right: -34px;
    color: #4b8df8;
    font-size: 20px;
    cursor: move;
}

#form-view li:hover .field .order {
    display: block;
}

#form-view .toolbar {
	border: #c2c2c2 2px dashed;
	text-align: center;
}

#form-view .toolbar .tip {
	padding: 10px;
	font-size: 20px;
	cursor: pointer;
}

#form-view .toolbar .tip:hover {
	background-color: #fafafa;
}

#form-view .toolbar .tip .glyphicon {
	color: #4b8df8;
}

#form-view .toolbar .types {
	display: none;
	overflow: auto;
}

#form-view .toolbar .types button {
	margin: 5px 0;
	width: 80%;
}

#field-setting {
	height: 100%;
	padding-left: 0;
}
#field-setting .setting {
	background-color: #d9edf7;
}

#field-setting .setting .typename {
	height: 40px;
	background-color: #4b8df8;
	color: #FFFFFF;
	font-size: 20px;
	line-height: 40px;
	text-align: center;
}

#field-setting .setting .view {
	padding: 0 11px 11px 11px;
}

#field-setting .setting .view label {
	padding-top: 10px;
}

#field-setting .setting .view .lblfix {
	width: 100%;
}

#field-setting .setting .view .lblfix::before {
	top: 6px;
}

#field-setting .setting .view select, #field-setting .setting .view .lblfix input {
	font-weight: normal;
}

#field-setting .setting .view .radio .lblfix::after {
	top: 11px;
}

#field-setting .setting .view .checkbox .lblfix::after {
	top: 7px;
}

#field-setting .setting .view .radio .lblfix, #field-setting .setting .view .checkbox .lblfix {
	width: 80%;
}

#field-setting .setting .view .radio i, #field-setting .setting .view .checkbox i {
	margin-top: 6px;
	color: #4b8df8;
	font-size: 20px;
	vertical-align: top;
	cursor: pointer;
}

#field-setting .setting .view .input-xs {
	display: inline;
	width: 100px;
}

#field-setting .setting .view .select-xs {
	width: 40%
}

#field-setting .setting .view .open-other {
	display: block;
	cursor: pointer;
}

#field-setting .setting .view .option-other {
    display: none;
}

.action button {
	margin-right: 20px;
}
</style>

<link href="{$CSSDIR}iconfont/iconfont.css" rel="stylesheet" type="text/css" />

<div class="panel panel-default">
	<div class="panel-body">
		<form method="post" action="" autocomplete="off" class="form-horizontal">
			<div class="form-group">
				<label class="control-label col-sm-2">问卷标题</label>
				<div class="col-sm-9">
					<input id="title" name="title" type="text" maxlength="25" class="form-control" />
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2">问卷描述</label>
				<div class="col-sm-9">
					<textarea id="body" name="body" rows="4" maxlength="120" class="form-control"></textarea>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2">分类</label>
				<div class="col-sm-9">
					<select id="qc_id" name="qc_id" class="form-control"></select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2">截止日期</label>
				<div class="col-sm-9">
					<div id="deadline" class="input-daterange input-group">
						<div style="width: 300px;">
							<input id="deadline_date" name="deadline_date" type="text" value="{date('Y-m-d')}" placeholder="截止日期" class="form-control" style="width: 150px;" />
							<input id="deadline_time" name="deadline_time" type="text" placeholder="截止时间" class="form-control" style="width: 150px;" />
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<span class="help-block text-right">以下设置在第一次发布后不可进行二次编辑</span>
			</div>
			<hr class="no-grid-gutter-h grid-gutter-margin-b no-margin-t">
			<div class="form-group disable-edit">
				<label class="control-label col-sm-2">可见范围</label>
				<div class="col-sm-9 persons">
					<button id="selectall" type="button" class="btn btn-info">所有人</button>&nbsp;&nbsp;
					<button id="selectper" type="button" class="btn">特定人员</button>
					<input id="is_all" name="is_all" type="hidden" value="-1" />
					<div id="persons">
						<ul></ul>
					</div>
				</div>
			</div>
			<div class="form-group disable-edit">
				<label class="control-label col-sm-2">分享</label>
				<div class="col-sm-9 share">
					<label class="radio-inline">
						<input name="share" type="radio" value="1" class="px">
						<span class="lbl">可分享</span>
					</label>
					<label class="radio-inline">
						<input name="share" type="radio" value="2" checked="checked" class="px">
						<span class="lbl">不可分享</span>
					</label>
					<lable class="control-label help-block inline">（分享按钮被屏蔽，且用户在微信端收到的消息详情页将会加上姓名水印，一定程度上防止客户用手机“截屏”泄密）</lable>
				</div>
			</div>
			<div class="form-group share-url">
				<label class="control-label col-sm-2">问卷网址</label>
				<div class="col-sm-9">
					<span class="help-block">您可复制该网址给他人，也可直接通过微信端打开问卷，点击右上角分享出去</span>
					<span class="url"></span>
				</div>
			</div>
			<div class="form-group disable-edit">
				<label class="control-label col-sm-2">类型</label>
				<div class="col-sm-9 anonymous">
					<label class="radio-inline">
						<input name="anonymous" type="radio" value="1" class="px">
						<span class="lbl">匿名填写</span>
					</label>
					<label class="radio-inline">
						<input name="anonymous" type="radio" value="2" checked="checked" class="px">
						<span class="lbl">实名填写</span>
					</label>
				</div>
			</div>
			<div class="form-group disable-edit">
				<label class="control-label col-sm-2">允许重复填写</label>
				<div class="col-sm-9 repeat">
					<label class="radio-inline">
						<input name="repeat" type="radio" value="1" class="px">
						<span class="lbl">允许</span>
					</label>
					<label class="radio-inline">
						<input name="repeat" type="radio" value="2" checked="checked" class="px">
						<span class="lbl">不允许</span>
					</label>
				</div>
			</div>
			<div class="form-group disable-edit">
				<label class="control-label col-sm-2">自动提醒</label>
				<div class="col-sm-9 remind">
					问卷结束前
					<input id="remind" name="remind" type="text" value="10" maxlength="3" class="form-control" style="width: 50px; display: inline;" />
					分钟进行消息提醒
				</div>
			</div>
			<div class="form-group disable-edit">
				<label class="control-label col-sm-2">定时发布</label>
				<div class="col-sm-9 release">
					<div style="margin-bottom: 10px;">
						<label class="radio-inline">
							<input name="release" type="radio" value="0" checked="checked" class="px">
							<span class="lbl">关闭</span>
						</label>
						<label class="radio-inline">
							<input name="release" type="radio" value="1" class="px">
							<span class="lbl">开启</span>
						</label>
						<lable class="control-label help-block inline">（开启定时发布后点击发布，问卷将于设定时间自动发布出去，在此之间显示为预发布状态）</lable>
					</div>
					<div id="release_datetime" class="input-daterange input-group" style="display: none;">
						<div style="width: 300px;">
							<input id="release_date" name="release_date" type="text" value="{date('Y-m-d')}" placeholder="发布日期" class="form-control" style="width: 150px;" />
							<input id="release_time" name="release_time" type="text" placeholder="发布时间" class="form-control" style="width: 150px;" />
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-2"></div>
				<div class="col-sm-9">
					<div class="form-title">
						<h2>问题表单设置</h2>
					</div>
				</div>
			</div>
			<div class="form-group custom-form">
				<div class="col-sm-2"></div>
				<div id="form-view" class="col-sm-6">
					<ul></ul>
					<div class="toolbar">
						<div class="tip">
							<span class="glyphicon glyphicon-plus"></span>
							<span class="text">添加新字段</span>
						</div>
						<div class="types">
							<div class="col-sm-3">
								<button type="button" data-type="text" class="btn">单行文字</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="textarea" class="btn">多行文字</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="radio" class="btn">单项选择</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="checkbox" class="btn">多项选择</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="number" class="btn">数字</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="date" class="btn">日期</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="time" class="btn">时间</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="datetime" class="btn">日期时间</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="score" class="btn">评分</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="note" class="btn">段落说明</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="image" class="btn">上传图片</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="file" class="btn">上传文件</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="select" class="btn">下拉框</button>
							</div>
							<div class="col-sm-9" style="height: 41px;"></div>
							<div class="col-sm-3">
								<button type="button" data-type="username" class="btn ">姓名</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="mobile" class="btn">手机</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="email" class="btn">邮箱</button>
							</div>
							<div class="col-sm-3">
								<button type="button" data-type="address" class="btn">地址</button>
							</div>
						</div>
					</div>
				</div>
				<div id="field-setting" class="col-sm-3"></div>
				<div class="clearfix"></div>
			</div>
			<div class="form-group">
				<div class="col-sm-2"></div>
				<div class="col-sm-9 action">
					<button id="savedraft" type="button" class="btn btn-primary">保存为草稿</button>
					<button id="release" type="button" class="btn btn-primary">发布</button>
					<button id="goback" type="button" class="btn btn-default">返回</button>
				</div>
			</div>
            {*选人控件数据展示*}
            <template id="persons-li-template">
                <li>
                    <div class="item">
                        <i class="glyphicon glyphicon-folder-close department"></i>
                        <i class="glyphicon glyphicon-tag tag"></i>
                        <img class="person" />
                        <label></label>
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                    </div>
                </li>
            </template>
			{*单行文字*}
			<template id="text-field-template">
				<li data-type="text">
                    <div class="field">
                        <span class="title"></span>
                        <input type="text" disabled="disabled" class="form-control tip" />
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*多行文字*}
			<template id="textarea-field-template">
				<li data-type="textarea">
                    <div class="field">
                        <span class="title"></span>
                        <textarea disabled="disabled" class="form-control tip"></textarea>
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*单项选择*}
			<template id="radio-field-template">
				<li data-type="radio">
                    <div class="field">
                        <span class="title"></span>
                        <span class="help-block inline tip"></span>
                        <div class="option">
                            <label class="radio">
                                <input type="radio" disabled="disabled" class="px" />
                                <span class="lbl text"></span>
                            </label>
                        </div>
						<div class="option-other">
							<label class="radio-inline">
								<input type="radio" disabled="disabled" class="px" />
								<span class="lbl text">其他</span>
							</label>
                            <input type="text" disabled="disabled" class="form-control input-sm input-inline" />
						</div>
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*多项选择*}
			<template id="checkbox-field-template">
				<li data-type="checkbox">
                    <div class="field">
                        <span class="title"></span>
                        <span class="help-block inline tip"></span>
                        <div class="option">
                            <label class="checkbox">
                                <input type="checkbox" disabled="disabled" class="px" />
                                <span class="lbl text"></span>
                            </label>
                        </div>
						<div class="option-other">
							<label class="checkbox-inline">
								<input type="checkbox" disabled="disabled" class="px" />
								<span class="lbl text">其他</span>
							</label>
                            <input type="text" disabled="disabled" class="form-control input-sm input-inline" />
						</div>
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*数字*}
			<template id="number-field-template">
				<li data-type="number">
                    <div class="field">
                        <span class="title"></span>
                        <input type="text" disabled="disabled" class="form-control tip" />
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*日期*}
			<template id="date-field-template">
				<li data-type="date">
                    <div class="field">
                        <span class="title"></span>
                        <input type="text" disabled="disabled" class="form-control tip" />
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*时间*}
			<template id="time-field-template">
				<li data-type="time">
                    <div class="field">
                        <span class="title"></span>
                        <input type="text" disabled="disabled" class="form-control tip" />
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*日期时间*}
			<template id="datetime-field-template">
				<li data-type="datetime">
                    <div class="field">
                        <span class="title"></span>
                        <input type="text" disabled="disabled" class="form-control tip" />
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*评分*}
			<template id="score-field-template">
				<li data-type="score">
                    <div class="field">
                        <span class="title"></span>
                        <span class="help-block inline tip"></span>
                        <div class="option">
                            <i class="iconfont icon-xingxingpingfen image"></i>
                        </div>
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*段落说明*}
			<template id="note-field-template">
				<li data-type="note">
                    <div class="field">
                        <span class="title"></span>
                        <span class="help-block tip">说明内容</span>
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*上传图片*}
			<template id="image-field-template">
				<li data-type="image">
                    <div class="field">
                        <span class="title"></span>
                        <span class="help-block inline tip"></span>
                        <i class="iconfont icon-shangchuantupian image block"></i>
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*上传文件*}
			<template id="file-field-template">
				<li data-type="file">
                    <div class="field">
                        <span class="title"></span>
                        <span class="help-block inline tip"></span>
                        <i class="iconfont icon-shangchuanwenjian image block"></i>
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*下拉框*}
			<template id="select-field-template">
				<li data-type="select">
                    <div class="field">
                        <span class="title"></span>
                        <span class="help-block inline tip"></span>
                        <div>
                            <select disabled="disabled" class="form-control inline" style="width: 50%;">
                                <option>请选择</option>
                            </select>
                            <input type="text" disabled="disabled" class="form-control inline" style="width: 49%;" />
                        </div>
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*姓名*}
			<template id="username-field-template">
				<li data-type="username">
                    <div class="field">
                        <span class="title"></span>
                        <input type="text" disabled="disabled" class="form-control tip" />
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*手机*}
			<template id="mobile-field-template">
				<li data-type="mobile">
                    <div class="field">
                        <span class="title"></span>
                        <input type="text" disabled="disabled" class="form-control tip" />
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*邮箱*}
			<template id="email-field-template">
				<li data-type="email">
                    <div class="field">
                        <span class="title"></span>
                        <input type="text" disabled="disabled" class="form-control tip" />
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			{*地址*}
			<template id="address-field-template">
				<li data-type="address">
                    <div class="field">
                        <span class="title"></span>
                        <input type="text" disabled="disabled" class="form-control tip" />
                        <div class="remove">
                            <i class="iconfont icon-cuowu"></i>
                        </div>
                        <div class="order">
                            <i class="glyphicon glyphicon-align-justify"></i>
                        </div>
                    </div>
				</li>
			</template>
			<template id="text-setting-template">
				<div data-type="text" class="setting">
					<div class="typename">单行文字</div>
					<div class="view">
						<label>标题</label>
						<input type="text" placeholder="单行文字" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
						<label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
						<label class="checkbox">
							<input type="checkbox" disabled="disabled" class="px enabled-min" />
							<span class="lbl lblfix">
								最少填
								<input type="text" maxlength="10" class="form-control input-xs min" />
								个字符
							</span>
						</label>
						<label class="checkbox">
							<input type="checkbox" disabled="disabled" class="px enabled-max" />
							<span class="lbl lblfix">
								最多填
								<input type="text" maxlength="10" class="form-control input-xs max" />
								个字符
							</span>
						</label>
					</div>
				</div>
			</template>
			<template id="textarea-setting-template">
				<div data-type="textarea" class="setting">
					<div class="typename">多行文字</div>
					<div class="view">
						<label>标题</label>
						<input type="text" placeholder="多行文字" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
						<label class="checkbox">
							<input type="checkbox" disabled="disabled" class="px enabled-min" />
							<span class="lbl lblfix">
								最少填
								<input type="text" maxlength="10" class="form-control input-xs min" />
								个字符
							</span>
						</label>
						<label class="checkbox">
							<input type="checkbox" disabled="disabled" class="px enabled-max" />
							<span class="lbl lblfix">
								最多填
								<input type="text" maxlength="10" class="form-control input-xs max" />
								个字符
							</span>
						</label>
					</div>
				</div>
			</template>
			<template id="radio-setting-template">
				<div data-type="radio" class="setting">
					<div class="typename">单项选择</div>
					<div class="view">
						<label>标题</label>
						<input type="text" placeholder="单项选择" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
						<label>选项</label>
						<div class="option">
							<label class="radio">
								<input type="radio" disabled="disabled" class="px" />
								<span class="lbl lblfix">
									<input type="text" placeholder="选项" class="form-control text" />
								</span>
								<i class="glyphicon glyphicon-plus-sign add"></i>
								<i class="glyphicon glyphicon-minus-sign delete"></i>
							</label>
						</div>
						<label class="open-other">添加“其他”选项</label>
						<label class="checkbox option-other">
							<input type="checkbox" disabled="disabled" class="px enabled-other" />
							<span class="lbl lblfix">
								<input type="text" placeholder="其他" class="form-control text" />
							</span>
							<i class="glyphicon glyphicon-minus-sign close-other"></i>
						</label>
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
					</div>
				</div>
			</template>
			<template id="checkbox-setting-template">
				<div data-type="checkbox" class="setting">
					<div class="typename">多项选择</div>
					<div class="view">
						<label>标题</label>
						<input type="text" placeholder="多项选择" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
						<label>选项</label>
						<div class="option">
							<label class="checkbox">
								<input type="checkbox" disabled="disabled" class="px" />
								<span class="lbl lblfix">
									<input type="text" placeholder="选项" class="form-control text" />
								</span>
								<i class="glyphicon glyphicon-plus-sign add"></i>
								<i class="glyphicon glyphicon-minus-sign delete"></i>
							</label>
						</div>
						<label class="open-other">添加“其他”选项</label>
						<label class="checkbox option-other">
							<input type="checkbox" disabled="disabled" class="px enabled-other" />
							<span class="lbl lblfix">
								<input type="text" placeholder="其他" class="form-control text" />
							</span>
							<i class="glyphicon glyphicon-minus-sign close-other"></i>
						</label>
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
						<label class="checkbox">
							<input type="checkbox" disabled="disabled" class="px enabled-min" />
							<span class="lbl lblfix">
								最少选择
								<input type="text" maxlength="10" class="form-control input-xs min" />
								个选项
							</span>
						</label>
						<label class="checkbox">
							<input type="checkbox" disabled="disabled" class="px enabled-max" />
							<span class="lbl lblfix">
								最多选择
								<input type="text" maxlength="10" class="form-control input-xs max" />
								个选项
							</span>
						</label>
					</div>
				</div>
			</template>
			<template id="number-setting-template">
				<div data-type="number" class="setting">
					<div class="typename">数字</div>
					<div class="view">
						<label>标题</label>
						<input type="text" placeholder="数字" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
						<label class="checkbox">
							<input type="checkbox" disabled="disabled" class="px enabled-min" />
							<span class="lbl lblfix">
								最小值 <input type="text" maxlength="10" class="form-control input-xs min" />
							</span>
						</label>
						<label class="checkbox">
							<input type="checkbox" disabled="disabled" class="px enabled-max" />
							<span class="lbl lblfix">
								最大值 <input type="text" maxlength="10" class="form-control input-xs max" />
							</span>
						</label>
					</div>
				</div>
			</template>
			<template id="date-setting-template">
				<div data-type="date" class="setting">
					<div class="typename">日期</div>
					<div class="view">
						<label>标题</label>
						<input type="text" placeholder="日期" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
					</div>
				</div>
			</template>
			<template id="time-setting-template">
				<div data-type="time" class="setting">
					<div class="typename">时间</div>
					<div class="view">
						<label>标题</label>
						<input type="text" placeholder="时间" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
					</div>
				</div>
			</template>
			<template id="datetime-setting-template">
				<div data-type="datetime" class="setting">
					<div class="typename">日期时间</div>
					<div class="view">
						<label>标题</label>
						<input type="text" placeholder="日期时间" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
					</div>
				</div>
			</template>
			<template id="score-setting-template">
				<div data-type="score" class="setting">
					<div class="typename">评分</div>
					<div class="view">
						<label>标题</label>
						<input type="text" placeholder="评分" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
						<label>满分</label>
						<select class="form-control max">
							<option>3</option>
							<option>5</option>
							<option>10</option>
						</select>
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
					</div>
				</div>
			</template>
			<template id="note-setting-template">
				<div data-type="note" class="setting">
					<div class="typename">段落说明</div>
					<div class="view">
						<label>标题</label>
						<input type="text" placeholder="段落说明" class="form-control title" />
						<label>说明内容</label>
						<textarea placeholder="说明内容" class="form-control tip"></textarea>
					</div>
				</div>
			</template>
			<template id="image-setting-template">
				<div data-type="image" class="setting">
					<div class="typename">上传图片</div>
					<div class="view">
						<label>标题</label>
						<input type="text" placeholder="上传图片" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
						<label>图片数量</label>
						<label class="block">
							最多上传
							<select class="form-control inline select-xs max">
								<option>1</option>
								<option>2</option>
								<option>3</option>
								<option>4</option>
								<option>5</option>
							</select>
							张
						</label>
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
					</div>
				</div>
			</template>
			<template id="file-setting-template">
				<div data-type="file" class="setting">
					<div class="typename">上传文件</div>
					<div class="view">
						<label>标题</label>
						<input type="text" placeholder="上传文件" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
					</div>
				</div>
			</template>
			<template id="select-setting-template">
				<div data-type="select" class="setting">
					<div class="typename">下拉框</div>
					<div class="view">
						<label>标题</label>
						<input type="text" placeholder="下拉框" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
						<label>选项</label>
						<div class="option">
							<label class="radio select">
								<input type="radio" disabled="disabled" class="px" />
								<span class="lbl lblfix">
									<input type="text" placeholder="选项" class="form-control text" />
								</span>
								<i class="glyphicon glyphicon-plus-sign add"></i>
								<i class="glyphicon glyphicon-minus-sign delete"></i>
							</label>
						</div>
						<label class="open-other">添加“其他”选项</label>
						<label class="radio option-other">
							<input type="radio" disabled="disabled" class="px enabled-other" />
							<span class="lbl lblfix">
								<input type="text" placeholder="其他" class="form-control text" />
							</span>
							<i class="glyphicon glyphicon-minus-sign close-other"></i>
						</label>
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
					</div>
				</div>
			</template>
			<template id="username-setting-template">
				<div data-type="username" class="setting">
					<div class="typename">姓名</div>
					<div class="view">
						<label>标题</label>
						<input type="text" value="姓名" placeholder="姓名" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
					</div>
				</div>
			</template>
			<template id="mobile-setting-template">
				<div data-type="mobile" class="setting">
					<div class="typename">手机</div>
					<div class="view">
						<label>标题</label>
						<input type="text" value="手机" placeholder="手机" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
					</div>
				</div>
			</template>
			<template id="email-setting-template">
				<div data-type="email" class="setting">
					<div class="typename">邮箱</div>
					<div class="view">
						<label>标题</label>
						<input type="text" value="邮箱" placeholder="邮箱" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
					</div>
				</div>
			</template>
			<template id="address-setting-template">
				<div data-type="address" class="setting">
					<div class="typename">地址</div>
					<div class="view">
						<label>标题</label>
						<input type="text" value="地址" placeholder="地址" class="form-control title" />
						<label>提示信息</label>
						<input type="text" placeholder="提示信息" class="form-control tip" />
                        <label class="block">设置</label>
						<label class="checkbox">
							<input type="checkbox" class="px required" />
							<span class="lbl">必填</span>
						</label>
						<label class="checkbox">
							<input type="checkbox" class="px more" />
							<span class="lbl">需填写详细地址</span>
						</label>
					</div>
				</div>
			</template>
		</form>
	</div>
</div>

<script type="text/javascript" src="{$JSDIR}jquery.blockUI.min.js"></script>
<script type="text/javascript" src="{$JSDIR}dragsort/jquery.dragsort-0.5.2.min.js"></script>
<script type="text/javascript">
var q = {
	qu_id: {$qu_id},
	share_id: "{$share_id}",
	release_status: 2
};

var chooseData = {
	selectedDepartment: [],
	selectedPersons: [],
	selectedTags: []
};

var idCache = [];

$(function (){
	blockUI();

	var title;

	if ({$copy}) {
		title = "复制";
	}
	else if (q.qu_id > 0) {
		title = "编辑";
	}

	if (title) {
		var document_obj = $(document);
		var sub_navbar = $("#sub-navbar h1");
		
		document_obj.attr("title", document_obj.attr("title").replace("新增", title));
		sub_navbar.html(sub_navbar.html().replace("新增", title));
	}

	var date_option = {
		todayBtn: "linked",
		orientation: "auto auto",
		startDate: new Date()
	};

	var time_option = { showMeridian: false };

	$("#deadline_date").datepicker(date_option);
	$("#deadline_time").timepicker(time_option);

	$("#selectall").click(selectAllPerson);
	$("#selectper").click(function () {
		selectPerson();
		choosePerson();
	});

	$(".share :radio").change(function () {
		$(".share-url").toggle();
	});

	$(".share-url .url").text("{$share_url}" + q.share_id);
	
	$(".release :radio").change(function () {
		$("#release_datetime").toggle();
	});

	$("#release_date").datepicker(date_option);
	$("#release_time").timepicker(time_option);

    $("#form-view ul").dragsort({
        dragSelector: ".order",
        placeHolderTemplate: "<li class=\"placeholder\"><div></div></li>",
        dragEnd: orderField
    });

	$("#form-view .toolbar .tip").click(showTypes);
	$("#form-view .toolbar .types button").click(function () {
		addField($(this).data("type"));
	});

	$("#savedraft").click(function () { saveData(2 ,false); });
	$("#release").click(function () {
		var status = Number($(".release :checked").val()) ? 1 : 3;
		saveData(status, true);
	});

	$("#goback").click(function () {
		window.location.href = "{$list_url}";
	});

	getClassData();
});

//显示页面遮罩层
function blockUI() {
	var html = '<div class="loading-message"><img src="{$IMGDIR}loading-grey.gif" /></div>';

	$.blockUI({
		message: html,
		baseZ: 1100,
		css: {
			padding: "0",
			border: "0",
			backgroundColor: "none"
		},
		overlayCSS: {
			backgroundColor: "#000000",
			opacity: 0.1,
			cursor: "wait"
		}
	});
}

//关闭页面遮罩层
function unblockUI() {
	$.unblockUI();
}

//可见范围选择所有人
function selectAllPerson() {
	var isallInput = $("#is_all");

	if (Number(isallInput.val()) != -1) {
		$("#selectall").addClass("btn-info");
		$("#selectper").removeClass("btn-info");
		$("#persons").hide().find("li").remove();

		isallInput.val("-1");

		for (var k in chooseData) {
			chooseData[k] = [];
		}
	}
}

//可见范围选择特定人员
function selectPerson() {
	var isallInput = $("#is_all");

	if (Number(isallInput.val()) != 0) {
		$("#selectall").removeClass("btn-info");
		$("#selectper").addClass("btn-info");
		$("#persons").show();

		isallInput.val("0");
	}
}

//选人组件
function choosePerson() {
	//js深坑，如果直接使用chooseData，当用户选择后点击取消按钮，chooseData会一并改变
	var selecteds = $.extend(true, new Object(), chooseData);

	var config = {
		person: {
			isPerson: true,
			isSingle: false,
			isShow: true
		},
		department: {
			isDepartment: true,
			isSingle: false
		},
		tag: {
			isTags: true,
			isSingle: false
		}
	};

	advancedChoose(selecteds, config, function(data) {
		chooseData.selectedDepartment = data.selectedDepartment;
		chooseData.selectedPersons = data.selectedPersons;
		chooseData.selectedTags = data.selectedTags;

		choosePerson_callback();
	});
}

//选人组件回调函数
function choosePerson_callback() {
    var html = $("#persons-li-template").html();
	var persons = $("#persons ul");

	persons.children().remove();

    for (var type in chooseData) {
        for (var k in chooseData[type]) {
			var data = chooseData[type][k];
            var li = $(html).data("type", type).data("data", data);
            var label = li.find("label");

            switch (type) {
                case "selectedDepartment":
                    label.text(data.name);
                    li.find(".person, .tag").remove();
                    break;
                case "selectedPersons":
                    label.text(data.m_username);
                    li.find(".person").attr("src", data.m_face);
                    li.find(".department, .tag").remove();
                    break;
                case "selectedTags":
                    label.text(data.name);
                    li.find(".department, .person").remove();
                    break;
            }

			//判断问卷状态为草稿
			if (q.release_status == 2) {
				li.find(".remove").click(function () {
					deletePersonItem($(this).parent().parent());
				});
			}
			else {
				li.find(".remove").remove();
			}

            persons.append(li);
        }
    }
}

//删除已选择的人员
function deletePersonItem(li) {
	var type = li.data("type");
	var data = li.data("data");
	var key = 0;

	for (var k in chooseData[type]) {
		var obj = chooseData[type][k];

		switch (type) {
			case "selectedDepartment":
				if (data.id != obj.id) {
					continue;
				}
				break;
			case "selectedPersons":
				if (data.m_uid != obj.m_uid) {
					continue;
				}
				break;
			case "selectedTags":
				if (data.laid != obj.laid) {
					continue;
				}
				break;
		}

		key = k;
		break;
	}

	if (key) {
		chooseData[type].splice(key, 1);
		li.remove();
	}
}

//显示字段类型按钮
function showTypes() {
	$("#form-view .toolbar .tip").hide();
	$("#form-view .toolbar .types").show()
}

//隐藏字段类型按钮
function hideTypes() {
	$("#form-view .toolbar .types").hide();
	$("#form-view .toolbar .tip").show();
}

//创建纯数字唯一ID
function createUniqueID(id) {
	id = id ? id + 1 : new Date().getTime();

	if (idCache.indexOf(id) == -1) {
		idCache.push(id);
		return id;
	}

	return createUniqueID(id);
}

//添加字段
function addField(data) {
	if (typeof(data) != "object") {
		data = { type: data };
	}

	//通用id，加上前缀，既可获取对应的field或setting
	var common_id = data.id ? data.id : createUniqueID();
	var field = $($("#" + data.type + "-field-template").html());
	var setting = $($("#" + data.type + "-setting-template").html());
	var cache = new Object();

	field.attr("id", "field-" + common_id).data("common_id", common_id).click(function () { activeField(field); });
	setting.attr("id", "setting-" + common_id).data("common_id", common_id);

	//设置删除事件
	field.find(".remove").click(function () { deleteField(field); });

	//设置标题、提示信息事件
	cache.settingTitle = setting.find(".title")
			.keyup(function () { changeFieldTitle(setting, this.value); })
			.blur(function () { changeFieldTitle(setting, this.value); });

	cache.settingTip = setting.find(".tip")
			.keyup(function () { changeFieldTip(setting, this.value); })
			.blur(function () { changeFieldTip(setting, this.value); });

	//设置必填选项事件
	cache.settingRequired = setting.find(".required").change(function () { changeFieldRequired(setting) });

	//设置事件
	switch (data.type) {
		//单行文字
		case "text":
		//多行文字
		case "textarea":
		//数字
		case "number":
			cache.settingMin = setting.find(".min").change(function () { changeFieldMin(setting, $(this)); });
			cache.settingMax = setting.find(".max").change(function () { changeFieldMax(setting, $(this)); });
			break;
		//单项选择
		case "radio":
		//多项选择
		case "checkbox":
		//下拉框
		case "select":
			var option_id = data.otheroption ? data.otheroption.id : createUniqueID();

			cache.settingOpenOther = setting.find(".open-other").click(function () { openOtherOption(setting); });
			cache.settingCloseOther = setting.find(".close-other").click(function () { closeOtherOption(setting); });

			cache.settingOtherOption = setting.find(".option-other")
					.attr("id", "setting-option-" + option_id)
					.data("option_id", option_id);

			//表单预览，下拉框不显示其他选项，不用设置事件
			if (data.type != "select") {
				cache.settingOtherOption.find(".text")
						.keyup(function () { changeOptionText(cache.settingOtherOption, this.value); })
						.blur(function () { changeOptionText(cache.settingOtherOption, this.value); });

				field.find(".option-other").attr("id", "field-option-" + option_id).data("option_id", option_id);
			}

			if (data.type == "checkbox") {
				cache.settingMin = setting.find(".min").change(function () { changeFieldMin(setting, $(this)); });
				cache.settingMax = setting.find(".max").change(function () { changeFieldMax(setting, $(this)); });
			}
			break;
		//评分
		case "score":
			cache.settingMax = setting.find(".max").change(function () { changeScore(setting, $(this)); });
			break;
	}

	//添加到页面
	$("#form-view ul").append(field);
	$("#field-setting").append(setting);

	//以上为初始化控件事件，以下为设置控件的各种值

	//设置标题
	if (typeof(data.title) != "undefined") {
		cache.settingTitle.val(data.title);
	}

	changeFieldTitle(setting, data.title);

	//设置提示信息
	cache.settingTip.val(data.placeholder);
	changeFieldTip(setting, data.placeholder);

	//设置必填
	if (data.required && data.required == "true") {
		cache.settingRequired.prop("checked", true);
		changeFieldRequired(setting);
	}

	switch (data.type) {
		//单行文字
		case "text":
		//多行文字
		case "textarea":
		//数字
		case "number":
			if (data.min) {
				setting.find(".enabled-min").prop("checked", true);
				cache.settingMin.val(data.min);
			}

			if (data.max) {
				setting.find(".enabled-max").prop("checked", true);
				cache.settingMax.val(data.max);
			}
			break;
		//单项选择
		case "radio":
		//多项选择
		case "checkbox":
		//下拉框
		case "select":
			//移除选项模板
            field.find(".option").children().remove();
            setting.find(".option").children().remove();

			//添加选项
            addFieldOption(setting, data.option);

			if (data.otheroption) {
				cache.settingOtherOption.find(".text").val(data.otheroption.value);

				if (data.type != "select") {
					changeOptionText(cache.settingOtherOption, data.otheroption.value);
				}

				openOtherOption(setting);
			}

			if (data.type == "checkbox") {
				if (data.min) {
					setting.find(".enabled-min").prop("checked", true);
					cache.settingMin.val(data.min);
				}

				if (data.max) {
					setting.find(".enabled-max").prop("checked", true);
					cache.settingMax.val(data.max);
				}
			}
			break;
		//评分
		case "score":
			field.find(".option").children().remove();

			if (data.max) {
				cache.settingMax.val(data.max);
			}

			changeScore(setting, cache.settingMax);
			break;
		//图片
		case "image":
			if (data.max) {
				setting.find(".max").val(data.max);
			}
			break;
		//地址
		case "address":
			if (data.more == "true") {
				setting.find(".more").prop("checked", true);
			}
			break;
	}

	activeField(field);
}

//删除字段
function deleteField(field) {
	if (confirm("您确认删除此字段吗？")) {
		var prev = field.prev();

		if (prev.length == 0) {
			prev = field.next();
		}

		$("#setting-" + field.data("common_id")).remove();
		field.remove();

		if (prev.length) {
			activeField(prev);
		}
	}

}

//激活字段
function activeField(field) {
	//判断字段是否已激活
    if (!field.hasClass("active")) {
        var top = field.position().top;

        $("#form-view .active").removeClass("active");
        $("#field-setting .setting").hide();

        field.addClass("active");
        $("#setting-" + field.data("common_id")).css("margin-top", top + "px").fadeIn("normal");
    }

    hideTypes();
}

//更改字段标题
function changeFieldTitle(setting, title) {
    $("#field-" + setting.data("common_id") + " .title")
            .text(title ? title : setting.find(".title").attr("placeholder"));
}

//更改字段提示信息
function changeFieldTip(setting, tip) {
    var fieldTip = $("#field-" + setting.data("common_id") + " .tip");

    if (fieldTip.is("input") || fieldTip.is("textarea")) {
        fieldTip.val(tip ? tip : "");
    }
    else {
        if (!tip) {
            tip = setting.find(".tip").attr("placeholder");
        }

        if (setting.data("type") == "note") {
            fieldTip.text(tip);
        }
        else {
            fieldTip.text("（" + tip + "）");
        }
    }
}

//添加字段选项
function addFieldOption(setting, data, prev) {
	if (data == null || typeof(data) != "object") {
		data = [{
			id: createUniqueID()
		}];
	}

	var type = setting.data("type");
	var fieldOptionHtml = $($("#" + type + "-field-template").html()).find(".option").html();
	var settingOptionHtml = $($("#" + type + "-setting-template").html()).find(".option").html();

	for (var k in data) {
		var option_id = data[k].id;
		var text = data[k].value;

		//表单预览，下拉框不显示选项
		if (type != "select") {
			var fo = $(fieldOptionHtml).attr("id", "field-option-" + option_id).data("option_id", option_id);

			if (prev) {
				$("#field-option-" + prev.data("option_id")).after(fo);
			}
			else {
				$("#field-" + setting.data("common_id") + " .option").append(fo);
			}
		}

		var so = $(settingOptionHtml).attr("id", "setting-option-" + option_id).data("option_id", option_id);
		var soText = so.find(".text").val(text);

		//表单预览，下拉框不显示选项
		if (type != "select") {
			soText.keyup(function () { changeOptionText($(this).parent().parent(), this.value); })
					.blur(function () { changeOptionText($(this).parent().parent(), this.value); });
		}
		
		so.find(".add").click(function () { addFieldOption(setting, null, $(this).parent()); });
		so.find(".delete").click(function () { deleteFieldOption(setting, $(this).parent()); });

		if (prev) {
			prev.after(so);
		}
		else {
			setting.find(".option").append(so);
		}

		changeOptionText(so, text);
	}
}

//更改字段选项文本
function changeOptionText(option, text) {
	$("#field-option-" + option.data("option_id") + " .text")
			.text(text ? text : option.find(".text").attr("placeholder"));
}

//删除字段选项
function deleteFieldOption(setting, option) {
	if (setting.find(".option>label").length > 1) {
		$("#field-option-" + option.data("option_id")).remove();
		option.remove();
	}
}

//打开字段其他选项
function openOtherOption(setting) {
	var otheroption = setting.find(".option-other");

	if (otheroption.is(":hidden")) {
		otheroption.show().find(".enabled-other").prop("checked", true);

		//表单预览，下拉框不显示其他选项
		if (setting.data("type") != "select") {
			$("#field-option-" + otheroption.data("option_id")).show();
		}
	}
}

//关闭字段其他选项
function closeOtherOption(setting) {
	var otheroption = setting.find(".option-other");

	otheroption.find(".enabled-other").prop("checked", false);
	otheroption.find(".text").val("");
	otheroption.hide();

	//表单预览，下拉框不显示其他选项
	if (setting.data("type") != "select") {
		$("#field-option-" + otheroption.data("option_id")).hide();
		changeOptionText(otheroption);
	}
}

//更改字段必填选项
function changeFieldRequired(setting) {
	$("#field-" + setting.data("common_id")).find(".title").toggleClass("required");
}

//判断是否为大于0的正整数
function isSignInt(value) {
    return /^[1-9]+\d*$/.test(value);
}

//更改字段最小值
function changeFieldMin(setting, input) {
    var value = input.val();
    var enabledMin = setting.find(".enabled-min");

    if (value.length > 0) {
        if (isSignInt(value)) {
            var max = setting.find(".max").val();

            if (!isSignInt(max) || Number(value) <= Number(max)) {
                enabledMin.prop("checked", true);
                return;
            }
        }

        input.val("");
    }

    enabledMin.prop("checked", false);
}

//更改字段最大值
function changeFieldMax(setting, input) {
    var value = input.val();
    var enabledMax = setting.find(".enabled-max");

    if (value.length > 0) {
        if (isSignInt(value)) {
            var min = setting.find(".min").val();

            if (!isSignInt(min) || Number(value) >= Number(min)) {
                enabledMax.prop("checked", true);
                return;
            }
        }

        input.val("");
    }

    enabledMax.prop("checked", false);
}

//更改评分满分值
function changeScore(setting, select) {
	var option = $("#field-" + setting.data("common_id") + " .option");
	var image = option.find(".image");
	var value = select.val();

	if (image.length != value) {
		image.remove();

		var imageHtml = $($("#" + setting.data("type") + "-field-template").html()).find(".option").html();

		for (var i = 0; i < value; i++) {
			option.append(imageHtml);
		}
	}
}

//字段排序
function orderField() {
	var activeField = $("#form-view .active");

	if (activeField.length) {
		var top = activeField.position().top;
		$("#setting-" + activeField.data("common_id")).animate({ "margin-top": top + "px" }, "normal");
	}
}

//获取问卷分类数据
function getClassData() {
	$.ajax({
		url: "/Questionnaire/Apicp/Classify/List",
		type: "get",
		dataType: "json",
		success: function (data) {
			var list = data.result.list;
			var select = $("#qc_id");

			for (var k in list) {
				select.append("<option value=\"" + list[k].qc_id + "\">" + list[k].name + "</option>");
			}

			//判断是否为编辑问卷
			if (q.qu_id > 0) {
				getData();
			}
			else {
				$("#save").hide();
				unblockUI();
			}
		},
		error: function (e) {
			window.alert(e.message);
			window.location.href = "{$list_url}";
		}
	});
}

//获取问卷数据
function getData() {
	$.ajax({
		url: "/Questionnaire/Apicp/Questionnaire/Data",
		type: "post",
		dataType: "json",
		data: q,
		success: function (data) {
			if (data.result.result) {
				q = data.result.q;

				//判断是否为复制
				if ({$copy}) {
					q.qu_id = 0;
					q.share_id = "{$share_id}";
					q.release_status = 2;
				}

				$("#title").val(q.title);
				$("#body").val(q.body);
				$("#qc_id").val(q.qc_id);
				$("#deadline_date").val(q.deadline_date);
				$("#deadline_time").val(q.deadline_time);
				$(".share-url .url").text("{$share_url}" + q.share_id);

				//判断可见范围为特定人员
				if (q.is_all == 0) {
					selectPerson();

					if (data.result.viewranges) {
						var viewranges = data.result.viewranges;

						for (var type in viewranges) {
							switch (type) {
								case "departments":
									chooseData.selectedDepartment = viewranges[type];
									break;
								case "persons":
									chooseData.selectedPersons = viewranges[type];
									break;
								case "tags":
									chooseData.selectedTags = viewranges[type];
									break;
							}
						}

						choosePerson_callback();
					}
				}
				else {
					$("#is_all").val(q.is_all);
				}

				if (q.share != 2) {
					$(".share :radio").eq(0).click();
				}

				if (q.anonymous != 2) {
					$(".anonymous :radio").eq(0).prop("checked", true);
				}

				if (q.repeat != 2) {
					$(".repeat :radio").eq(0).prop("checked", true);
				}

				$("#remind").val(q.remind);

				if (q.release > 0) {
					$(".release :radio").eq(1).click();
					$("#release_date").val(q.release_date);
					$("#release_time").val(q.release_time);
				}

                if (q.field) {
                    for (var i = 0; i < q.field.length; i++) {
                        addField(q.field[i]);
                    }

                    activeField($("#form-view li:first"));
                }

				//判断问卷状态为预发布或进行中
				if (q.release_status != 2) {
					$(".disable-edit").find("button, input").attr("disabled", true);
					$("#savedraft").remove();
				}

				unblockUI();
			}
			else {
				window.alert("无法获取问卷数据");
				window.location.href = "{$list_url}";
			}
		},
		error: function (e) {
			window.alert(e.message);
			window.location.href = "{$list_url}";
		}
	});
}

//获取时间戳
function getUnixTime(str) {
	var date = new Date(str.replace(/-/g,'/'));
	return Number(date.getTime().toString().substr(0, 10));
}

//获取当前时间戳
function getNowTime() {
	return parseInt(new Date().getTime() / 1000);
}

//获取表单字段数据
function getFieldData(check) {
	var fieldData = [];

	$("#form-view li").each(function (i) {
		var field = $(this);
		var common_id = field.data("common_id");
		var setting = $("#setting-" + common_id);
		var typename = setting.find(".typename").text();

		var data = {
			id: common_id,
			type: setting.data("type"),
			title: $.trim(setting.find(".title").val()),
			placeholder: $.trim(setting.find(".tip").val()),
			required: setting.find(".required").is(":checked"),
			order: i
		};

		if (check && data.title.length == 0) {
			activeField(field);
			throw "请输入" + typename + "字段的标题";
		}

		//单行文字、多行文字、多项选择、数字字段获取最小、最大值，评分、图片字段获取最大值
		if (["text", "textarea", "checkbox", "number", "score", "image"].indexOf(data.type) > -1) {
			var min = setting.find(".min").val();
			var max = setting.find(".max").val();

			if (isSignInt(min)) {
				data.min = min;
			}

			if (isSignInt(max)) {
				data.max = max;
			}
		}

		//单项选择、多项选择、下拉框字段获取选项
		if (["radio", "checkbox", "select"].indexOf(data.type) > -1) {
			data.option = [];

			setting.find(".option").children().each(function (j) {
				var so = $(this);
				var option_id = so.data("option_id");
				var text = $.trim(so.find(".text").val());

				if (check && text.length == 0) {
					throw "请输入" + typename + "第" + (j + 1) + "选项的文字";
				}
				else {
					data.option.push({
						id: option_id,
						value: text
					});
				}
			});

			//获取其他选项
			var otheroption = setting.find(".option-other");

			if (otheroption.find(".enabled-other").is(":checked")) {
				data.option.push({
					id: otheroption.data("option_id"),
					value: $.trim(otheroption.find(".text").val()),
					other: true
				});
			}
		}

		//地址字段获取详细地址选项
		if (data.type == "address") {
			data.more = setting.find(".more").is(":checked");
		}

		fieldData.push(data);
	});

	return fieldData;
}

//对话框
function qDialog(message, callback) {
    bootbox.dialog({
        message: message,
        closeButton: false,
        buttons: {
            ok: {
                label: "确定",
                callback: callback
            }
        }
    });
}

//保存数据
function saveData(status, check) {
	var data = {
		qu_id: q.qu_id,
		title: $.trim($("#title").val()),
		body: $.trim($("#body").val()),
		qc_id: $("#qc_id").val(),
		share_id: q.share_id,
		release_status: status
	};

	var viewranges = null;

	var deadline_date = $("#deadline_date").val();
	var deadline_time = $("#deadline_time").val();

	if (data.title.length == 0) {
		window.alert("请输入问卷标题");
		return;
	}

	if (deadline_date.length == 0) {
		window.alert("请选择截止日期");
		return;
	}

	if (deadline_time.length == 0) {
		window.alert("请选择截止时间");
		return;
	}

    data.deadline = getUnixTime(deadline_date + " " + deadline_time);

	if (check && data.deadline < getNowTime()) {
		window.alert("截止时间不能小于当前时间");
		return;
	}

	//判断问卷状态为草稿
	if (q.release_status == 2) {
		data.is_all = Number($("#is_all").val());
		data.share = $(".share :checked").val();
		data.anonymous = $(".anonymous :checked").val();
		data.repeat = $(".repeat :checked").val();
		data.remind = $("#remind").val();
		data.release = Number($(".release :checked").val());

		//判断可见范围为特定人员
		if (data.is_all == 0) {
			var count = 0;

			for (var k in chooseData) {
				count += chooseData[k].length;
			}

			if (count > 0) {
				viewranges = {
					departments: chooseData.selectedDepartment,
					persons: chooseData.selectedPersons,
					tags: chooseData.selectedTags
				};
			}
			else if (check) {
				window.alert("请选择特定人员");
				return;
			}
		}

		if (!isSignInt(data.remind)) {
			window.alert("自动提醒时间必须为大于0的整数");
			return;
		}

		if (data.release == 1) {
			var release_date = $("#release_date").val();
			var release_time = $("#release_time").val();

			if (release_date.length == 0) {
				window.alert("请选择定时发布日期");
				return;
			}

			if (release_time.length == 0) {
				window.alert("请选择定时发布时间");
				return;
			}

            data.release = getUnixTime(release_date + " " + release_time);

			if (check && data.release < getNowTime()) {
				window.alert("定时发布时间不能小于当前时间");
				return;
			}

			if (check && data.release > data.deadline) {
				window.alert("定时发布时间不能大于截至时间");
				return;
			}
		}
	}

	try {
		data.field = getFieldData(check);

		if (check && data.field.length == 0) {
			window.alert("请添加表单字段");
			return;
		}
	}
	catch (e) {
		window.alert(e);
		return;
	}

	blockUI();

	$.ajax({
		url: "/Questionnaire/Apicp/Questionnaire/Save",
		type: "post",
		dataType: "json",
		data: {
			q: data,
			viewranges: viewranges
		},
		success: function (e) {
			unblockUI();

			if (e.result.result) {
				q = e.result.q;

                //判断问卷状态为草稿
                if (q.release_status == 2) {
                    qDialog("保存成功");
                }
                else {
                    var message;

                    if (data.share == 1) {
                        message = "问卷网址：<br />{$share_url}" + q.share_id + "<br />（您可复制该网址给他人，也可直接通过微信端打开问卷，点击右上角分享出去）";
                    }
                    else {
                        message = "发布成功";
                    }

                    qDialog(message, function () {
                        window.location.href = "{$list_url}";
                    });
                }
			}
			else {
				window.alert("操作失败，请重试");
			}
		},
		error: function (e) {
			window.alert(e.message);
			unblockUI();
		}
	});
}

</script>

{include file="$tpl_dir_base/footer.tpl"}