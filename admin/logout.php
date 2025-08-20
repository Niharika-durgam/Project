<?php
session_start();
session_destroy();
header("Location: /FreelancerPlatform/admin/index.php");
exit();