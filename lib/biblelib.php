<?php
    function _callback_to_hide_unwanted_tags($element) {
        if ($element->tag=='div' && !empty($element->class) && ($element->class == 'footnotes' || $element->class == 'crossrefs')) {
            $element->outertext = '';
        }

        if ($element->tag=='sup' && !empty($element->class) && ($element->class == 'xref' || $element->class == 'footnote')) {
            $element->outertext = '';
        }

        if ($element->tag=='a' || 
            $element->tag == 'h1' || 
            $element->tag == 'h2' ||
            $element->tag == 'h3' ||
            $element->tag == 'h4' ||
            $element->tag == 'h5') {
            $element->outertext = '';
            return;
        }
    }

    function lookup_bible_verse($versionid, $startbookid, $startchapter, $startverse, $endbookid, $endchapter, $endverse) {
        include_once('simple_html_dom.php');

        $biblebookmap = biblebooks_array();

        $startbook = $biblebookmap[$startbookid];
        $endbook = $biblebookmap[$endbookid];

        $version = get_field('memorization_version', 'name', 'id', $versionid);

        $version = $version === false ? 'ESV' : $version;

        $html = file_get_html("http://www.biblegateway.com/passage/?search={$startbook}%20{$startchapter}:{$startverse}%20-%20{$endbook}%20{$endchapter}:{$endverse}&version={$version}");

        if (empty($html)) {
            return false;
        }

        $html->set_callback('_callback_to_hide_unwanted_tags');

        $verse = $html->find('div[class=result-text-style-normal]', -1);

        if (empty($verse)) {
            return false;
        }

        return $verse->innertext;
    }

    function bible_gateway_available_versions() {
        include_once('simple_html_dom.php');

        $html = file_get_html("http://www.biblegateway.com");

        if (empty($html)) {
            return false;
        }

        $select = $html->find('select[name=qs_version]', -1);

        if (empty($select)) {
            return false;
        }


        $versionnames = array();

        $currentlang = '';

        $options = $select->children;

        foreach ($options as $option) {
            if ($option->class == 'lang') {
                $currentlang = $option->value;
                continue;
            }

            // for some reason the english language section is deliniated by NIV not EN
            if ($currentlang != 'NIV') {
                continue;
            }

            $versionname = str_replace('&nbsp;', '', $option->innertext);

            $versionnames[$option->value] = $versionname; 
        }

        return $versionnames;
    }

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