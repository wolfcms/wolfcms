<?php

/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2011 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * Please see license.txt for the full license text.
 */

/**
 * Supplies helper functions to create HTML forms.
 * 
 * The Form helper is designed to provide quick and easy to use functions that
 * will echo back HTML 5 form elements.
 * 
 * @author     Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright  2011, Martijn van der Kleijn
 * @license    http://www.wolfcms.org/about/wolf-cms-licensing.html
 */
class Form {

    /**
     * Generates an <input> element.
     * 
     * Depending on browser support, valid types are:
     *      - text
     *      - image
     *      - tel
     *      - email
     *      - url
     *      - file
     *      - password
     *      - number
     *      - range
     *      - date
     *      - month
     *      - week
     *      - time
     *      - datetime
     *      - datetime-local
     *      - search
     *      - color
     *      - hidden
     *
     * @param type $name
     * @param type $id
     * @param type $type
     * @param type $label
     * @param type $placeholder
     * @param type $required
     * @param type $focus
     * @return type 
     */
    public static function input($name, $id=false, $type='text', $label=false, $placeholder=false, $required=false, $focus=false, $autocomplete=false, $options=array()) {
        $id = ($id !== false) ? $id : $name;
        $type = (($type !== false) && ($type !== null)) ? $type : 'text';
        $required = ($required !== false) ? ' required' : '';
        $focus = ($focus !== false) ? ' autofocus' : '';
        $ph = '';
        $lbl = '';
        $opt = '';
        
        switch ($autocomplete) {
            case true:
                $complete = ' autocomplete="on"';
                break;
            case false:
                $complete = ' autocomplete="off"';
                break;
            default:
                $complete = '';
                break;
        }     

        if ($placeholder !== false) {
            $ph = ' placeholder="';
            if ($placeholder === true) {
                switch ($type) {
                    case 'url':
                        $ph = $ph.'http://www.example.com';
                        break;
                    case 'email':
                        $ph = $ph.'user@example.com';
                        break;
                    case 'tel':
                        $ph = $ph.'e.g. +31201234567';
                        break;
                    default:
                        break;
                }
            }
            else {
                $ph = $ph.$placeholder;
            }
            $ph = $ph.'"';
        }
        
        if ($label !== true) {
            $lbl = '<label for="'.$id.'">'.$label.'</label>';
        }
        
        foreach ($options as $name => $value) {
            $opt = $opt.' '.$name.'="'.$value.'"';
        }

        return $lbl.'<input id="'.$id.'" name="'.$name.'" type="'.$type.'"'.$ph.$opt.$required.$focus.$complete.' />';
    }
    
    public static function textarea($name, $id=false, $rows=5, $value=false, $label=false, $placeholder=false, $required=false, $focus=false, $options=array()) {
        $id = ($id !== false) ? $id : $name;
        $type = (($type !== false) && ($type !== null)) ? $type : '5';
        $required = ($required !== false) ? ' required' : '';
        $focus = ($focus !== false) ? ' autofocus' : '';
        $ph = '';
        $opt = '';
        $value = (($value !== false) && ($value !== null)) ? $value : '';
        
        if ($placeholder !== false) {
            $ph = ' placeholder="';
            if ($placeholder !== true) {
                $ph = $ph.$placeholder;
            }
            $ph = $ph.'"';
        }
        
        if ($label !== true) {
            $lbl = '<label for="'.$id.'">'.$label.'</label>';
        }
        
        foreach ($options as $name => $value) {
            $opt = $opt.' '.$name.'="'.$value.'"';
        }
        
        return $lbl.'<textarea id="'.$id.'" name="'.$name.'" rows="'.$rows.'"'.$ph.$opt.$required.$focus.'>'.$value.'</textarea>';
    }
    
    public static function radio($name, $values=array(), $required=false, $focus=false, $options=array()) {
        $ret = '';
        $opt = '';

        foreach ($options as $name => $value) {
            $opt = $opt.' '.$name.'="'.$value.'"';
        }
        
        foreach ($values as $label => $value) {
            $ret = $ret.'<input id="'.$value.'" name="'.$name.'" value="'.$value.'" type="radio"'.$opt.$required.$focus.' />';
            $ret = $ret.'<label for="'.$value.'">'.$label.'</label>';
        }
        
        return $ret;
    }
    
    public static function box($name, $values=array(), $required=false, $focus=false, $options=array()) {
        $ret = '';
        $opt = '';

        foreach ($options as $name => $value) {
            $opt = $opt.' '.$name.'="'.$value.'"';
        }
        
        foreach ($values as $label => $value) {
            $ret = $ret.'<input id="'.$value.'" name="'.$name.'" value="'.$value.'" type="checkbox"'.$opt.$required.$focus.' />';
            $ret = $ret.'<label for="'.$value.'">'.$label.'</label>';
        }
        
        return $ret;
    }
    
    public static function dropdown($name, $values=array(), $multiple=false, $selected=false, $size=1, $required=false, $focus=false, $options=array()) {
        $multiple = ($multiple !== false) ? ' multiple="multiple"' : '';
        $size = ' size="'.$size.'"';
        $opt = '';
        foreach ($options as $name => $value) {
            $opt = $opt.' '.$name.'="'.$value.'"';
        }
        
        $ret = '<select name="'.$name.'"'.$multiple.$size.$opt.$required.$focus.'>';
        
        foreach ($values as $label => $value) {
            $sel = ($selected !== false && $value == $selected) ? ' selected="selected"' : '';
            $ret = $ret.'<option value="'.$value.'"'.$sel.'>'.$label.'</option>';
        }
        
        return $ret.'</select>';
    }
    
    public static function button($name="Submit", $type='submit', $options=array()) {
        $opt = '';
        
        foreach ($options as $name => $value) {
            $opt = $opt.' '.$name.'="'.$value.'"';
        }
        
        return '<button type="'.$type.'"'.$opt.'>'.$name.'</button>';
    }

}