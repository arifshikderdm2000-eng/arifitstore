<?php
/**
 * Admin Logout
 * BongoStore BD
 */
require_once __DIR__ . '/../includes/functions.php';

unset($_SESSION['admin_user']);
set_flash('info', 'আপনি সফলভাবে অ্যাডমিন প্যানেল থেকে লগআউট করেছেন।');
header("Location: /admin/login.php");
exit;
