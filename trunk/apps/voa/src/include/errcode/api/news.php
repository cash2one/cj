<?php
/**
 * voa_errcode_api_news
 * API 接口通信录错误码定义
 * 约定错误码均以1开头的5位数字
 * 每个错误码以常量定义，格式为“errcode:errmsg”
 * errcode为唯一的整型
 * errmsg为错误信息，可使用%s做为变量
 * 一旦提供文档，错误码errcode不允许变更！！
 * $Author$
 * $Id$
 */
class voa_errcode_api_news {

	const SUBJECT_NULL = '28000:标题不能为空';
	const MESSAGE_NULL = '28001:正文内容不能为空';
	const SUBJECT_BEYOND = '28004:标题最多64字';
	const SUMMARY_BEYOND = '28005:摘要最多120字';
	const MESSIMG_NULL = '28006:图片不能为空';
	const MESSCATE_NULL = '28007:类型不能为空';

}
