<?php
session_start();
session_destroy();
// Redirect relative to this file's location in /backend/
header('Location: ../frontend/login.html');
exit;