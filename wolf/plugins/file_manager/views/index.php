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
  $paths = explode('/', $dir);
  $nb_path = count($paths)-1; // -1 to didn't display current dir as a link
  foreach ($paths as $i => $path) {
    if ($i+1 == $nb_path) {
      $out .= $path;
    } else if ($path != '') {
      $path = preg_replace('/.*:\/\/[^\/]+\//', '/', $path);
      $progres_path .= $path.'/';
      $out .= '<a href="'.get_url('plugin/file_manager/browse/'.rtrim($progres_path, '/')).'">'.htmlContextCleaner($path).'</a>/';
    }
  }
?>
<h1><a href="<?php echo get_url('plugin/file_manager'); ?>">public</a>/<?php echo $out; ?></h1>
<table id="files-list" class="index" cellpadding="0" cellspacing="0" border="0">
  <thead>
    <tr>
      <th class="files"><?php echo __('File'); ?></th>
      <th class="size"><?php echo __('Size'); ?></th>
      <th class="permissions"><?php echo __('Permissions'); ?></th>
      <th class="mtime"><?php echo __('Modified'); ?></th>
      <th class="modify"><?php echo __('Modify'); ?></th>
    </tr>
  </thead>
  <tbody>
<?php foreach ($files as $file): ?>
    <tr class="<?php echo odd_even(); ?>">
      <td>
          <?php if (preg_match('/\.(jpg|jpeg|pjpeg|png|gif|ico)$/i', $file->name)) { ?>
          <img class="thumb" src="<?php echo PATH_PUBLIC;?>public/<?php echo $dir.$file->name; ?>" align="middle" />
          <?php } else { ?>
          <img src="<?php echo ICONS_PATH;?>file-<?php echo $file->type ?>-16.png" align="top" />
          <?php } ?>
          <?php echo $file->link; ?>
      </td>
      <td><code><?php echo $file->size; ?></code></td>
      <td><code><?php echo $file->perms; ?> (<a href="#" onclick="toggle_chmod_popup('<?php echo $dir.$file->name; ?>', '<?php echo $file->chmod; ?>'); return false;" title="<?php echo __('Change mode'); ?>"><?php echo $file->chmod; ?></a>)</code></td>
      <td><code><?php echo $file->mtime; ?></code></td>
      <td>
        <a href="#" onclick="toggle_rename_popup('<?php echo $dir.$file->name; ?>', '<?php echo $file->name; ?>'); return false;" title="<?php echo __('Rename'); ?>"><img src="<?php echo ICONS_PATH;?>action-rename-16.png" alt="rename icon" /></a>
        <a href="<?php echo get_url('plugin/file_manager/delete/'.$dir.$file->name.'?csrf_token='.SecureToken::generateToken(BASE_URL.'plugin/file_manager/delete/'.$dir.$file->name)); ?>" onclick="return confirm('<?php echo __('Are you sure you wish to delete?'); ?> <?php echo $file->name; ?>?');"><img src="<?php echo ICONS_PATH;?>action-delete-16.png" alt="<?php echo __('delete file icon'); ?>" title="<?php echo __('Delete file'); ?>" /></a>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>

<div id="popups">

  <div class="popup" id="chmod-popup" style="display:none;">
    <h3><?php echo __('Change mode'); ?></h3>
    <form action="<?php echo get_url('plugin/file_manager/chmod'); ?>" method="post">
      <div>
        <input id="csrf_token" name="csrf_token" type="hidden" value="<?php echo SecureToken::generateToken(BASE_URL.'plugin/file_manager/chmod'); ?>" />
        <input id="chmod_file_name" name="file[name]" type="hidden" value="" />
        <input id="chmod_file_mode" maxlength="4" name="file[mode]" type="text" value="" />
        <input id="chmod_file_button" name="commit" type="submit" value="<?php echo __('Change mode'); ?>" />
      </div>
      <p><a class="close-link" href="#" onclick="toggle_chmod_popup(); return false;"><?php echo __('Close'); ?></a></p>
    </form>
  </div>
  <div class="popup" id="rename-popup" style="display:none;">
      <h3><?php echo __('Rename'); ?></h3>
      <form action="<?php echo get_url('plugin/file_manager/rename'); ?>" method="post">
        <div>
          <input id="csrf_token" name="csrf_token" type="hidden" value="<?php echo SecureToken::generateToken(BASE_URL.'plugin/file_manager/rename'); ?>" />
          <input id="rename_file_current_name" name="file[current_name]" type="hidden" value="" />
          <input id="rename_file_new_name" maxlength="50" name="file[new_name]" type="text" value="" />
          <input id="rename_file_button" name="commit" type="submit" value="<?php echo __('Rename'); ?>" />
        </div>
        <p><a class="close-link" href="#" onclick="toggle_rename_popup(); return false;"><?php echo __('Close'); ?></a></p>
      </form>
    </div>
</div>

<div id="boxes">
	<!-- #Demo dialog -->
	<div id="dialog" class="window">
		<div class="titlebar">
            Demo dialog
            <a href="#" class="close"><img src="<?php echo ICONS_PATH;?>action-delete-disabled-16.png"/></a>
        </div>
        <div class="content">
            <p>This is just a demo.</p>
        </div>
	</div>

    <div id="create-file-popup" class="window">
		<div class="titlebar">
            <?php echo __('Create new file'); ?>
            <a href="#" class="close"><img src="<?php echo ICONS_PATH;?>action-delete-disabled-16.png"/></a>
        </div>
        <div class="content">
            <form action="<?php echo get_url('plugin/file_manager/create_file'); ?>" method="post">
                <input id="csrf_token" name="csrf_token" type="hidden" value="<?php echo SecureToken::generateToken(BASE_URL.'plugin/file_manager/create_file'); ?>" />
                <input id="create_file_path" name="file[path]" type="hidden" value="<?php echo ($dir == '') ? '/': $dir; ?>" />
                <input id="create_file_name" maxlength="255" name="file[name]" type="text" value="" />
                <input id="create_file_button" name="commit" type="submit" value="<?php echo __('Create'); ?>" />
            </form>
        </div>
    </div>

    <div id="create-directory-popup" class="window">
		<div class="titlebar">
            <?php echo __('Create new directory'); ?>
            <a href="#" class="close"><img src="<?php echo ICONS_PATH;?>action-delete-disabled-16.png"/></a>
        </div>
        <div class="content">
            <form action="<?php echo get_url('plugin/file_manager/create_directory'); ?>" method="post">
                <input id="csrf_token" name="csrf_token" type="hidden" value="<?php echo SecureToken::generateToken(BASE_URL.'plugin/file_manager/create_directory'); ?>" />
                <input id="create_directory_path" name="directory[path]" type="hidden" value="<?php echo ($dir == '') ? '/': $dir; ?>" />
                <input id="create_directory_name" maxlength="255" name="directory[name]" type="text" value="" />
                <input id="file_button" name="commit" type="submit" value="<?php echo __('Create'); ?>" />
            </form>
        </div>
    </div>

    <div id="upload-file-popup" class="window">
		<div class="titlebar">
            <?php echo __('Upload file'); ?>
            <a href="#" class="close"><img src="<?php echo ICONS_PATH;?>action-delete-disabled-16.png"/></a>
        </div>
        <div class="content">
            <form action="<?php echo get_url('plugin/file_manager/upload'); ?>" method="post" enctype="multipart/form-data">
                <input id="csrf_token" name="csrf_token" type="hidden" value="<?php echo SecureToken::generateToken(BASE_URL.'plugin/file_manager/upload'); ?>" />
                <input id="upload_overwrite" name="upload[overwrite]" type="checkbox" value="1" /> <label for="upload_overwrite"><small><?php echo __('overwrite it?'); ?></small></label><br />
                <input id="upload_path" name="upload[path]" type="hidden" value="<?php echo ($dir == '') ? '/': $dir; ?>" />
                <input id="upload_file" name="upload_file" type="file" />
                <input id="upload_file_button" name="commit" type="submit" value="<?php echo __('Upload'); ?>" />
            </form>
        </div>
    </div>



    <!-- Do not remove div#mask, because you'll need it to fill the whole screen -->
 	<div id="mask"></div>

</div>
