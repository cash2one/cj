<?php
/**
 *  controller_route_exception
 *
 * $Author$
 * $Id$
 */

class controller_route_exception extends controller_exception {

	public function __construct($message, $code = 0) {
		if ($message instanceof Exception) {
			parent::__construct($message->getMessage(), intval($message->getCode()));
		} else {
			parent::__construct($message, intval($code));
		}
	}

}
