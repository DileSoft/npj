<?php
//!!! ����� �������� ��� ����������� :-)

$text = htmlspecialchars (trim ($what));
$text = preg_replace ('/ {2, }/', ' ', $text);


$trans = array (
        "\r" => '', 
        "\n" => '<br />',
        '|' => '&#124;'
         );

echo str_replace ('<br /><br />', '</p><p>', strtr ($text, $trans));

?>