<?php 
    function biblebooks_array($testament=null){
        $oldtestament = array('Genesis', 'Exodus', 'Leviticus', 'Numbers', 'Deuteronomy', 'Joshua', 'Judges', 'Ruth', 'I Samuel', 'II Samuel', 'I Kings', 'II Kings', 'I Chronicles', 'II Chronicles', 'Ezra', 'Nehemiah', 'Esther', 'Job', 'Psalms', 'Proverbs', 'Ecclesiastes', 'The Song of Songs', 'Isaiah', 'Jeremiah', 'Lamentations', 'Ezekiel', 'Daniel', 'Hosea', 'Joel', 'Amos', 'Obadiah', 'Jonah', 'Micah', 'Nahum', 'Habakkuk', 'Zephaniah', 'Haggai', 'Zechariah', 'Malachi');
        $newtestament = array('Matthew', 'Mark', 'Luke', 'John', 'Acts', 'Romans', 'Corinthians I', 'Corinthians II', 'Galatians', 'Ephesians', 'Philippians', 'Colossians', 'I Thessalonians', 'II Thessalonians', 'I Timothy', 'II Timothy', 'Titus', 'Philemon', 'Hebrews', 'James', 'I Peter', 'II Peter', 'I John', 'II John', 'III John', 'Jude', 'Revelation');

        if ($testament == null) {
            return array_merge($oldtestament, $newtestament);
        } elseif (strtolower($testament) == 'new') {
            return $newtestament;
        } elseif (strtolower($testament) == 'old') {
            return $oldtestament;
        }
    }
?>