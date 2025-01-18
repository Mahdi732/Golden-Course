<?php
require_once('database.php');
require_once('user.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Teacher extends User {
    
}