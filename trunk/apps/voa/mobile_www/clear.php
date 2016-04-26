<?php

foreach ($_COOKIE as $_n => $_v) {
	setcookie($_n, '', -1);
}
