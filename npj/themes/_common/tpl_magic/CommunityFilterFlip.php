<?php

    if (!$rh->object->record) return GRANTED;

    // ����� ������
    $su = &$rh->utility["skin"];

    echo $su->ParseCommunityFilterFlip( $rh->object->record );


?>