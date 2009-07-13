<?php

/**
 * Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 * Copyright (C) 2008 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Frog CMS.
 *
 * Frog CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Frog CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Frog CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Frog CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/**
 * @package frog
 * @subpackage controllers
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @version 0.1
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, 2008
 */

/**
 * Class SettingsController
 *
 * @package frog
 * @subpackage controllers
 *
 * @since 0.8.7
 */
class SettingController extends Controller
{
    public function __construct()
    {
        AuthUser::load();
        if ( ! AuthUser::isLoggedIn())
        {
            redirect(get_url('login'));
        }
        else if ( ! AuthUser::hasPermission('administrator'))
        {
            Flash::set('error', __('You do not have permission to access the requested page!'));

            if (Setting::get('default_tab') === 'setting')
                redirect(get_url('page'));
            else
                redirect(get_url());
        }
        
        $this->setLayout('backend');
    }
    
    public function index()
    {
        // check if trying to save
        if (get_request_method() == 'POST')
            return $this->_save();
        
        $this->display('setting/index');
    }
    
    private function _save()
    {
        $data = $_POST['setting'];
        
        if (!isset($data['allow_html_title']))
            $data['allow_html_title'] = 'off';
        
        Setting::saveFromData($data);
        
        Flash::set('success', __('Settings has been saved!'));
        
        redirect(get_url('setting'));
    }
    
    public function activate_plugin($plugin)
    {
        if ( ! AuthUser::hasPermission('administrator'))
        {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url());
        }
        
        Plugin::activate($plugin);
        Observer::notify('plugin_after_enable', $plugin);
    }
    
    public function deactivate_plugin($plugin)
    {
        if ( ! AuthUser::hasPermission('administrator'))
        {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url());
        }
        
        Plugin::deactivate($plugin);
        Observer::notify('plugin_after_disable', $plugin);
    }

} // end SettingController class

$GLOBALS['iso_639_1'] = array(
'aa' => 'Afar',
'ab' => 'Abkhazian',
'ae' => 'Avestan',
'af' => 'Afrikaans',
'ak' => 'Akan',
'am' => 'Amharic',
'an' => 'Aragonese',
'ar' => 'Arabic',
'as' => 'Assamese',
'av' => 'Avaric',
'ay' => 'Aymara',
'az' => 'Azerbaijani',
'ba' => 'Bashkir',
'be' => 'Belarusian',
'bg' => 'Bulgarian',
'bh' => 'Bihari',
'bi' => 'Bislama',
'bm' => 'Bambara',
'bn' => 'Bengali',
'bo' => 'Tibetan',
'br' => 'Breton',
'bs' => 'Bosnian',
'ca' => 'Catalan',
'ce' => 'Chechen',
'ch' => 'Chamorro',
'co' => 'Corsican',
'cr' => 'Cree',
'cs' => 'Czech',
'cu' => 'Church Slavic',
'cv' => 'Chuvash',
'cy' => 'Welsh',
'da' => 'Danish',
'de' => 'German',
'dv' => 'Dhivehi',
'dz' => 'Dzongkha',
'ee' => 'Ewe',
'el' => 'Greek',
'en' => 'English',
'eo' => 'Esperanto',
'es' => 'Spanish',
'et' => 'Estonian',
'eu' => 'Basque',
'fa' => 'Persian',
'ff' => 'Fulah',
'fi' => 'Finnish',
'fj' => 'Fijian',
'fo' => 'Faroese',
'fr' => 'French',
'fy' => 'Western Frisian',
'ga' => 'Irish',
'gd' => 'Gaelic',
'gl' => 'Galician',
'gn' => 'Guarani',
'gu' => 'Gujarati',
'gv' => 'Manx',
'ha' => 'Hausa',
'he' => 'Hebrew',
'hi' => 'Hindi',
'ho' => 'Hiri Motu',
'hr' => 'Croatian',
'ht' => 'Haitian',
'hu' => 'Hungarian',
'hy' => 'Armenian',
'hz' => 'Herero',
'ia' => 'Interlingua',
'id' => 'Indonesian',
'ie' => 'Interlingue',
'ig' => 'Igbo',
'ii' => 'Sichuan Yi',
'ik' => 'Inupiak',
'io' => 'Ido',
'is' => 'Icelandic',
'it' => 'Italian',
'iu' => 'Inuktitut',
'ja' => 'Japanese',
'jv' => 'Javanese',
'ka' => 'Georgian',
'kg' => 'Kongo',
'ki' => 'Kikuyu',
'kj' => 'Kuanyama',
'kk' => 'Kazakh',
'kl' => 'Greenlandic',
'km' => 'Cambodian',
'kn' => 'Kannada',
'ko' => 'Korean',
'kr' => 'Kanuri',
'ks' => 'Kashmiri',
'ku' => 'Kurdish',
'kv' => 'Komi',
'kw' => 'Cornish',
'ky' => 'Kirghiz',
'la' => 'Latin',
'lb' => 'Luxembourgish',
'lg' => 'Ganda',
'li' => 'Limburgan',
'ln' => 'Lingala',
'lo' => 'Laothian',
'lt' => 'Lithuanian',
'lu' => 'Luba-Katanga',
'lv' => 'Latvian',
'mg' => 'Malagasy',
'mh' => 'Marshallese',
'mi' => 'Maori',
'mk' => 'Macedonian',
'ml' => 'Malayalam',
'mn' => 'Mongolian',
'mo' => 'Moldavian',
'mr' => 'Marathi',
'ms' => 'Malay',
'mt' => 'Maltese',
'my' => 'Burmese',
'na' => 'Nauru',
'nb' => 'Norwegian Bokmal',
'nd' => 'North Ndebele',
'ne' => 'Nepali',
'ng' => 'Ndonga',
'nl' => 'Dutch',
'nn' => 'Norwegian Nynorsk',
'no' => 'Norwegian',
'nr' => 'South Ndebele',
'nv' => 'Navajo',
'ny' => 'Nyanja',
'oc' => 'Occitan',
'oj' => 'Ojibwa',
'om' => 'Oromo',
'or' => 'Oriya',
'os' => 'Ossetian',
'pa' => 'Punjabi',
'pi' => 'Pali',
'pl' => 'Polish',
'ps' => 'Pushto',
'pt' => 'Portuguese',
'qu' => 'Quechua',
'rm' => 'Romansh',
'rn' => 'Rundi',
'ro' => 'Romanian',
'ru' => 'Russian',
'rw' => 'Kinyarwanda',
'sa' => 'Sanskrit',
'sc' => 'Sardinian',
'sd' => 'Sindhi',
'se' => 'Northern Sami',
'sg' => 'Sangro',
'sh' => 'Serbo-Croatian',
'si' => 'Sinhala',
'sk' => 'Slovak',
'sl' => 'Slovenian',
'sm' => 'Samoan',
'sn' => 'Shona',
'so' => 'Somali',
'sq' => 'Albanian',
'sr' => 'Serbian',
'ss' => 'Siswati',
'st' => 'Sesotho',
'su' => 'Sudanese',
'sv' => 'Swedish',
'sw' => 'Swahili',
'ta' => 'Tamil',
'te' => 'Tegulu',
'tg' => 'Tajik',
'th' => 'Thai',
'ti' => 'Tigrinya',
'tk' => 'Turkmen',
'tl' => 'Tagalog',
'tn' => 'Tswana',
'to' => 'Tonga',
'tr' => 'Turkish',
'ts' => 'Tsonga',
'tt' => 'Tatar',
'tw' => 'Twi',
'ty' => 'Tahitian',
'ug' => 'Uighur',
'uk' => 'Ukrainian',
'ur' => 'Urdu',
'uz' => 'Uzbek',
've' => 'Venda',
'vi' => 'Vietnamese',
'vo' => 'Volapuk',
'wa' => 'Walloon',
'wo' => 'Wolof',
'xh' => 'Xhosa',
'yi' => 'Yiddish',
'yo' => 'Yoruba',
'za' => 'Zhuang',
'zh' => 'Chinese',
'zu' => 'Zulu');
