<?php

/**
 * Parsimony
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@parsimony.mobi so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Parsimony to newer
 * versions in the future. If you wish to customize Parsimony for your
 * needs please refer to http://www.parsimony.mobi for more information.
 *
 *  @authors Julien Gras et Benoît Lorillot
 *  @copyright  Julien Gras et Benoît Lorillot
 *  @version  Release: 1.0
 * @category  Parsimony
 * @package Parsimony
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace admin;

/**
 * Admin Class 
 * Manage the administration of Parsimony
 */
class admin extends \module {
    
    protected $name = 'admin';

    /** @var string theme name */
    private $theme;

    /** @var string module name */
    private $module;

    /**
     * Controller post
     * @param string $action
     * @return false 
     */
    public function controllerPOST($action) {
	if (ID_ROLE != 1)
	    return $this->returnResult(array('eval' => '', 'notification' => t('Permission denied', FALSE), 'notificationType' => 'negative'));
	if (!empty($action)) {
	    $this->theme = \theme::get(THEMEMODULE, THEME, THEMETYPE);
	    $this->module = \app::getModule(MODULE);
	    if(isset($_POST['IDPage']) && is_numeric($_POST['IDPage'])) $this->page = $this->module->getPage($_POST['IDPage']);
	    return $this->controller($action);
	}
    }

    /**
     * Edit In Line
     * @param string $module
     * @param string $model
     * @param string $property
     * @param string $id
     * @param string $value
     * @return string 
     */
    protected function editInLineAction($module, $model, $property, $id, $value) {
	unset($_POST['action']);
	$obj = \app::getModule($module)->getEntity($model);
	$query = 'UPDATE ' . $module . '_' . $model . ' SET ' . $property . ' = \' ' . addslashes($value) . '\'';
	$query .= ' WHERE ' . $obj->getId()->name . '=' . $id . ';';
	$res = \PDOconnection::getDB()->exec($query);
	if ($res) {
	    $return = array('eval' => '', 'notification' => t('The data has been saved', FALSE), 'notificationType' => 'positive');
	} else {
	    $return = array('eval' => '', 'notification' => t('The data has not been saved', FALSE), 'notificationType' => 'negative');
	}
	return $this->returnResult($return);
    }

    /**
     * Search Block
     * @param string $container
     * @param string $identifiant
     * @return string 
     */
    private function &search_block($container, $identifiant) {
	if ($container->getId() === $identifiant || (is_numeric($identifiant) && $container->getId() === (int) $identifiant))
	    return $container;
	$blocks = $container->getBlocks();
	if (!empty($blocks)) {
	    foreach ($blocks AS $id => $block) {
		if ($id === $identifiant) {
		    return $block;
		} else {
		    $rbloc = & $this->search_block($block, $identifiant);
		    if (isset($rbloc))
			return $rbloc;
		}
	    }
	}
	return $rbloc;
    }

    /**
     * Check If Id Exists 
     * @param string $id
     * @return bool 
     */
    private function checkIfIdExists($id) {
	if ($this->search_block($this->theme, $id) != NULL)
	    return TRUE;
	foreach (\app::$activeModules as $module => $type) {
	    $moduleObj = \app::getModule($module);
	    foreach ($moduleObj->getPages() as $key => $page) {
		$block = $this->search_block($page, $id);
		if ($block != NULL)
		    return TRUE;
	    }
	    if (is_file('modules/' . $module . '/views/web/' . $id . '.php'))
		return TRUE;
            /*if (is_file(PROFILE_PATH . $module . '/views/web/' . $id . '.php'))
		return TRUE;*/
	}
	return FALSE;
    }

    /**
     * Add a block
     * @param string $popBlock
     * @param string $parentBlock
     * @param string $idBlock
     * @param string $id_next_block
     * @param string $stop_typecont
     * @return string 
     */
    protected function addBlockAction($popBlock, $parentBlock, $idBlock, $id_next_block, $stop_typecont) {
        $tempBlock = new $popBlock($idBlock);
        $idBlock = $tempBlock->getId();
        if ($this->checkIfIdExists($idBlock)) {
            return $this->returnResult(array('eval' => '', 'notification' => t('ID block already exists, please choose antother', FALSE)));
        }
	$block = $this->search_block($this->$stop_typecont, $parentBlock);
        $block->addBlock($tempBlock, $id_next_block);
        $this->saveAll();
        if ($this->search_block($this->$stop_typecont, $idBlock) != NULL) {
            \app::$request->page = new \page(999);
	    $return = array('eval' => $tempBlock->ajaxRefresh('add'), 'jsFiles' => json_encode(\app::$request->page->getJSFiles()), 'CSSFiles' => json_encode(\app::$request->page->getCSSFiles()), 'notification' => t('The Block is saved', FALSE), 'notificationType' => 'positive');
        }else
            $return = array('eval' => '', 'notification' => t('Error on drop', FALSE), 'notificationType' => 'negative');
        return $this->returnResult($return);
    }

    /**
     * Save the block configs
     * @param string $typeProgress
     * @param string $idBlock
     * @param string $maxAge
     * @param string $tag
     * @param string $allowedModules
     * @param string $ajaxReload
     * @param string $ajaxLoad
     * @param string $cssClasses
     * @return string 
     */
    protected function saveBlockConfigsAction($typeProgress, $idBlock, $maxAge, $tag, $allowedModules, $ajaxReload, $ajaxLoad, $cssClasses) {
	$block = $this->search_block($this->$typeProgress, $idBlock);
	$block->setConfig('maxAge', $maxAge);
	$block->setConfig('tag', $tag);
	$block->setConfig('allowedModules', $allowedModules);
	$block->setConfig('ajaxReload', $ajaxReload);
	\app::$request->page = new \page(999);
        if(isset($_POST['getVars'])){
            parse_str($_POST['getVars'],$outVars);
            array_merge($_GET,$outVars);
            \app::$request->setParams($outVars);
            unset($_POST['getVars']);
        }
        if(isset($_POST['postVars'])){ 
            parse_str($_POST['postVars'],$outVars);
            array_merge($_POST,$outVars);
            \app::$request->setParams($outVars);
            unset($_POST['postVars']);
        }
        unset($_POST['TOKEN']);
	if ($ajaxLoad != 0)
	    $block->setConfig('ajaxLoad', '1');
	else
	    $block->setConfig('ajaxLoad', '0');
	$block->setConfig('css_classes', $cssClasses);
	if (method_exists($block, 'saveConfigs')) {
	    $block->saveConfigs();
	} else {
	    $rm = array('action', 'MODULE', 'THEME', 'THEMETYPE', 'THEMEMODULE', 'idBlock', 'parentBlock', 'typeProgress', 'maxAge', 'tag', 'ajaxReload', 'css_classes', 'allowedModules', 'save_configs');
	    $rm = array_flip($rm);
	    $configs = array_diff_key($_POST, $rm);
	    foreach ($configs AS $configName => $value) {
		$val = $_POST[$configName];
		$block->setConfig($configName, $val);
	    }
	}
	$this->saveAll();
	$return = array('eval' => $block->ajaxRefresh(),  'jsFiles' => json_encode(\app::$request->page->getJSFiles()), 'CSSFiles' => json_encode(\app::$request->page->getCSSFiles()), 'notification' => t('The Config has been saved', FALSE), 'notificationType' => 'positive');
	return $this->returnResult($return);
    }

    /**
     * Return results in json
     * @param string $results
     * @return string 
     */
    private function returnResult($results) {
	if (!isset($results['notificationType']))
	    $results['notificationType'] = 'normal';
	\app::$response->setHeader('X-XSS-Protection', '0');
	\app::$response->setHeader('Content-type', 'application/json');
	return json_encode($results);
    }

    /**
     * Remove block
     * @param string $typeProgress
     * @param string $parentBlock
     * @param string $idBlock
     * @return string
     */
    protected function removeBlockAction($typeProgress, $parentBlock, $idBlock) {
	$block = $this->search_block($this->$typeProgress, $parentBlock);
	$block->rmBlock($idBlock);
	$test = $this->search_block($this->$typeProgress, $idBlock);
	if ($test == NULL){
	    if ($typeProgress === 'theme')
		$css = new \css(PROFILE_PATH . THEMEMODULE . '/themes/' . THEME . '/' . THEMETYPE . '.css');
	    else
		$css = new \css(PROFILE_PATH . MODULE . '/style.css');
	    $css->deleteSelector('#' . $idBlock);
	    $css->save();
	    $this->saveAll();
	    $return = array('eval' => '$("#" + ParsimonyAdmin.inProgress,ParsimonyAdmin.currentWindow).remove();$("#changeres").trigger("change");', 'notification' => t('The block has been deleted', FALSE), 'notificationType' => 'positive');
	}else
	    $return = array('eval' => '', 'notification' => t('Container block cannot be deleted', FALSE), 'notificationType' => 'negative');
	return $this->returnResult($return);
    }

    /**
     * Save page : add or update
     * @param string $module
     * @param string $id_page
     * @param string $title
     * @param string $meta
     * @param array $URLcomponents
     * @param string $regex
     * @return string 
     */
    protected function savePageAction($module, $id_page, $title, $meta, $regex, array $URLcomponents = array()) {
	$moduleObj = \app::getModule($module);
	$page = $moduleObj->getPage($id_page); /* modif */
        $page->setModule($module);
	$page->setTitle($title);
	$page->setMetas($meta);
	if (isset($URLcomponents))
	    $page->setURLcomponents($URLcomponents);
	$page->setRegex('@' . $regex . '@');
	$moduleObj->updatePage($page); //modif
	if (\tools::serialize(PROFILE_PATH . $module . '/module', $moduleObj)) {
	    $return = array('eval' => 'ParsimonyAdmin.loadBlock(\'panelmodules\');', 'notification' => t('The page has been saved', FALSE), 'notificationType' => 'positive');
	    return $this->returnResult($return);
	}
    }
    
    /**
     * Reorder Pages of a module
     * @param string $module
     * @param array $order
     * @return boolean 
     */
    protected function reorderPagesAction($module, array $order = array()) {
	$module = \app::getModule($module);
	$newOrder = array();
	foreach ($order as $value) {
	    $id = substr($value,  strpos($value, '_') +1 );
	    $newOrder[] = $id; 
	}
	return $module->reoderPages($newOrder);
    }
    
    /**
     * Get the view to update the page
     * @param string $module
     * @param string $page
     * @return string|false
     */
    protected function getViewUpdatePageAction($module, $page) {
	$moduleObj = \app::getModule($module);
	if ($page == 'new') {
	    $lastPage = array_keys($moduleObj->getPages());
	    $idPage = max($lastPage) + 1;
	    $page = new \page($idPage);
            $page->setModule($module);
	    $page->setTitle('Page '.$idPage);
	    $page->setRegex('@page_'.$idPage.'@');
	    $page->save();
	    $moduleObj->addPage($page); //modif
	} else {
	    $page = $moduleObj->getPage($page);
	}
	$moduleObj->save();
        $module = $moduleObj;
	ob_start();
	include ('modules/admin/views/web/managePage.php');
	return ob_get_clean();
    }

    /**
     * Delete the Page
     * @param string $module
     * @param integer $id_page
     * @return string 
     */
    protected function deleteThisPageAction($module, $id_page) {
	$module = \app::getModule($module);
	$page = $module->getPage($id_page);
	$module->deletePage($page);
	$module->save();
        $url = '';
        if($module->getName() != 'core') $url = $module->getName().'/';
	$return = array('eval' => 'window.location = "' . BASE_PATH . $url . 'index";', 'notification' => t('The page has been deleted', FALSE), 'notificationType' => 'positive');
	return $this->returnResult($return);
    }

    /**
     * Get the rules of a css selector and return them in json
     * @param string $filePath
     * @param string $selector
     * @return string 
     */
    protected function getCSSSelectorRulesAction($filePath, $selector) {
        if(is_file(PROFILE_PATH. $filePath)) $filePath2 =  PROFILE_PATH. $filePath;
        else $filePath2 =  'modules/'. $filePath;
        $css = new \css($filePath2);
        $selectorText = str_replace("\t", '', trim($css->selectorExists($selector)));
        if (!$selectorText)
            $selectorText = '';
        $CSSJson = array('selector' => $selector,'filePath' => $filePath, 'code' => $selectorText, 'values' => $css->extractSelectorRules($selector));
        return json_encode($CSSJson);
    }
    
    /**
     * Get the selectors of a css file and return them in json
     * @param string $term
     * @param string $filePath
     * @return string 
     */
    protected function getCSSSelectorsAction($term,$filePath) {
        if(is_file(PROFILE_PATH. $filePath)) $filePath =  PROFILE_PATH. $filePath;
        else $filePath =  'modules/'. $filePath;
        $css = new \css($filePath);
        $selectors = array();
        foreach($css->getAllSselectors() AS $selector){
            if(strstr($selector, $term)) $selectors[] = $selector;
        }
        return json_encode($selectors);
    }

    /**
     * Save CSS
     * @param string $filePath
     * @param string $selector
     * @return string 
     */
    protected function saveCSSAction($filePath, $selector,$typeofinput = 'form',$changecsscodetextarea = '',$save = TRUE) {
        if(!is_file(PROFILE_PATH. $filePath) && is_file('modules/'. $filePath)) \tools::file_put_contents(PROFILE_PATH. $filePath,file_get_contents('modules/'. $filePath));
        if(is_file(PROFILE_PATH. $filePath)) $filePath2 =  PROFILE_PATH. $filePath;
        else $filePath2 =  'modules/'. $filePath;
	$css3 = array(
	    'box-shadow' => array('-moz-box-shadow', '-webkit-box-shadow'),
	    'border-radius' => array('-moz-border-radius', '-webkit-border-radius'),
	    'border-image' => array('-moz-border-image', '-webkit-border-image'),
	    'transform' => array('-webkit-transform', '-moz-transform', '-ms-transform', '-o-transform'),
	    'transition' => array('-webkit-transition', '-moz-transition', '-ms-transition', '-o-transition'),
	    'text-shadow' => array(),
	    'background-size' => array('-moz-background-size', '-webkit-background-size'),
	    'column-count' => array('-moz-column-count', '-webkit-column-count'),
	    'column-gap' => array('-moz-column-gap', '-webkit-column-gap'),
	    'background-clip' => array('-moz-background-clip', '-webkit-background-clip'),
	    'background-origin' => array('-webkit-background-origin'),
	    'transform-origin' => array('-ms-transform-origin', '-webkit-transform-origin', '-moz-transform-origin', '-o-transform-origin'),
	    'transform-style' => array('-webkit-transform-style'),
	    'perspective' => array('-webkit-perspective'),
	    'perspective-origin' => array('-webkit-perspective-origin'),
	    'backface-visibility' => array('-webkit-backface-visibility'),
	    'transition-property' => array('-moz-transition-property', '-webkit-transition-property', '-o-transition-property'),
	    'transition-duration' => array('-moz-transition-duration', '-webkit-transition-duration', '-o-transition-duration'));
	$css = new \css($filePath2);
	unset($_POST['current_selector_update']);
	unset($_POST['action']);
	unset($_POST['filePath']);
	unset($_POST['selector']);
	unset($_POST['typeofinput']);
        unset($_POST['save']);
	if (!$css->selectorExists($selector)) {
	    unset($_POST['action']);
	    $css->addSelector($selector);
	}
	if ($typeofinput == 'form') {
	    unset($_POST['changecsscodetextarea']);
	    foreach ($_POST AS $key => $value) {
		$value = trim($value);
		if ($value != '') {
		    if (!$css->propertyExists($selector, $key)) {
			if (isset($css3[$key])) {
			    foreach ($css3[$key] as $property) {
				$css->addProperty($selector, $property, $value);
			    }
			}
			$css->addProperty($selector, $key, $value);
		    } else {
			if (isset($css3[$key])) {
			    foreach ($css3[$key] as $property) {
				$css->updateProperty($selector, $property, $value);
			    }
			}
			$css->updateProperty($selector, $key, $value);
		    }
		} else {
		    if (isset($css3[$key])) {
			foreach ($css3[$key] as $property) {
			    $css->deleteProperty($selector, $property);
			}
		    }
		    $css->deleteProperty($selector, $key);
		}
	    }
	    if (!$css->propertyExists($selector, 'behavior')) {
		$css->addProperty($selector, 'behavior', 'url(/lib/csspie/PIE.htc)');
	    }
	} elseif ($typeofinput == 'code') {
	    $css->replaceSelector($selector, $_POST['changecsscodetextarea']);
	}
	if (!$css->propertyExists($selector, 'behavior')) {
	    $css->addProperty($selector, 'behavior', 'url(/lib/csspie/PIE.htc)');
        }
        $selectorText = str_replace("\t", '', trim($css->selectorExists($selector)));
        if (!$selectorText)
            $selectorText = '';
        $CSSJson = array('selector' => $selector, 'filePath' => $filePath, 'code' => $selectorText, 'values' => $css->extractSelectorRules($selector));
        if((bool)$save){ 
            $css->save();
            $return = array('eval' => '','css' => $CSSJson, 'notification' => t('The style sheet has been saved', FALSE), 'notificationType' => 'positive');
        }else{
            $return = array('eval' => '','css' => $CSSJson);
        }
        return $this->returnResult($return);
    }

    /**
     * Move Block from a container to another
     * @param string $start_typecont
     * @param string $idBlock
     * @param string $popBlock
     * @param string $startParentBlock
     * @param string $id_next_block
     * @param string $stop_typecont
     * @param string $parentBlock
     * @return string 
     */
    protected function moveBlockAction($start_typecont, $idBlock, $popBlock, $startParentBlock, $id_next_block, $stop_typecont, $parentBlock) {
	//depart
	if (empty($start_typecont)) {
	    $temp = substr($popBlock, 0, -10);
	    $newblock = new $temp($idBlock);
	} else {
	    $block = $this->search_block($this->$start_typecont, $idBlock);
	    $blockparent = $this->search_block($this->$start_typecont, $startParentBlock);
	    $blockparent->rmBlock($idBlock);
	    $this->saveAll();
	    $newblock = $block;
	}
	//arrivée
	if ($id_next_block === '' || $id_next_block === 'undefined')
	    $id_next_block = FALSE;
	$block2 = $this->search_block($this->$stop_typecont, $parentBlock);
	$block2->addBlock($newblock, $id_next_block);
	$this->saveAll();
	if ($this->search_block($this->$stop_typecont, $idBlock) != NULL)
	    $return = array('eval' => 'ParsimonyAdmin.moveMyBlock("' . $idBlock . '","dropInPage");', 'notification' => t('The move has been saved', FALSE), 'notificationType' => 'positive');
	else
	    $return = array('eval' => '', 'notification' => t('Error on drop', FALSE), 'notificationType' => 'negative');
	return $this->returnResult($return);
    }

    /**
     * Get the view of the configuration block
     * @param string $typeProgress
     * @param string $idBlock
     * @param string $parentBlock
     * @return string|false
     */
    protected function getViewConfigBlockAction($typeProgress, $idBlock, $parentBlock) {
	$block = $this->search_block($this->$typeProgress, $idBlock);
	ob_start();
	require('modules/admin/views/web/manageBlock.php');
	return ob_get_clean();
    }

    /**
     * Get the view of the theme form
     * @return string|false 
     */
    protected function getViewConfigThemesAction() {
	return $this->getView('manageThemes','web');
    }

    /**
     * Get the view of the translation form
     * @return string|false 
     */
    protected function getViewTranslationAction() {
	return $this->getView('manageTranslation','web');
    }

    /**
     * Get the adding view of the module
     * @return string|false 
     */
    protected function getViewAddModuleAction() {
	return $this->getView('addModule','web');
    }

    /**
     * Get the adding view of the block
     * @return string|false 
     */
    protected function getViewAddBlockAction() {
	return $this->getView('addBlock','web');
    }

    /**
     * Save translation
     * @param string $key
     * @param string $val
     * @return string 
     */
    protected function saveTranslationAction($key, $val) {
	$locale = \app::$request->getLocale();
	\unlink('cache/' . $locale . '-lang.php');
	if (isset($_COOKIE['locale']))
	    $locale = $_COOKIE['locale'];
	else
	    $locale = \app::$config['localization']['default_language'];
	$path = 'modules/' . MODULE . '/locale/' . $locale . '.php';
	if (file_exists($path))
	    include($path);
	$lang[$key] = $val;
	$config = new \config($path, TRUE);
	$config->setVariable('lang');
	$config->saveConfig($lang);
	$return = array('eval' => '$(\'span[data-key="' . $key . '"]\',ParsimonyAdmin.currentWindow).html("' . $val . '")', 'notification' => t('The translation has been saved', FALSE), 'notificationType' => 'positive');
	return $this->returnResult($return);
    }

    /**
     * Cross the nodes from a given node
     * @param string $node
     * @return string 
     */
    private function domToArray($node) {
	if ($node->children()) {
	    $tts = array();
	    // echo '<ul>';
	    foreach ($node->children() as $kid) {
		if ($kid->tag == 'div' && !empty($kid->id)) {
		    //echo '<li>' . $kid->id . '</li>';
		    $tts[$kid->id] = $kid->id;
		    if ($kid->children()) {
			$res = $this->domToArray($kid);
			if (empty($res))
			    $tts[$kid->id] = $kid->innertext;
			else
			    $tts[$kid->id] = $res;
		    }
		}
	    }
	    //echo '</ul>';
	    return $tts;
	}
    }

    /**
     * Dump an array into a container theme
     * @param string $node
     * @param string $id by default container
     * @return string 
     */
    private function arrayToBlocks($node, $id = 'container') {
	if($id=='content') $block = new \core\blocks\page($id);
	else $block = new \core\blocks\container($id);
	if (is_array($node)) {
	    foreach ($node as $id => $ssnode) {
		$b = $this->arrayToBlocks($ssnode, $id);
		$block->addBlock($b);
	    }
	} else {
	    $b = new \core\blocks\wysiwyg($id . '_html');
	    //$b->setConfig('text', $node);
	    $block->addBlock($b);
	}
	return $block;
    }
    
    /**
     * Get view of dbDesigner
     * @return string 
     */
    protected function dbDesignerAction() {
	return $this->getView('dbDesigner','web');
    }
    
    /**
     * Get view of the file explorer
     * @return string 
     */
    protected function explorerAction() {
	return $this->getView('explorer','web');
    }
    
    /**
     * Get view of the files for explorer
     * @return string 
     */
    protected function filesAction($dirPath) {
	return $this->getView('files','web');
    }

    /**
     * Add a theme to the site
     * @param string $thememodule
     * @param string $name
     * @param string $template
     * @param string $patterntype
     * @param string $url
     * @return false 
     */
    protected function addThemeAction($thememodule, $name, $patterntype, $template, $url) {
	if (!is_dir(PROFILE_PATH . $thememodule . '/themes/' . $name)) {
	    mkdir(PROFILE_PATH . $thememodule . '/themes/' . $name, 0777);
	    if ($patterntype == 'url' && !empty($url)) {
		include('lib/simplehtmldom/simple_html_dom.php');
		$str = file_get_contents($url);
		substr($url, -1) == '/' ? $baseurl = dirname($url . 'index') : $baseurl = dirname($url);
		$str = \tools::absolute_url($str, $baseurl);
		$html = str_get_html($str);
		preg_match_all('/<.*href="(.*\.css).*[^"]/i', $str, $out);
		$allCSS = '';
		foreach ($out[1] as $css) {
		    $allCSS .= file_get_contents($css);
		}
		file_put_contents(PROFILE_PATH . $thememodule . '/themes/' . $name . '/web.css', utf8_encode($allCSS));
		$body = $html->find('body');
		$tree = $this->domToArray($body[0]);
		$structure1 = $this->arrayToBlocks($tree);
		$theme = new \theme('container');
		$theme->setName($name);
		$theme->setThemeType('web');
		$theme->setModule($thememodule);
		$theme->setBlocks($structure1->getBlocks());
		$theme->save();
	    } else if ($patterntype == 'template' && !empty($template)) {
		$themeweb = \theme::get($thememodule, $template, 'web');
		$themeweb->setName($name);
		$thememobile = \theme::get($thememodule, $template, 'thememobile');
		$thememobile->setName($name);
		\tools::copy_dir(PROFILE_PATH . $thememodule . '/themes/' . $template . '/', PROFILE_PATH . $thememodule . '/themes/' . $name . '/');
		$themeweb->save('web');
		$thememobile->save('thememobile');
	    } else {
		$theme = new \theme('container');
		$theme->setName($name);
                $theme->setThemeType('web');
                $theme->setModule($thememodule);
		$theme->save();
	    }
	    $this->changeThemeAction($thememodule, $name);
	    $return = array('eval' => 'top.window.location.reload()', 'notification' => t('The Theme has been created', FALSE), 'notificationType' => 'positive');
	} else {
	    $return = array('eval' => '', 'notification' => t('The Theme has not been created, theme already exists', FALSE), 'notificationType' => 'negative');
	}
	return $this->returnResult($return);
    }

    /**
     * Change the theme of the site
     * @param string $THEMEMODULE
     * @param string $name
     * @return string 
     */
    protected function changeThemeAction($THEMEMODULE, $name) {
	if (isset($_COOKIE['THEME']))
	    setcookie('THEME', '', time() - 99000, '/');
	if (is_dir(PROFILE_PATH . $THEMEMODULE . '/themes/' . $name)) {
	    $configs = file_get_contents('config.php');
	    $configs = preg_replace('@\$config\[\'THEME\'\] = \'(.*)\';@Ui', "\$config['THEME'] = '" . $name . "';", $configs);
	    file_put_contents('config.php', $configs);
	    $return = array('eval' => 'document.getElementById("parsiframe").contentWindow.location.reload()', 'notification' => t('The Theme has been changed', FALSE), 'notificationType' => 'positive');
	} else {
	    $return = array('eval' => '', 'notification' => t('The Theme has been changed', FALSE), 'notificationType' => 'negative');
	}
	return $this->returnResult($return);
    }

    /**
     * Delete the theme
     * @param string $THEMEMODULE
     * @param string $name
     * @return string 
     */
    protected function deleteThemeAction($THEMEMODULE, $name) {
	if (is_dir(PROFILE_PATH . $THEMEMODULE . '/themes/' . $name)) {
	    \tools::rmdir(PROFILE_PATH . $THEMEMODULE . '/themes/' . $name);
	}
	$return = array('eval' => "$('#theme_".$name."').remove()", 'notification' => t('The Theme has been deleted', FALSE), 'notificationType' => 'positive');
	return $this->returnResult($return);
    }

    /**
     * Add a module
     * @param string $name_module
     * @param string $name_titre
     * @return string 
     */
    protected function addModuleAction($name_module, $name_titre) {
	if (\module::build($name_module, $name_titre)) {
	    $return = array('eval' => 'top.window.location.href = "' . BASE_PATH . $name_module . '/index"', 'notification' => t('The Module has been created', FALSE), 'notificationType' => 'positive');
	} else {
	    $return = array('eval' => '', 'notification' => t('Module already exists, please choose another name', FALSE), 'notificationType' => 'negative');
	}
	return $this->returnResult($return);
    }

    /**
     * Get the the view of user profile
     * @return string 
     */
    protected function getViewUserProfileAction() {
	return $this->getView('userProfile','web');
    }

    /**
     * Change Locale language
     * @param string $locale
     */
    protected function changeLocaleAction($locale) {
	$config = new \config('config.php', TRUE);
	$config->saveConfig(array('localization' => array('default_language' => $locale)));
    }

    /**
     * Display the administration of a given module
     * @param string $module
     * @return string 
     */
    protected function getViewModuleAdminAction($module) {
	return \app::getModule($module)->displayAdmin();
    }

    /**
     * Save configuration in the file
     * @param string $file
     * @param string $config
     * @return string 
     */
    protected function saveConfigAction($file, $config) {
	\unlink('cache/' . \app::$request->getLocale() . '-lang.php');
	$configObj = new \config($file, TRUE);
	$configObj->saveConfig($config);
	$return = array('eval' => '', 'notification' => t('The Config has been saved', FALSE), 'notificationType' => 'positive');
	return $this->returnResult($return);
    }

    /**
     * Search data in db of a given model 
     * @param string $module
     * @param string $entity
     * @param string $search
     * @return string|false
     */
    protected function searchDataAction($module, $entity, $search, $limit = 10) {
            $obj = \app::getModule($module)->getEntity($entity);
	$sql = '';
	foreach ($obj->getFields() as $field) {
            if(get_class($field) != \app::$aliasClasses['field_formasso'])
	    $sql .= ' ' . $field->name . ' like \'%' . addslashes($search) . '%\' OR';
	}
	$obj = $obj->where(substr($sql, 0, -3))->limit($limit);
	ob_start();
	require('modules/admin/views/web/datagrid.php');
	return ob_get_clean();
    }

    /**
     * Display the datagrid
     * @param string $module
     * @param string $entity
     * @param string $page
     * @return string|false
     */
    protected function datagridAction($module, $entity, $page, $limit = 10) {
	$obj = \app::getModule($module)->getEntity($entity)->limit((($page - 1) * $limit) . ','.$limit);
	ob_start();
	require('modules/admin/views/web/datagrid.php');
	return ob_get_clean();
    }
    
    public function structureTree($obj) {
        $idPage = '';
        if($obj->getId() == 'content') $idPage = ' data-page="'.\app::$request->page->getId().'"';
	$html = '<ul class="tree_selector container parsicontainer" style="clear:both" id="treedom_' . $obj->getId() . '"'.$idPage.'><span class="arrow_tree"></span>' . $obj->getId();
	if ($obj->getId() == 'content'){
	    $obj = \app::$request->page;
	}
	foreach ($obj->getBlocks() AS $block) {
	    if (get_class($block) == 'core\blocks\container' || $block->getId() == 'content')
		$html .= $this->structureTree($block);
	    else
		$html .= '<li class="tree_selector parsiblock" id="treedom_' . $block->getId() . '"> ' .$block->getId() . '</li>';
	};
	$html .= '</ul>';
	return $html;
    }

    /**
     * Display the datagrid preview
     * @param string $properties
     * @param string $relations
     * @param string $pagination
     * @param string $nbitem
     * @return string|false
     */
    protected function datagridPreviewAction(array $properties = array(), array $relations = array(), $pagination = false, $nbitem = 5) {
	$maview = new \view();
	if (!empty($properties)) {
	    if (isset($relations))
		$maview = $maview->initFromArray($properties, $relations);
	    else
		$maview = $maview->initFromArray($properties);
	    if ($pagination)
		$maview->limit($nbitem);
	    else 
		$maview->limit(10);
	} else {
	    return t('No data for this query.', FALSE);
	}
	$obj = $maview;
	ob_start();
	require('modules/admin/views/web/datagrid.php');
	return ob_get_clean();
    }

    /**
     * Get the view Update Form of a given id
     * @param string $module
     * @param string $entity
     * @param string $id
     * @return string 
     */
    protected function getViewUpdateFormAction($module, $entity, $id) {
	$obj = \app::getModule($module)->getEntity($entity);
	return $obj->where($obj->getId()->name. '=' . $id)->getViewUpdateForm(TRUE);
    }

    /**
     * Get the admin view of a given model
     * @param string $model
     * @return string|false
     */
    protected function getViewAdminModelAction($model) {
	list($module, $model) = explode(' - ', $model);
	$obj = \app::getModule($module)->getEntity($model)->limit('10');
	ob_start();
	require('modules/admin/views/web/manageModel.php');
	return ob_get_clean();
    }

    /**
     * Add a new entry in a table
     * @param string $entity
     * @return string 
     */
    protected function addNewEntryAction($entity) {
	unset($_POST['action']);
	unset($_POST['add']);
	unset($_POST['TOKEN']);
	list($module, $entity) = explode(' - ', $entity);
	$obj = \app::getModule($module)->getEntity($entity);
	unset($_POST['entity']);
	$obj->insertInto($_POST);
	$return = array('eval' => '$(\'a[rel="' . $module . ' - ' . $entity . '"]\').trigger("click")', 'notification' => t('The data have been added', FALSE), 'notificationType' => 'positive');
	return $this->returnResult($return);
    }

    /**
     * Update an entry in a table
     * @param string $entity
     * @return string 
     */
    protected function updateEntryAction($entity) {
	unset($_POST['action']);
	unset($_POST['TOKEN']);
	list($module, $entity) = explode(' - ', $entity);
	$obj = \app::getModule($module)->getEntity($entity);
	unset($_POST['entity']);
	if (isset($_POST['update'])) {
	    unset($_POST['update']);
	    $obj->update($_POST);
	} elseif (isset($_POST['delete'])) {
	    unset($_POST['delete']);
	    $obj->where($obj->getId()->name.' = '.$_POST[$obj->getId()->name])->delete();
	}
	$return = array('eval' => '$(\'a[rel="' . $module . ' - ' . $entity . '"]\').trigger("click")', 'notification' => t('The data have been modified', FALSE), 'notificationType' => 'positive');
	return $this->returnResult($return);
    }

    /**
     * Get the view of rights
     * @return string|false
     */
    protected function getViewAdminRightsAction() {
	return $this->getView('manageRights','web');
    }
    
    /**
     * Get the view in order to change current language
     * @return string|false
     */
    protected function getViewAdminLanguageAction() {
	return $this->getView('manageLanguage','web');
    }

    /**
     * Get the preview of the adding form
     * @param string $module
     * @param string $model
     * @return string 
     */
    protected function getPreviewAddFormAction($module, $model) {
	return \app::getModule($module)->getEntity($model)->getViewAddForm();
    }

    /**
     * Upload file
     * @param string $path
     * @return string 
     */
    protected function uploadAction($path, $size = 999999, $allowedExt = 'jpg|png|gif') {
	$upload = new \core\classes\upload($size, $allowedExt, $path . '/');
	$result = $upload->upload($_FILES['fileField']);
	if($result !== FALSE){
	$arr = $_FILES['fileField'];
	$arr['name'] = $result;
	$params = @getimagesize($path.'/'.$result);
	list($width, $height, $type, $attr) = $params;
	if($params){
	    $arr['x'] = $width;
	    $arr['y'] = $height;
	    $arr['type'] = $type;
	}
	\app::$response->setHeader('Content-type', 'application/json');
	return json_encode($arr);
	}else
	    return FALSE;
    }
    
     /**
     * Get the rules of a css selector and return them in json
     * @param string $filePath
     * @param string $selector
     * @return string 
     */
    protected function loadBlockAction($blockName) {
        ob_start();
        include('admin/blocks/'.$blockName.'/view.php');
        $html = ob_get_clean();
        return $html;
    }

    /**
     * Save rights
     * @param string $modulerights
     * @param string $pagesrights
     * @return string 
     */
    protected function saveRightsAction($modelsrights, $modulerights, $pagesrights) {
	if (is_array($modulerights)) {
	    foreach ($modulerights as $numRole => $role) {
		foreach ($role as $moduleName => $value) {
		    $module = \app::getModule($moduleName);
		    if ($value == 'on')
			$module->updateRights($numRole, 1);
		    else
			$module->updateRights($numRole, 0);
		    $module->save();
		}
	    }
	}
	if (is_array($modelsrights)) {
	    foreach ($modelsrights as $numRole => $role) {
		foreach ($role as $moduleName => $modules) {
		    foreach ($modules as $entityName => $entities) {
			$nb = 0;
			$model = \app::getModule($moduleName)->getEntity($entityName);
			foreach ($entities as $right => $value) {
			    if ($right == 'display' && $value == 'on')
				$nb += 1;
			    if ($right == 'insert' && $value == 'on')
				$nb += 2;
			    if ($right == 'update' && $value == 'on')
				$nb += 4;
			    if ($right == 'delete' && $value == 'on')
				$nb += 8;
			}
			$model->updateRights($numRole, $nb);
			$model->save();
		    }
		}
	    }
	}
	if (is_array($pagesrights)) {
	    foreach ($pagesrights as $numRole => $role) {
		foreach ($role as $moduleName => $modules) {
		    foreach ($modules as $pageId => $pages) {
			$nb = 0;
			$mod = \app::getModule($moduleName);
			$page = $mod->getPage($pageId);
			foreach ($pages as $right => $value) {
			    if ($right == 'display' && $value == 'on')
				$nb += 1;
			}
			$page->updateRights($numRole, $nb);
			$page->save();
		    }
		}
	    }
	}
	$return = array('eval' => '', 'notification' => t('The Permissions have been saved', FALSE), 'notificationType' => 'positive');
	return $this->returnResult($return);
    }
    
    /**
     * Save model
     * @return string
     */
    protected function saveModelAction($module,$list) {
	$schema = json_decode($list);
	$tableExists = array();
	if (is_array($schema)) {
	    foreach ($schema as $table) {
		
		if ($table->name != $table->oldName) include_once('modules/' . $module . '/model/' . $table->oldName . '.php');

		$tplProp = '';
		$tplParam = '';
		$tplAssign = '';
		$args = array();
		$matchOldNewNames = array();

		foreach ($table->properties as $fieldName => $property) {
		    list($name, $type) = explode(':', $fieldName);
		    $tplProp .= '    protected $' . $name . ";\n\r"; //genere les atributs
		    $tplParam .= '\\' . $type . ' $' . $name . ','; //génère les paramètres du constructeur
		    $tplAssign .= '        $this->' . $name . ' = $' . $name . ";\n"; //génère les affectations dans le constructeur
		    $reflectionObj = new \ReflectionClass($type);
		    $property = json_encode($property);
		    $property = json_decode($property, true);
                    
		    $args[] = $reflectionObj->newInstanceArgs($property);
		    if(isset($property['oldName']) && ($property['oldName'] != $name && !empty($property['oldName']))) $matchOldNewNames[$name] = $property['oldName'];
		}
		$tpl = 
'<?php
namespace ' . $module . '\model;
/**
* Description of entity ' . $table->name . '
* @author Parsimony
* @top ' . $table->top . '
* @left ' . $table->left . '
*/
class ' . $table->name . ' extends \entity {

' . $tplProp . '

public function __construct(' . substr($tplParam, 0, -1) . ') {
' . $tplAssign . '
}
// DON\'T TOUCH THE CODE ABOVE ##########################################################
';

		$model = 'modules/' . $module . '/model/' . $table->name . '.php';
		if (!is_file($model)) {
		    $tpl .= '}'.PHP_EOL.'?>';
		} else {
		    $code = file_get_contents($model);
		    $tpl = preg_replace('@<\?php(.*)}(.*)?(ABOVE ##########################################################)?@Usi', $tpl, $code);
		}

		\tools::file_put_contents($model, $tpl);
		include_once($model);
		$oldFields = array();
		$oldObjModel = FALSE;
		if (is_file('modules/' . $module . '/model/' . $table->oldName . '.'.\app::$config['dev']['serialization'])) {
		    $oldObjModel = \tools::unserialize('modules/' . $module . '/model/' . $table->oldName);
		    $oldFields = $oldObjModel->getFields();
		}
		
		// Change table Name if has change
		if ($table->name != $table->oldName) {
		    \PDOconnection::getDB()->exec('ALTER TABLE ' . $module . '_' . $table->oldName . ' RENAME TO ' . $module . '_' . $table->name . ';');
		    unlink('modules/' . $module . '/model/' . $table->oldName . '.php');
		    unlink('modules/' . $module . '/model/' . $table->oldName .  '.' .\app::$config['dev']['serialization']);
		    //require_once('modules/' . $module . '/model/' . $table->name . '.php');
		}
		// make a reflection object
		$reflectionObj = new \ReflectionClass($module . '\\model\\' . $table->name);
		$newObj = $reflectionObj->newInstanceArgs($args);
		$newObj = unserialize(serialize($newObj)); // in order to call __wakeup method
		$newObj->behaviorTitle = $table->title;
		$newObj->behaviorDescription = $table->description;
		$newObj->behaviorKeywords = $table->keywords;
		$newObj->behaviorImage = $table->image;
		if ($oldObjModel != FALSE) {
		    $nameFieldBefore = '';
		    foreach ($args as $fieldName => $field) {
			if (isset($oldFields[$field->name])) {
			    $field->alterColumn($nameFieldBefore);
			} elseif (isset($matchOldNewNames[$field->name])) {
			    $field->alterColumn($nameFieldBefore,$matchOldNewNames[$field->name]);
			} else {
			    $field->addColumn($nameFieldBefore);
			}
			if(get_class($field) != \app::$aliasClasses['field_formasso']) $nameFieldBefore = $field->name;
		    }
		    foreach ($oldObjModel->getFields() as $fieldName => $field) {
			if (is_object($field) && (!property_exists($newObj, $fieldName) && !in_array($fieldName, $matchOldNewNames) ))
			    $field->deleteColumn();
		    }
		}else {
		    $newObj->createTable();
		}
		\tools::serialize('modules/' . $module . '/model/' . $table->name , $newObj);
		$tableExists[] = $table->name;
	    }
	}
	foreach (glob('modules/' . $module . '/model/*.php') as $filename) {
	    $modelName = substr(substr(strrchr($filename, "/"), 1), 0, -4);
	    if (!in_array($modelName, $tableExists)) {
		\app::getModule($module)->getEntity($modelName)->deleteTable();
	    }
	}
	return ' ';
    }

    /**
     * Save module page in putting data in module.obj
     */
    private function saveAll() {
	$this->theme->save();
	if (isset($this->page) && is_object($this->page) ) {
	    \tools::serialize(PROFILE_PATH . MODULE . '/pages/' . $this->page->getId(), $this->page);
	}
	\tools::serialize(PROFILE_PATH . MODULE . '/module', $this->module);
    }

    /**
     * Sanitize url
     * @param string $url
     * @return string 
     */
    protected function titleToUrlAction($url) {
	$url = \tools::sanitizeString($url);
	return $url;
    }

    /**
     * Get a back Up
     * @param string $replace
     * @param string $file
     * @return string 
     */
    protected function getBackUpAction($replace, $file) {
	$old = file_get_contents('backup/' . $file . '-' . $replace . '.bak');
	file_put_contents($file, $old);
	return $old;
    }

    /**
     * Build a new block in a given module
     * @param string $choosenmodule
     * @param string $name_block
     * @return string 
     */
    protected function buildNewBlockAction($choosenmodule, $name_block) {
	if (isset($name_block) && isset($choosenmodule) && !empty($choosenmodule)) {
	    if (!empty($name_block)) {
		if (!file_exists('modules/' . $choosenmodule . '/blocks/' . $name_block)) {
		    \block::build($choosenmodule, $name_block);
		} else {
		    $return = array('eval' => '', 'notification' => t('The Block name already exists', FALSE), 'notificationType' => 'negative');
		}
	    } else {
		$return = array('eval' => '', 'notification' => t('The Block name is required', FALSE), 'notificationType' => 'negative');
	    }
	    //$message = t('The Block name is required', FALSE);
	    $return = array('eval' => 'ParsimonyAdmin.displayConfBox("' . BASE_PATH . 'admin/action","Block","choosenmodule=' . $choosenmodule . '&name_block=' . $name_block . '&action=displayModifyBlock");', 'notification' => 'The Block name is saved', 'notificationType' => 'positive');
	}
	return $this->returnResult($return);
    }

    /**
     * Display the view to modify block
     * @return string|false
     *//*
      private function displayModifyBlock() {
      ob_start();
      require('modules/admin/views/web/modifyBlock.php');
      return ob_get_clean();
      } */

    /**
     * Wrap result of an action in an instance of Page in order to display it in a popup
     * @return string 
     */
    protected function actionAction() {
	if (isset($_POST['action'])) {
	    $content = $this->controllerPOST($_POST['action']);
	    if (isset($_POST['popup']) && $_POST['popup'] == 'yes') {
		ob_start();
		require('modules/admin/views/web/popup.php');
		return ob_get_clean();
	    } else {
		return $content;
	    }
	}
    }

}

?>