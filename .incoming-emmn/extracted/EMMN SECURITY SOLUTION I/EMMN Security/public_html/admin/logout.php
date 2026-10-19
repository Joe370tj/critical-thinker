<?php
require_once dirname(__DIR__) . '/includes/config.php';
logoutUser();
redirect('admin/login.php');
