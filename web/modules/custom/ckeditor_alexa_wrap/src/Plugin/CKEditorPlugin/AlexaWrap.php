<?php
namespace Drupal\ckeditor_alexa_wrap\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginInterface;
use Drupal\ckeditor\CKEditorPluginButtonsInterface;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "AlexaWrap" plugin.
 *
 * @CKEditorPlugin(
 *   id = "alexawrap",
 *   label = @Translation("Alexa Wrap"),
 *   module = "ckeditor"
 * )
 */
class AlexaWrap extends CKEditorPluginBase {


  public function getFile() {
    return drupal_get_path('module', 'ckeditor_alexa_wrap') . '/js/plugins/alexa_wrap/plugin.js';
  }

	public function getConfig(Editor $editor) {
    return array(
//      'alexaWrap_AlexaWrap' => $this->t('Add Alexa Wrapper'),
    );
  }

  public function getButtons() {
    return array(
      'AlexaWrap' => array(
        'label' => $this->t('Add Alexa Response'),
        'image' => drupal_get_path('module', 'ckeditor_alexa_wrap') . '/js/plugins/alexa_wrap/icons/alexa.png',
      ),
    );
  }

  /**
    * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getDependencies().
    * Returns a list of plugins this plugin requires.
    */
  public function getDependencies(Editor $editor) {
    return [];
  }

  /**
    * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getLibraries().
    * Returns a list of libraries this plugin requires.
    */
  public function getLibraries(Editor $editor) {
    return [];
  }

  /**
    * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::isInternal().
    * Indicates if this plugin is part of the optimized CKEditor build.
    */
  public function isInternal() {
    return FALSE;
  }
}