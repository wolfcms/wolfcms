<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2014 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/**
 * @package Models
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2008-2010
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 License
 */

/**
 * An abstract class that is the base class for all HTML page models. By extending the Node class,
 * AbstractPage inherits it's URL hierarchy methods. AbstractPage adds a couple of abstract methods
 * that are required for pages to be displayed correctly in a layout, without prescribing how those
 * methods should be implemented. This makes for a flexible page system that can easily be hooked
 * into by plugins that generate output for the frontend.
 *
 * @abstract
 * @author Nic Wortel <nic.wortel@nth-root.nl>
 */
abstract class AbstractPage extends Node {

}
