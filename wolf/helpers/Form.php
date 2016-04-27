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
     *      text, image, tel, email, url, file, password, number, range, date
     *      month, week, time, datetime, datetime-local, search, color, hidden
     * 
     * Some features are not supported yet by all browsers, most notably IE.
     *
     * @param string    $name           Name attribute for <input> field.
     * @param string    $id             Id attribute for <input> field.
     * @param string    $type           Type of <input> field.
     * @param boolean   $label          Add a label?
     * @param string    $placeholder    Placeholder text.
     * @param boolean   $required       Required field?
     * @param boolean   $focus          Autofocus on field?
     * @param boolean   $autocomplete   Autocomplete attribute.
     * @param array     $options        Array of name=>value pairs to add as element attributes.
     * @return string                   HTML 5 compliant <input> field.
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

        foreach ($options as $oname => $ovalue) {
            $opt = $opt.' '.$oname.'="'.$ovalue.'"';
        }

        return $lbl.'<input id="'.$id.'" name="'.$name.'" type="'.$type.'"'.$ph.$opt.$required.$focus.$complete.' />';
    }


    /**
     * Generates a <textarea> element.
     * 
     * Minimal usage example:
     * 
     * use_helper('Form');
     * echo Form::textarea('comment');
     * 
     * This will generate:
     * 
     * <textarea id="comments" name="comments" rows="5"></textarea>
     *
     * @param string    $name
     * @param string    $id
     * @param int       $rows
     * @param string    $value
     * @param string    $label
     * @param string    $placeholder
     * @param boolean   $required
     * @param boolean   $focus
     * @param array     $options
     * @return string 
     */
    public static function textarea($name, $id=false, $rows=5, $value=false, $label=false, $placeholder=false, $required=false, $focus=false, $options=array()) {
        $id = ($id !== false) ? $id : $name;
        $type = (($type !== false) && ($type !== null)) ? $type : '5';
        $required = ($required !== false) ? ' required' : '';
        $focus = ($focus !== false) ? ' autofocus' : '';
        $ph = '';
        $opt = '';
        $lbl = '';
        $value = (($value !== false) && ($value !== null)) ? $value : '';

        if ($placeholder !== false) {
            $ph = ' placeholder="';
            if ($placeholder !== true) {
                $ph = $ph.$placeholder;
            }
            $ph = $ph.'"';
        }

        if ($label === true) {
            $lbl = '<label for="'.$id.'">'.$label.'</label>';
        }

        foreach ($options as $oname => $ovalue) {
            $opt = $opt.' '.$oname.'="'.$ovalue.'"';
        }

        return $lbl.'<textarea id="'.$id.'" name="'.$name.'" rows="'.$rows.'"'.$ph.$opt.$required.$focus.'>'.$value.'</textarea>';
    }


    /**
     * Generates an <input> element for radiobuttons.
     * 
     * Minimal usage example:
     * 
     *      use_helper('Form');
     *      $values = array('nl' => 'Netherlands, The', 'uk' => 'United Kingdom',
     *                      'us' => 'United States');
     *      echo Form::radio('country', $values);
     *
     * @param string    $name
     * @param array     $values
     * @param boolean   $required
     * @param boolean   $focus
     * @param array     $options
     * @return string 
     */
    public static function radio($name, $values=array(), $required=false, $focus=false, $options=array()) {
        $ret = '';
        $opt = '';

        foreach ($options as $oname => $ovalue) {
            $opt = $opt.' '.$oname.'="'.$ovalue.'"';
        }

        foreach ($values as $label => $value) {
            $ret = $ret.'<input id="'.$value.'" name="'.$name.'" value="'.$value.'" type="radio"'.$opt.$required.$focus.' />';
            $ret = $ret.'<label for="'.$value.'">'.$label.'</label>';
        }

        return $ret;
    }


    /**
     * Generates an <input> element for checkboxes.
     * 
     * Minimal usage example:
     * 
     *      use_helper('Form');
     *      $values = array('nl' => 'Netherlands, The', 'uk' => 'United Kingdom',
     *                      'us' => 'United States');
     *      echo Form::box('country', $values);
     *
     * @param string    $name
     * @param array     $values
     * @param boolean   $required
     * @param boolean   $focus
     * @param array     $options
     * @return string 
     */
    public static function box($name, $values=array(), $required=false, $focus=false, $options=array()) {
        $ret = '';
        $opt = '';

        foreach ($options as $oname => $ovalue) {
            $opt = $opt.' '.$oname.'="'.$ovalue.'"';
        }

        foreach ($values as $label => $value) {
            $ret = $ret.'<input id="'.$value.'" name="'.$name.'" value="'.$value.'" type="checkbox"'.$opt.$required.$focus.' />';
            $ret = $ret.'<label for="'.$value.'">'.$label.'</label>';
        }

        return $ret;
    }


    /**
     * Generates a <select> element. (dropdown box)
     * 
     * Minimal usage example:
     * 
     *      use_helper('Form');
     *      $values = array('nl' => 'Netherlands, The', 'uk' => 'United Kingdom',
     *                      'us' => 'United States');
     *      echo Form::dropdown('country', $values);
     *
     * @param string    $name
     * @param array     $values
     * @param boolean   $multiple
     * @param string    $selected
     * @param int       $size
     * @param boolean   $required
     * @param boolean   $focus
     * @param array     $options
     * @return string 
     */
    public static function dropdown($name, $values=array(), $multiple=false, $selected=false, $size=1, $required=false, $focus=false, $options=array()) {
        $multiple = ($multiple !== false) ? ' multiple="multiple"' : '';
        $size = ' size="'.$size.'"';
        $opt = '';
        foreach ($options as $oname => $ovalue) {
            $opt = $opt.' '.$oname.'="'.$ovalue.'"';
        }

        $ret = '<select name="'.$name.'"'.$multiple.$size.$opt.$required.$focus.'>';

        foreach ($values as $label => $value) {
            $sel = ($selected !== false && $value == $selected) ? ' selected="selected"' : '';
            $ret = $ret.'<option value="'.$value.'"'.$sel.'>'.$label.'</option>';
        }

        return $ret.'</select>';
    }


    /**
     * Generates a <button> element.
     *
     * @param string    $name
     * @param string    $type
     * @param array     $options
     * @return string 
     */
    public static function button($name="Submit", $type='submit', $options=array()) {
        $opt = '';

        foreach ($options as $oname => $ovalue) {
            $opt = $opt.' '.$oname.'="'.$ovalue.'"';
        }

        return '<button type="'.$type.'"'.$opt.'>'.$name.'</button>';
    }

}