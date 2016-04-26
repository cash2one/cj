<?php
/**
 * 培训
 * voa_errcode_oa_jobtrain
 * OA travel 相关 呼叫错误码
 * 使用7位编码
 *
 * $Author$
 * $Id$
 */

class voa_errcode_oa_jobtrain {
	const CATEGORY_TITLE_ERROR = '3906001:分类标题错误';
	const CATEGORY_TITLE_EXIST = '3906002:存在相同的分类标题';
	const CATEGORY_NOT_EXIST = '3906003:分类不存在';
	const CATEGORY_HAVE_SUB = '3906004:有子分类不能删除';
	const ARTICLE_TITLE_ERROR = '3906005:内容标题错误';
	const ARTICLE_CID_ERROR = '3906006:内容分类id错误';
	const ARTICLE_CONTENT_ERROR = '3906007:内容必填';
	const ARTICLE_DEL_FAILED = '3906008:内容删除失败';
	const CATEGORY_HAVE_ARTICLE = '3906009:分类下有内容不能删除';
}
