<?php

    // Ќпж¬заимноќднозначный“ранслит
        $NpjMacros = array( "вики" => "wiki", "вака" => "wacko", "швака" => "shwacko",
                            "веб" => "web", "ланс" => "lance", "кукуц" => "kukutz", "мендокуси" => "mendokusee",
                            "€ремко" => "iaremko", "николай" => "nikolai", "алексей" => "aleksey", 
                            "анатолий" => "anatoly", "нпж" => "npj", 
                          );
        $NpjLettersFrom = "абвгдезиклмнопрстуфцы";
        $NpjLettersTo   = "abvgdeziklmnoprstufcy";
        $NpjConsonant = "бвгджзйклмнпрстфхцчшщ";
        $NpjVowel = "аеЄиоуыэю€";
        $NpjBiLetters = array( 
      "й" => "jj", "Є" => "jo", "ж" => "zh", "х" => "kh", "ч" => "ch", 
      "ш" => "sh", "щ" => "shh", "э" => "je", "ю" => "ju", "€" => "ja",
      "ъ" => "", "ь" => "",
                              );

        $NpjCaps  = "јЅ¬√ƒ≈®∆«»… ЋћЌќѕ–—“”‘’÷„Ўў№ЏџЁёя";
        $NpjSmall = "абвгдеЄжзийклмнопрстуфхцчшщьъыэю€";


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