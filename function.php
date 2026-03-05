<?php
// Pfüfung auf verbotene Begriffe
function enthaeltVerboteneWoerter($text)
{
    //1. Liste an verbotenen Wörtern
    $blacklist = ['waffe', 'droge', 'porno', 'hausaufgabe'];

    //2. Den regEx dynamisch aufbauen:
    // /(waffe|droge|porno|hausaufgabe)/iu
    // i steht für "case-insensitive" egal, ob es groß oder klein geschrieben wurde
    // u steht für Umlaute und Sonderzeichen (utf-8)
    $pattern = '/(' . implode('|', $blacklist) . ')/iu';

    //3. Pfüfung: Gibt es einen Treffer?
    if (preg_match($pattern, $text)) {
        //Treffer - Text enthält ein verbotenes Wort
        return true;
    }
    return false;
}
