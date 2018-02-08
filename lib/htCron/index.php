test 4 htcron
<?php
echo time();

/**/
ob_end_flush();
flush();
include("htcron.php");
$db = array(
"host"=>"localhost",
"user"=>"npj",
"password"=>"*****",
"database"=>"npj",
);
NPZ_Cycle($db, "r0_npz");
?>