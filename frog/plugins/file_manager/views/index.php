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
 * The FileManager allows users to upload and manipulate files.
 *
 * @package frog
 * @subpackage plugin.file_manager
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 1.0.0
 * @since Frog version 0.9.0
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault & Martijn van der Kleijn, 2008
 */

  $out = '';
  $progres_path = '';
  $paths = explode('/', $dir); 
  $nb_path = count($paths)-1; // -1 to didn't display current dir as a link
  foreach ($paths as $i => $path) {
    if ($i+1 == $nb_path) {
      $out .= $path;
    } else if ($path != '') {
      $progres_path .= $path.'/';
      $out .= '<a href="'.get_url('plugin/file_manager/browse/'.rtrim($progres_path, '/')).'">'.$path.'</a>/';
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
      <th class="mtime"><?php echo __('Modify'); ?></th>
      <th class="modify"><?php echo __('Action'); ?></th>
    </tr>
  </thead>
  <tbody>
<?php foreach ($files as $file): ?>
    <tr class="<?php echo odd_even(); ?>">
      <td>
        <?php if ($file->is_dir) { ?>
            <img src="../frog/plugins/file_manager/images/dir_16.png" align="top" alt="dir icon" />
        <?php } else { ?>
            <img src="../frog/plugins/file_manager/images/page_16.png" align="top" alt="page icon" />
        <?php } ?>
        <?php echo $file->link; ?>
      </td>
      <td><code><?php echo $file->size; ?></code></td>
      <td><code><?php echo $file->perms; ?> (<a href="#" onclick="toggle_chmod_popup('<?php echo $dir.$file->name; ?>'); return false;" title="<?php echo __('Change mode'); ?>"><?php echo $file->chmod; ?></a>)</code></td>
      <td><code><?php echo $file->mtime; ?></code></td>
      <td>
        <a href="#" onclick="toggle_rename_popup('<?php echo $dir.$file->name; ?>', '<?php echo $file->name; ?>'); return false;" title="<?php echo __('Rename'); ?>"><img src="images/icon-rename.gif" alt="rename icon" /></a> 
        <a href="<?php echo get_url('plugin/file_manager/delete/'.$dir.$file->name); ?>" onclick="return confirm('<?php echo __('Are you sure you wish to delete'); ?> <?php echo $file->name; ?>?');"><img src="images/icon-remove.gif" alt="remove icon" /></a>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>

<div id="popups">
  <div class="popup" id="create-file-popup" style="display:none;">
    <h3><?php echo __('Create new file'); ?></h3>
    <form action="<?php echo get_url('plugin/file_manager/create_file'); ?>" method="post"> 
      <div>
        <input id="create_file_path" name="file[path]" type="hidden" value="<?php echo ($dir == '') ? '/': $dir; ?>" />
        <input id="create_file_name" maxlength="255" name="file[name]" type="text" value="" /> 
        <input id="create_file_button" name="commit" type="submit" value="<?php echo __('Create'); ?>" />
      </div>
      <p><a class="close-link" href="#" onclick="Element.hide('create-file-popup'); return false;"><?php echo __('Close'); ?></a></p>
    </form>
  </div>
  <div class="popup" id="create-directory-popup" style="display:none;">
    <h3><?php echo __('Create new directory'); ?></h3>
    <form action="<?php echo get_url('plugin/file_manager/create_directory'); ?>" method="post">
      <div>
        <input id="create_directory_path" name="directory[path]" type="hidden" value="<?php echo ($dir == '') ? '/': $dir; ?>" />
        <input id="create_directory_name" maxlength="255" name="directory[name]" type="text" value="" /> 
        <input id="file_button" name="commit" type="submit" value="<?php echo __('Create'); ?>" />
      </div>
      <p><a class="close-link" href="#" onclick="Element.hide('create-directory-popup'); return false;"><?php echo __('Close'); ?></a></p>
    </form>
  </div>
  <div class="popup" id="upload-file-popup" style="display:none;">
    <form action="<?php echo get_url('plugin/file_manager/upload'); ?>" method="post" enctype="multipart/form-data"> 
      <h3><?php echo __('Upload file'); ?> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input id="upload_overwrite" name="upload[overwrite]" type="checkbox" value="1" /><label for="upload_overwrite"><small><?php echo __('overwrite it?'); ?></small></label></h3>
      <div>
        <input id="upload_path" name="upload[path]" type="hidden" value="<?php echo ($dir == '') ? '/': $dir; ?>" />
        <input id="upload_file" name="upload_file" type="file" />
        <input id="upload_file_button" name="commit" type="submit" value="<?php echo __('Upload'); ?>" />
      </div>
      <p><a class="close-link" href="#" onclick="Element.hide('upload-file-popup'); return false;"><?php echo __('Close'); ?></a></p>
    </form>
  </div>
  <div class="popup" id="chmod-popup" style="display:none;">
    <h3><?php echo __('Change mode'); ?></h3>
    <form action="<?php echo get_url('plugin/file_manager/chmod'); ?>" method="post"> 
      <div>
        <input id="chmod_file_name" name="file[name]" type="hidden" value="" />
        <input id="chmod_file_mode" maxlength="4" name="file[mode]" type="text" value="" /> 
        <input id="chmod_file_button" name="commit" type="submit" value="<?php echo __('Change mode'); ?>" />
      </div>
      <p><a class="close-link" href="#" onclick="Element.hide('chmod-popup'); return false;"><?php echo __('Close'); ?></a></p>
    </form>
  </div>
  <div class="popup" id="rename-popup" style="display:none;">
      <h3><?php echo __('Rename'); ?></h3>
      <form action="<?php echo get_url('plugin/file_manager/rename'); ?>" method="post"> 
        <div>
          <input id="rename_file_current_name" name="file[current_name]" type="hidden" value="" />
          <input id="rename_file_new_name" maxlength="50" name="file[new_name]" type="text" value="" /> 
          <input id="rename_file_button" name="commit" type="submit" value="<?php echo __('Rename'); ?>" />
        </div>
        <p><a class="close-link" href="#" onclick="Element.hide('rename-popup'); return false;"><?php echo __('Close'); ?></a></p>
      </form>
    </div>
</div>