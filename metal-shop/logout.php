<?php
/**
 * logout.php
 * Прекратяване на сесията на потребителя
 */
require_once __DIR__ . '/config.php';

$_SESSION = [];
session_destroy();

redirect('login.php');
