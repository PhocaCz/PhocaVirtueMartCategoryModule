<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component   Phoca Component
 * @copyright   Copyright (C) Jan Pavelka www.phoca.cz
 * @license     http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later
 */
 
 /*
* Best selling Products module for VirtueMart
* @version $Id: mod_virtuemart_category.php 1160 2008-01-14 20:35:19Z soeren_nb $
* @package VirtueMart
* @subpackage modules
*
* @copyright (C) John Syben (john@webme.co.nz)
* Conversion to Mambo and the rest:
* 	@copyright (C) 2004-2005 Soeren Eberhardt
*
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/
defined( '_JEXEC' ) or die( 'Restricted access' ); // no direct access

require('helper.php');
JTable::addIncludePath(JPATH_VM_ADMINISTRATOR.DS.'tables');


function PhocaVmCategoryTree ($category_id, $vendorId, $cache, $categoryModel, $p) {
	
	static $level 		= 0;
	static $columns 	= 0;// == $submenu
	static $parentmenu	= 0;
	
	//$categories		= $cache->call( array( 'VirtueMartModelCategory', 'getChildCategoryList' ),$vendorId, $category_id );
	$categories 	= $categoryModel->getChildCategoryList($vendorId, $category_id, 'c.ordering');
	
	
	
	if ($p['allcategories'] == 1 && $level == 0) {
		$categories = array();
		$categories[0] = new stdClass();
		$categories[0]->virtuemart_category_id = '0';
		$categories[0]->category_name = JText::_('MOD_PHOCA_VM_CATEGORY_ALL_CATEGORIES');
		$categories[0]->category_description = JText::_('MOD_PHOCA_VM_CATEGORY_ALL_CATEGORIES');
		$categories[0]->metadesc = '';
		$categories[0]->metakey = '';
		$categories[0]->slug = '';
		$categories[0]->virtuemart_media_id = array();
		$parentmenu++;
	}
	
	if (!empty($categories)) {
		
		$ulA 			= '';
		$submenustyle 	= '';
		
		if ($level == 0) {
			$ulA = ' id="pvmc-menu"';
		} else if ($level == 1){
			if ($p['submenustyle'] != '') {
				$submenustyle = $p['submenustyle'];
			}
			$ulA = ' class="level'.$level.' child pvmc-submenu" style="'.strip_tags($submenustyle).'"';
		} else {
			$ulA = ' class="level'.$level.' child"';
		}
		
		echo "\n\n";
		echo  '<ul'.$ulA.'>'."\n";
		
		foreach ($categories as $c) {

			//$childCats 	= $cache->call( array( 'VirtueMartModelCategory', 'getChildCategoryList' ),$vendorId, $c->virtuemart_category_id );
			$childCats 	= $categoryModel->getChildCategoryList($vendorId, $c->virtuemart_category_id, 'c.ordering');
			$url 		= JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$c->virtuemart_category_id);
			
			$parent 		= '';
			$drop 			= '';
			$columnstyle 	= '';
			
			if (isset($childCats) && !empty($childCats)) {
				$parent = ' parent';
				$drop	= ' class="drop"';
				// Only design issue - no submenu, no style
				if ((int)$p['countlevels'] == 1) {
					$parent = '';
					$drop	= '';
				}
				
			}
			
			if ($p['columnstyle'] != '' && $level == 1) {
				$columnstyle = $p['columnstyle'];
				
				if ($p['countcolumns'] == $columns && $level == 1) {
					$columnstyle = $p['columnstyle'] . ';clear: both;';
					$columns = 0;
				} 
			}
			
			if ($level == 0) {
				$parentmenu++;
				echo  '<li class="level'.$level.''.$parent.'"><a '.$drop.' href="'.$url.'" >'.$c->category_name.'</a>' . "\n";
			} else {
				echo  '<li class="level'.$level.''.$parent.'" style="'.strip_tags($columnstyle).'"><a href="'.$url.'" >'.$c->category_name.'</a>' . "\n";
			}
			
			if ($level == 1) {
			
				$columns++;
				
				if ($p['enablethumbs'] == 1) {
				
					$categoryModel->addImages($c);
					if (isset($c->images[0]->file_url_thumb) && $c->images[0]->file_url_thumb != '') {
						$img = '<img alt="" src="'.JURI::base(true).'/'.$c->images[0]->file_url_thumb.'" />';
						echo '<div class="pvmc-submenu-img">'.$img.'</div>';
					}
				}
				
				if ($p['enabledesc'] == 1) {
					echo '<div class="pvmc-submenu-desc">'.$c->category_description.'</div>';
				}
			}
			
			if (isset($childCats) && !empty($childCats)) {
				$level++;
				
				if ((int)$p['countlevels'] == (int)$level) {
					$level--;
				} else {
					PhocaVmCategoryTree($c->virtuemart_category_id, $vendorId, $cache, $categoryModel, $p);
					$level--;
				}
			}
			echo  '</li>'."\n";
			
			
			
			if ((int)$p['countsubmenu'] == (int)$columns && $level == 1) {
				$level = 1;
				break;
			}
			
			if ((int)$p['countparentmenu'] == (int)$parentmenu && $level == 0) {
				echo  '</ul>'."\n\n";
				return false;
			}
		}
		echo  '</ul>'."\n\n";
	}
}

// Params
$vendorId 				= '1';
$categoryModel			= new VirtueMartModelCategory();
$cache 					= & JFactory::getCache('com_virtuemart','callback');

$category_id 			= $params->get('parent_category_id', 0);
$p['allcategories']		= $params->get('all_categories', 0);
$p['enabledesc']		= $params->get('enable_desc', 0);
$p['enablethumbs']		= $params->get('enable_thumbs', 0);
$p['columnstyle']		= $params->get('column_style', 'width: 120px; position: relative; float: left; padding: 0px 10px;');
$p['submenustyle']		= $params->get('submenu_style', 'width: 560px;');
$p['countparentmenu'] 	= $params->get('count_parentmenu', 8);//zero for unlimited
$p['countsubmenu'] 		= $params->get('count_submenu', 8);//zero for unlimited
$p['countcolumns'] 		= $params->get('count_columns', 4);//zero for unlimited
$p['countlevels'] 		= $params->get('count_levels', 10);// zero for unlimited



echo '<div id="pvmc-wrap">';
PhocaVmCategoryTree ($category_id, $vendorId, $cache, $categoryModel, $p);
echo '</div>';
echo '<div style="margin-bottom: 10px;clear:both;"> </div>';


//$document			= &JFactory::getDocument();
JHTML::stylesheet( 'modules/mod_phoca_vm_category/assets/style.css' );
JHTML::stylesheet( 'modules/mod_phoca_vm_category/assets/custom.css' );
	
require(JModuleHelper::getLayoutPath('mod_phoca_vm_category'));