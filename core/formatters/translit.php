<?php

    // �����������������������������
        $NpjMacros = array( "����" => "wiki", "����" => "wacko", "�����" => "shwacko",
                            "���" => "web", "����" => "lance", "�����" => "kukutz", "���������" => "mendokusee",
                            "������" => "iaremko", "�������" => "nikolai", "�������" => "aleksey", 
                            "��������" => "anatoly", "���" => "npj", 
                          );
        $NpjLettersFrom = "���������������������";
        $NpjLettersTo   = "abvgdeziklmnoprstufcy";
        $NpjConsonant = "���������������������";
        $NpjVowel = "���������";
        $NpjBiLetters = array( 
      "�" => "jj", "�" => "jo", "�" => "zh", "�" => "kh", "�" => "ch", 
      "�" => "sh", "�" => "shh", "�" => "je", "�" => "ju", "�" => "ja",
      "�" => "", "�" => "",
                              );

        $NpjCaps  = "�����Ũ��������������������������";
        $NpjSmall = "��������������������������������";


      $tag = $text;
      //insert _ between words
      $tag = preg_replace( "/\s+/ms", "_", $tag );
      $tag = str_replace( "::", "_", $tag );
      $tag = str_replace( "@", "_", $tag );
      $tag = preg_replace( "/\_+/ms", "_", $tag );

      $tag = strtolower( $tag );
      $tag = strtr( $tag, $NpjCaps, $NpjSmall );
      $tag = strtr( $tag, $NpjMacros );
      $tag = strtr( $tag, $NpjLettersFrom, $NpjLettersTo );
      $tag = strtr( $tag, $NpjBiLetters );

      $tag = preg_replace("/[^a-z0-9\-_]+/mi", "", $tag);

      echo $tag;

?>