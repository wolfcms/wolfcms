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
 * The Comment plugin provides an interface to enable adding and moderating page comments.
 *
 * @package Plugins
 * @subpackage comment
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Bebliuc George <bebliuc.george@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Philippe Archambault, Bebliuc George & Martijn van der Kleijn, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
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
	imagettftext($img,rand(25,30),rand(-10,10),rand(10,30),rand(25,35),$background_color,"fonts/wolf.ttf",$expression);

	header("Content-type:image/jpeg");
	header("Content-Disposition:inline ; filename=secure.jpg");
	imagejpeg($img);
?>
