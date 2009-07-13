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
 * The Comment plugin provides an interface to enable adding and moderating page comments.
 *
 * @package frog
 * @subpackage plugin.comment
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Bebliuc George <bebliuc.george@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 1.2.0
 * @since Frog version 0.9.3
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, Bebliuc George & Martijn van der Kleijn, 2008
 */
	session_start();
	$operators=array('+','-','*');
	$first_num=rand(1,5);
	$second_num=rand(6,11);
	shuffle($operators);
	$expression=$second_num.$operators[0].$first_num;
	eval("\$session_var=".$second_num.$operators[0].$first_num.";");

	$_SESSION['security_number']=$session_var;
	
	$img=imagecreatefromjpeg("test.jpg");

	$text_color		 = imagecolorallocate($img,255,255,255);
	
	$background_color= imagecolorallocate($img,255,255,255);
	

	
	imagefill($img,0,150,$background_color);
	imagettftext($img,rand(25,30),rand(-10,10),rand(10,30),rand(25,35),$background_color,"fonts/frog.ttf",$expression);

	header("Content-type:image/jpeg");
	header("Content-Disposition:inline ; filename=secure.jpg");
	imagejpeg($img);
?>