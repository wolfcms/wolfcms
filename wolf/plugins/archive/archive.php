<?php

/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2009-2011 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The Archive plugin provides an Archive pagetype behaving similar to a blog or news archive.
 *
 * @package Plugins
 * @subpackage archive
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2011
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright Philippe Archambault, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) {
    exit();
}

/**
 * The Archive class...
 */
class Archive {

    public function __construct(&$page, $params) {
        $this->page = & $page;
        $this->params = $params;

        switch (count($params)) {
            case 0: break;
            case 1:
                if (strlen((int) $params[0]) == 4)
                    $this->_archiveBy('year', $params);
                else
                    $this->_displayPage($params[0]);
                break;

            case 2:
                $this->_archiveBy('month', $params);
                break;

            case 3:
                $this->_archiveBy('day', $params);
                break;

            case 4:
                $this->_displayPage($params[3]);
                break;

            default:
                pageNotFound();
        }
    }

    private function _archiveBy($interval, $params) {
        $this->interval = $interval;

        $page = $this->page->children(array(
                    'where' => "behavior_id = 'archive_{$interval}_index'",
                    'limit' => 1
                        ), array(), true);

        if ($page instanceof Page) {
            $this->page = $page;
            $month = isset($params[1]) ? (int) $params[1] : 1;
            $day = isset($params[2]) ? (int) $params[2] : 1;

            $this->page->time = mktime(0, 0, 0, $month, $day, (int) $params[0]);
        } else {
            pageNotFound();
        }
    }

    private function _displayPage($slug) {
        if (!$this->page = Page::findBySlug($slug, $this->page, true))
            pageNotFound($slug);
    }

    function get() {
        // Make sure params are numeric
        foreach ($this->params as $param) {
            if (!is_numeric($param)) {
                // TODO replace by decent error message
                pageNotFound();
            }
        }
        
        $date = join('-', $this->params);

        $pages = $this->page->parent()->children(array(
                    'where' => 'page.created_on LIKE :date',
                    'order' => 'page.created_on DESC'
                ), array(':date' => ''.$date.'%'));

        return $pages;
    }

    function archivesByYear() {
      $tablename = TABLE_PREFIX.'page';

      $out = array();

      $res = Record::find(array(
                  'select' => "DISTINCT(DATE_FORMAT(created_on, '%Y')) AS date",
                  'from' => $tablename,
                  'where' => 'parent_id = :parent_id AND status_id != :status',
                  'order_by' => 'created_on DESC',
                  'values' => array(':parent_id' => $this->page->id, ':status' => Page::STATUS_HIDDEN )
                ));

      foreach($res as $r) {
        $out[] = $r->date;
      }

      return $out;
    }

    function archivesByMonth($year='all') {
        $tablename = TABLE_PREFIX.'page';

        $out = array();

        $res = Record::find(array(
                    'select' => "DISTINCT(DATE_FORMAT(created_on, '%Y/%m')) AS date",
                    'from' => $tablename,
                    'where' => 'parent_id = :parent_id AND status_id != :status',
                    'order_by' => 'created_on DESC',
                    'values' => array(':parent_id' => $this->page->id, ':status' => Page::STATUS_HIDDEN )
                  ));

        foreach($res as $r) {
          $out[] = $r->date;
        }

        return $out;
    }

    function archivesByDay($year='all') {
      $tablename = TABLE_PREFIX.'page';

      $out = array();

      $res = Record::find(array(
                  'select' => "DISTINCT(DATE_FORMAT(created_on, '%Y/%m/%d')) AS date",
                  'from' => $tablename,
                  'where' => 'parent_id = :parent_id AND status_id != :status',
                  'order_by' => 'created_on DESC',
                  'values' => array(':parent_id' => $this->page->id, ':status' => Page::STATUS_HIDDEN )
                ));

      foreach($res as $r) {
        $out[] = $r->date;
      }

      return $out;
    }

}

class PageArchive extends Page {

    /**
     * Returns the current PageArchive object's url.
     *
     * Note: overrides the Page::url() method.
     *
     * @return string   A fully qualified url.
     */
    public function url($suffix=false) {
        $use_date = Plugin::getSetting('use_dates', 'archive');
        if ($use_date === '1') {
            return BASE_URL . trim($this->parent()->path() . date('/Y/m/d/', strtotime($this->created_on)) . $this->slug, '/') . ($this->path() != '' ? URL_SUFFIX : '');
        }
        elseif ($use_date === '0') {
            return BASE_URL . trim($this->parent()->path() . '/' . $this->slug, '/') . ($this->path() != '' ? URL_SUFFIX : '');
        }
    }

    public function title() {
        return isset($this->time) ? strftime($this->title, $this->time) : $this->title;
    }

    public function breadcrumb() {
        return isset($this->time) ? strftime($this->breadcrumb, $this->time) : $this->breadcrumb;
    }

}
