<?php

    if (!$rh->object->record) return GRANTED;

    // бшбнд оюмекх
    $su = &$rh->utility["skin"];

    echo $su->ParseCommunityFilterFlip( $rh->object->record );


?>