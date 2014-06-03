<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Form\EntityEmbedCKEditorEmbedForm
 */

namespace Drupal\entity_embed\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\entity_embed\Ajax\EntityEmbedSubmitDialogSave;

/**
 * Provides a form to embed entities by specifying data attributes.
 */
class EntityEmbedCKEditorEmbedForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_embed_ckeditor_embed_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $form['#attached']['library'][] = 'entity_embed/entity_embed.ajax';

    // Set the existing values from previous step as hidden fields.
    $existing_values = $form_state['input']['editor_object'];
    $form['embed_method'] = array(
      '#type' => 'hidden',
      '#name' => 'embed_method',
      '#value' => $existing_values['embed-method'],
    );
    $form['entity_type'] = array(
      '#type' => 'hidden',
      '#name' => 'entity_type',
      '#value' => $existing_values['entity-type'],
    );
    $form['entity'] = array(
      '#type' => 'hidden',
      '#name' => 'entity',
      '#value' => $existing_values['entity'],
    );

    // Genrate list of view modes for selected entity type.
    $view_modes = \Drupal::entityManager()->getViewModeOptions($existing_values['entity_type']);
    $form['view_mode'] = array(
      '#type' => 'select',
      '#name' => 'view_mode',
      '#title' => 'View Mode',
      '#options' => $view_modes,
      '#required' => TRUE,
    );
    $form['display_links'] = array(
      '#type' => 'checkbox',
      '#name' => 'display_links',
      '#title' => 'Display links',
    );
    $form['align'] = array(
      '#type' => 'select',
      '#name' => 'align',
      '#title' => 'Align',
      '#options' => array(
        'none' => 'None',
        'left' => 'Left',
        'center' => 'Center',
        'right' => 'Right',
      ),
    );
    $form['show_caption'] = array(
      '#type' => 'checkbox',
      '#name' => 'show_caption',
      '#title' => 'Show Caption',
    );
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['save_modal'] = array(
      '#type' => 'submit',
      '#value' => 'Save',
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => array(),
      '#ajax' => array(
        'callback' => array($this, 'submitForm'),
        'event' => 'click',
      ),
    );

    // Set editor instance as a hidden field.
    $editor_instance = $existing_values['editor-id'];
    $form['editor_instance'] = array(
      '#type' => 'hidden',
      '#name' => 'editor_instance',
      '#value' => $editor_instance,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $response = new AjaxResponse();

    // Display errors in form, if any.
    if (form_get_errors($form_state)) {
      unset($form['#prefix'], $form['#suffix']);
      $status_messages = array('#theme' => 'status_messages');
      $output = drupal_render($form);
      $output = '<div>' . drupal_render($status_messages) . $output . '</div>';
      $response->addCommand(new HtmlCommand('#entity-embed-ckeditor-embed-form', $output));
    }
    else {
      $response->addCommand(new EntityEmbedSubmitDialogSave($form_state['values']));
      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }

}
