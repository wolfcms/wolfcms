<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Models
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2010
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 */

/**
 * Generic Node model.
 *
 * First version for future feature set.
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @since Wolf version 0.7.0
 */
class Node extends Record {


    /**
     * Intended to eventually replace like-wise names JS function from wolf.js
     * 
     * Note: this function might undergo a name change in future...
     *
     * @param type $string
     * @return type 
     */
    public static function toSlug($string) {
        $replacements = array();
        
        $replacements['áàâą'] = 'a';
        $replacements['б'] = 'b';
        $replacements['ćčц'] = 'c';
        $replacements['дđď'] = 'd';
        $replacements['éèêëęě'] = 'e';
        $replacements['ф'] = 'f';
        $replacements['гѓ'] = 'g';
        $replacements['íîïи'] = 'i';
        $replacements['й'] = 'j';
        $replacements['к'] = 'k';
        $replacements['łл'] = 'l';
        $replacements['м'] = 'm';
        $replacements['ñńň'] = 'n';
        $replacements['óôó'] = 'o';
        $replacements['п'] = 'p';
        $replacements['úùûů'] = 'u';
        $replacements['ř'] = 'r';
        $replacements['šś'] = 's';
        $replacements['ťт'] = 't';
        $replacements['в'] = 'v';
        $replacements['ýы'] = 'y';
        $replacements['žżźз'] = 'z';
        $replacements['äæ'] = 'ae';
        $replacements['ч'] = 'ch';
        $replacements['öø'] = 'oe';
        $replacements['ü'] = 'ue';
        $replacements['ш'] = 'sh';
        $replacements['щ'] = 'shh';
        $replacements['ß'] = 'ss';
        $replacements['å'] = 'aa';
        $replacements['я'] = 'ya';
        $replacements['ю'] = 'yu';
        $replacements['ж'] = 'zh';
        $replacements['[^-a-z0-9~\s\.:;+=_]'] = '';
        $replacements['[\s\.:;=+]+'] = '-';

        // Test for non western characters
        // Need to do this in a better way
        $test = preg_match('([a-z]|[A-Z]|[0-9]|[áàâąбćčцдđďéèêëęěфгѓíîïийкłлмñńňóôóпúùûůřšśťтвýыžżźзäæчöøüшщßåяюж])', $string);
        if ($test === 0 || false === $test) {
            return $string;
        }
        
        $string = strtolower(trim($string));
        
        foreach ($replacements as $key => $value) {
            $string = preg_replace('/'.$key.'/', $value, $string);
        }

        return preg_replace('/[-]+/', '-', $string);
    }
}
