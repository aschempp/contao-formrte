<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class FormRTE extends Controller
{
	
	/**
	 * Inject the RTE editor if available.
	 * 
	 * @access public
	 * @param object $objWidget
	 * @param int $intForm
	 * @return object
	 */
	public function loadFormField($objWidget, $intForm)
	{
		if(TL_MODE == 'BE')
		{
			return $objWidget;
		}

		// Register field name for rich text editor usage
		if ($objWidget instanceof FormTextArea && strlen($objWidget->rte))
		{
			$GLOBALS['TL_CONFIG']['useRTE'] = true;
			$GLOBALS['TL_RTE']['type'] = $objWidget->rte;
			$GLOBALS['TL_RTE']['fields'][] = 'ctrl_' . $objWidget->id;
		}
		
		// Rich text editor configuration
		if (count($GLOBALS['TL_RTE']) && $GLOBALS['TL_CONFIG']['useRTE'])
		{
			$this->base = $this->Environment->base;
			$this->brNewLine = $GLOBALS['TL_CONFIG']['pNewLine'] ? false : true;
			$this->rteFields = implode(',', $GLOBALS['TL_RTE']['fields']);

			$strFile = sprintf('%s/system/config/%s.php', TL_ROOT, $GLOBALS['TL_RTE']['type']);

			if (!file_exists($strFile))
			{
				throw new Exception(sprintf('Cannot find rich text editor configuration file "%s.php"', $GLOBALS['TL_RTE']['type']));
			}

			$this->language = 'en';

			// Fallback to English if the user language is not supported
			if (file_exists(TL_ROOT . '/plugins/tinyMCE/langs/' . $GLOBALS['TL_LANGUAGE'] . '.js'))
			{
				$this->language = $GLOBALS['TL_LANGUAGE'];
			}

			ob_start();
			include($strFile);
			$GLOBALS['TL_HEAD']['rte'] = ob_get_contents();
			ob_end_clean();
			$GLOBALS['TL_JAVASCRIPT']['rte'] = 'contao/contao.js';
			
			// register all fields for tinyMCE.
			// required because tinyMCE mode equals 'none'
			$GLOBALS['TL_MOOTOOLS']['rte'] = '<script>';
			foreach($GLOBALS['TL_RTE']['fields'] as $fieldName) 
			{
				$GLOBALS['TL_MOOTOOLS']['rte'] .=	"tinyMCE.execCommand('mceAddControl', false, '$fieldName');\$('$fieldName').erase('required');";
			}
			$GLOBALS['TL_MOOTOOLS']['rte'] .= '</script>';

		}
		
		return $objWidget;
	}
	
	
	/**
	 * Return a list of tinyMCE config files in this system.
	 * 
	 * @access public
	 * @return array
	 */
	public function getConfigFiles()
	{
		$arrConfigs = array();
		$arrFiles = scan(TL_ROOT . '/system/config/');
		
		foreach( $arrFiles as $file )
		{
			if (substr($file, 0, 4) == 'tiny')
			{
				$arrConfigs[] = basename($file, '.php');
			}
		}
		
		return $arrConfigs;
	}
}

