<?php 
    function biblebooks_array($testament=null){
        $oldtestament = array(  0 => 'Other',
                                1 => 'Genesis', 
                                2 => 'Exodus', 
                                3 => 'Leviticus', 
                                4 => 'Numbers', 
                                5 => 'Deuteronomy', 
                                6 => 'Joshua', 
                                7 => 'Judges', 
                                8 => 'Ruth', 
                                9 => 'I Samuel', 
                                10 => 'II Samuel', 
                                11 => 'I Kings',
                                12 => 'II Kings',
                                13 => 'I Chronicles',
                                14 => 'II Chronicles',
                                15 => 'Ezra',
                                16 => 'Nehemiah',
                                17 => 'Esther',
                                18 => 'Job',
                                19 => 'Psalms',
                                20 => 'Proverbs',
                                21 => 'Ecclesiastes',
                                22 => 'The Song of Songs',
                                23 => 'Isaiah',
                                24 => 'Jeremiah',
                                25 => 'Lamentations',
                                26 => 'Ezekiel',
                                27 => 'Daniel',
                                28 => 'Hosea',
                                29 => 'Joel',
                                30 => 'Amos',
                                31 => 'Obadiah',
                                32 => 'Jonah',
                                33 => 'Micah',
                                34 => 'Nahum',
                                35 => 'Habakkuk',
                                36 => 'Zephaniah',
                                37 => 'Haggai',
                                38 => 'Zechariah',
                                39 => 'Malachi');
        
        $newtestament = array(
                                40 => 'Matthew',
                                41 => 'Mark',
                                42 => 'Luke',
                                43 => 'John',
                                44 => 'Acts',
                                45 => 'Romans',
                                46 => 'I Corinthians',
                                47 => 'II Corinthians',
                                48 => 'Galatians',
                                49 => 'Ephesians',
                                50 => 'Philippians',
                                51 => 'Colossians',
                                52 => 'I Thessalonians',
                                53 => 'II Thessalonians',
                                54 => 'I Timothy',
                                55 => 'II Timothy',
                                56 => 'Titus',
                                57 => 'Philemon',
                                58 => 'Hebrews',
                                59 => 'James',
                                60 => 'I Peter',
                                61 => 'II Peter',
                                62 => 'I John',
                                63 => 'II John',
                                64 => 'III John',
                                65 => 'Jude',
                                66 => 'Revelation');

        if ($testament == null) {
            return array_merge($oldtestament, $newtestament);
        } elseif (strtolower($testament) == 'new') {
            return $newtestament;
        } elseif (strtolower($testament) == 'old') {
            return $oldtestament;
        }
    }
?>