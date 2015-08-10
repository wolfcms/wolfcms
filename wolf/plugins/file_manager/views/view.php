<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * The FileManager allows users to upload and manipulate files.
 *
 * @package Plugins
 * @subpackage file-manager
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Philippe Archambault & Martijn van der Kleijn, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

function htmlContextCleaner($input) {
    $bad_chars = array("<", ">");
    $safe_chars = array("&lt;", "&gt;");
    $output = str_replace($bad_chars, $safe_chars, $input);

    return stripslashes($output);
}


  $out = '';
  $progres_path = '';
  $paths = explode('/', $filename);
  $nb_path = count($paths);
  foreach ($paths as $i => $path) {
    if ($i+1 == $nb_path) {
      $out .= $path;
    } else {
      $path = preg_replace('/.*:\/\/[^\/]+\//', '/', $path);
      $progres_path .= $path.'/';
      $out .= '<a href="'.get_url('plugin/file_manager/browse/'.rtrim($progres_path, '/')).'">'.htmlContextCleaner($path).'</a>/';
    }
  }
?>
<h1><a href="<?php echo get_url('plugin/file_manager'); ?>">public</a>/<?php echo $out; ?></h1>
<?php if ($is_image) { ?>
  <img src="<?php echo BASE_FILES_DIR.'/'.$filename; ?>" />
<?php } else { ?>
<form method="post" action="<?php echo get_url('plugin/file_manager/save'); ?>">
    <div class="form-area">
        <p class="content">
            <label for="file_filter_id"><?php echo __('Filter'); ?></label>
            <select id="file_filter_id" class="filter-selector" name="file[filter_id]">
                <option value="">&#8212; <?php echo __('none'); ?> &#8212;</option>
                <?php foreach (Filter::findAll() as $filter): ?>
                    <option value="<?php echo $filter; ?>"><?php echo Inflector::humanize($filter); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="file[name]" value="<?php echo $filename; ?>" />
            <input id="csrf_token" name="csrf_token" type="hidden" value="<?php echo $csrf_token; ?>" />
            <textarea class="textarea" id="file_content" name="file[content]" style="width: 100%; height: 400px;" rows="20" cols="40"><?php echo htmlentities($content, ENT_COMPAT, 'UTF-8'); ?></textarea><br />
        </p>
    </div>
    <p class="buttons">
        <input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save'); ?>" />
        <input class="button" name="continue" type="submit" accesskey="e" value="<?php echo __('Save and Continue Editing'); ?>" />
        <?php echo __('or'); ?> <a href="<?php echo get_url('plugin/file_manager/browse/'.$progres_path); ?>"><?php echo __('Cancel'); ?></a>
    </p>
</form>
<?php } ?>
