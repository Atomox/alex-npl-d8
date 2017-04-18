/**
 * @file
 * Drupal Link plugin.
 *
 * @ignore
 */

(function ($, Drupal, drupalSettings, CKEDITOR) {

  'use strict';

  CKEDITOR.plugins.add('alexawrap', {
    icons: 'alexa',
    hidpi: true,

    init: function(editor) {
      editor.addCommand('alexawrap', {
        modes: {wysiwyg: 1},
        canUndo: true,
        exec : function(editor) {
          var selected_text = editor.getSelection().getSelectedText(); // Get Text
          var newElement = new CKEDITOR.dom.element("p");              // Make Paragraff
          newElement.setAttributes({style: 'alexa'})                 // Set Attributes
          newElement.setText(selected_text);                           // Set text to element
          editor.insertElement(newElement);                            // Add Element
        }
      });

      editor.ui.addButton( 'alexawrap', {
        label: 'Alexa Response',
        command: 'alexawrap',
        icon: this.path + 'icons/alexa.png'
      });

      // Save snapshot for undo support.
      editor.fire('saveSnapshot');
    }
  });
})(jQuery, Drupal, drupalSettings, CKEDITOR);