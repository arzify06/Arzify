<?php
/**
 * Desire Travel - Secure Sign Out Handler
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/lang.php';
require_once __DIR__ . '/helpers/auth.php';

logoutUser();

$_SESSION['flash_success'] = __('logged_out_success');
header('Location: ' . BASE_URL . 'index.php');
exit;
