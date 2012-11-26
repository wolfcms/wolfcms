<?php

/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Controllers
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2009-2010
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 */

/**
 * Controls the various actions with regards to the settings screen for Wolf CMS.
 */
class SettingController extends Controller {


    /**
     * Used to check generic permissions for entire the controller.
     */
    private static final function _checkPermission() {
        AuthUser::load();
        if (!AuthUser::isLoggedIn()) {
            redirect(get_url('login'));
        }
        else {
            if (!AuthUser::hasPermission('admin_edit')) {
                Flash::set('error', __('You do not have permission to access the requested page!'));

                if (Setting::get('default_tab') === 'setting') {
                    redirect(get_url('page'));
                }
                else {
                    redirect(get_url());
                }
            }
        }
    }


    public final function __construct() {
        SettingController::_checkPermission();
        $this->setLayout('backend');
    }


    /**
     * Calls save function or displays settings screen.
     */
    public final function index() {
        // check if trying to save
        if (get_request_method() == 'POST') {
            $this->_save();
        }

        $this->display('setting/index',
                array('csrf_token' => SecureToken::generateToken(BASE_URL.'setting'))
        );
    }


    /**
     * Saves the settings.
     */
    private final function _save() {
        $data = $_POST['setting'];

        // CSRF checks
        if (isset($_POST['csrf_token'])) {
            $csrf_token = $_POST['csrf_token'];
            if (!SecureToken::validateToken($csrf_token, BASE_URL.'setting')) {
                Flash::set('error', __('Invalid CSRF token found!'));
                Observer::notify('csrf_token_invalid', AuthUser::getUserName());
                redirect(get_url('setting'));
            }
        }
        else {
            Flash::set('error', __('No CSRF token found!'));
            Observer::notify('csrf_token_not_found', AuthUser::getUserName());
            redirect(get_url('setting'));
        }

        if (!isset($data['allow_html_title'])) {
            $data['allow_html_title'] = 'off';
        }

        use_helper('Kses');
        $allowed = array(
            'img' => array(
                'src' => array()
            ),
            'abbr' => array(
                'title' => array()
            ),
            'acronym' => array(
                'title' => array()
            ),
            'b' => array(),
            'blockquote' => array(
                'cite' => array()
            ),
            'br' => array(),
            'code' => array(),
            'em' => array(),
            'i' => array(),
            'p' => array(),
            'strike' => array(),
            'strong' => array()
        );
        $data['admin_title'] = kses(trim($data['admin_title']), $allowed);

        Setting::saveFromData($data);
        Flash::set('success', __('Settings have been saved!'));
        redirect(get_url('setting'));
    }


    /**
     * Can be used to activate a plugin.
     *
     * @param string $plugin The plugin id.
     */
    public final function activate_plugin($plugin) {
        Plugin::activate($plugin);
        Observer::notify('plugin_after_enable', $plugin);
    }


    /**
     * Can be used to deactivate a plugin.
     *
     * @param string $plugin The plugin id.
     */
    public final function deactivate_plugin($plugin) {
        Plugin::deactivate($plugin);
        Observer::notify('plugin_after_disable', $plugin);
    }


    /**
     * Can be used to uninstall a plugin.
     *
     * @param string $plugin The plugin id.
     */
    public final function uninstall_plugin($plugin) {
        Plugin::uninstall($plugin);
        Observer::notify('plugin_after_uninstall', $plugin);
    }

    /**
     * The $ietf array is an array that contains entries describing languages
     * useable by Wolf CMS based on rfc4646, specifically the following
     * combinations are acceptable:
     * 
     * Language (en)
     * Language-Region (en-UK)
     * 
     * Important note - This list is currently incomplete. Add as needed.
     * 
     * For more information, see:
     *     http://www.ietf.org/rfc/rfc4646.txt
     *     http://www.iana.org/assignments/language-subtag-registry
     */
    public static $ietf = array(
        'aa' => 'Afar',
        'ab' => 'Abkhazian',
        'ae' => 'Avestan',
        'af' => 'Afrikaans',
        'ak' => 'Akan',
        'am' => 'Amharic',
        'an' => 'Aragonese',
        'ar' => 'Arabic',
        'ar-DZ' => 'Arabic/Algeria',
        'ar-BH' => 'Arabic/Bahrain',
        'ar-EG' => 'Arabic/Egypt',
        'ar-IQ' => 'Arabic/Iraq',
        'ar-JO' => 'Arabic/Jordan',
        'ar-KW' => 'Arabic/Kuwait',
        'ar-LB' => 'Arabic/Lebanon',
        'ar-LI' => 'Arabic/Libya',
        'ar-MA' => 'Arabic/Marocco',
        'ar-OM' => 'Arabic/Oman',
        'ar-QA' => 'Arabic/Qatar',
        'ar-SA' => 'Arabic/Saudi Arabia',
        'ar-SY' => 'Arabic/Syria',
        'ar-TN' => 'Arabic/Tunesia',
        'ar-AE' => 'Arabic/UAE',
        'ar-YE' => 'Arabic/Yemen',
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
        'de-AT' => 'German/Austria',
        'de-DE' => 'German/Germany',
        'de-LI' => 'German/Liechtenstein',
        'de-LU' => 'German/Luxembourg',
        'de-CH' => 'German/Switzerland',
        'dv' => 'Dhivehi',
        'dz' => 'Dzongkha',
        'ee' => 'Ewe',
        'el' => 'Greek',
        'en' => 'English',
        'en-AU' => 'English/Australia',
        'en-BZ' => 'English/Belize',
        'en-CA' => 'English/Canada',
        'en-IE' => 'English/Ireland',
        'en-JM' => 'English/Jamaica',
        'en-NZ' => 'English/New Zealand',
        'en-PH' => 'English/Philippines',
        'en-ZA' => 'English/South Africa',
        'en-TT' => 'English/Trinidad and Tobago',
        'en-UK' => 'English/United Kingdom',
        'en-US' => 'English/United States',
        'en-ZW' => 'English/Zimbabwe',
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
        'fr-BE' => 'French/Belgium',
        'fr-CA' => 'French/Canada',
        'fr-FR' => 'French/France',
        'fr-LU' => 'French/Luxembourg',
        'fr-MC' => 'French/Monaco',
        'fr-CH' => 'French/Switzerland',
        'fy' => 'Western Frisian',
        'ga' => 'Irish',
        'gd' => 'Scottish Gaelic',
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
        'ik' => 'Inupiaq',
        'io' => 'Ido',
        'is' => 'Icelandic',
        'it' => 'Italian',
        'it-CH' => 'Italian/Switzerland',
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
        'ko-KP' => 'Korean/North Korea',
        'ko-KR' => 'Korean/South Korea',
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
        'nl-BE' => 'Dutch/Belgium',
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
        'pt-BR' => 'Portuguese/Brazil',
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
        'zh-CN' => 'Chinese/China',
        'zh-HK' => 'Chinese/Hong Kong',
        'zh-SG' => 'Chinese/Singapore',
        'zh-TW' => 'Chinese/Taiwan',
        'zu' => 'Zulu');

}