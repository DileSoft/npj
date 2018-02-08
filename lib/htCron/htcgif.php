<?php

// Call htCron from other site or non-PHP pages

header("Content-Type: image/gif");
header("Content-Disposition: inline;filename=z.gif");
echo base64_decode("R0lGODlhAQABAID/AMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOx"); 
flush();
include("htcron.php");
$db = array(
"host"=>"localhost",
"user"=>"npj",
"password"=>"***",
"database"=>"npj",
);
htcCycle($db, "r0_npz");

?>