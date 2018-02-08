<?php

    $dt = strtotime($text);

    echo $this->message_set["Months"][ date("n",$dt)-1 ]."&nbsp;".date("Y",$dt);

?>