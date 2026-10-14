<?php
require_once 'config/config.php';
if(isLoggedIn()) logHistory(uid(),'LOGOUT','User logged out');
clearSession();
header('Location:'.BASE_URL.'/index.php');
exit;
