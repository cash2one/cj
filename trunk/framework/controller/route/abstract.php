<?php
/**
 * controller_route_abstract
 *
 * $Author$
 * $Id$
 */

abstract class controller_route_abstract {

	/**
	 * _url_variable
	 * 变量标示符
	 *
	 * @var string
	 */
	protected $_url_variable = ':';

	/**
	 * _url_delimiter
	 * url分隔符
	 *
	 * @var string
	 */
	protected $_url_delimiter = '/';

	/**
	 * _module_key
	 *
	 * @var string
	 */
	protected $_module_key = 'module';

	/**
	 * _controller_key
	 *
	 * @var string
	 */
	protected $_controller_key = 'controller';

	/**
	 * _action_key
	 *
	 * @var string
	 */
	protected $_action_key = 'action';

	abstract function match($path);

}
