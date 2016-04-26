<?php
/**
 * db_exception
 *
 * $Author$
 * $Id$
 */

class db_exception extends Exception  {

	public function __construct($message, $code = 0) {
		if ($message instanceof Exception) {
			parent::__construct($message->getMessage(), intval($message->getCode()));
		} else {
			parent::__construct($message, intval($code));
		}
	}

}
