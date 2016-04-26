<?php
/**
 * view的默认配置
 *
 * 此配置可以被具体的app配置所覆盖
 *
 * 调用方式:
 * config::get('global.view.key');
 *
 * $Author$
 * $Id$
 */

$conf['templates'] = APP_PATH.'/src/templates';
$conf['templates_c'] = dirname(ROOT_PATH).'/tmp/templates_c';
