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
use Drupal\entity_embed\Ajax\EntityEmbedSubmitDialogGoBack;
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
    $view_modes = \Drupal::entityManager()->getViewModeOptions($existing_values['entity-type']);
    $form['view_mode'] = array(
      '#type' => 'select',
      '#name' => 'view_mode',
      '#title' => $this->t('View Mode'),
      '#options' => $view_modes,
      '#required' => TRUE,
    );
    $form['align'] = array(
      '#type' => 'select',
      '#name' => 'align',
      '#title' => $this->t('Align'),
      '#options' => array(
        'none' => $this->t('None'),
        'left' => $this->t('Left'),
        'center' => $this->t('Center'),
        'right' => $this->t('Right'),
      ),
    );
    $form['show_caption'] = array(
      '#type' => 'checkbox',
      '#name' => 'show_caption',
      '#title' => $this->t('Show Caption'),
    );
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['go_back'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => array(),
      '#ajax' => array(
        'callback' => array($this, 'goBack'),
        'event' => 'click',
      ),
    );
    $form['actions']['save_modal'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
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
  public function validateForm(array &$form, array &$form_state) {
    $entity_type = $form_state['values']['entity_type'];
    $entity = $form_state['values']['entity'];
    if (empty($entity_type) || empty($entity)) {
      $this->setFormError('', $form_state, $this->t('Required fields from previous step of the form are missing. Go back and try again.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $response = new AjaxResponse();

    // Display errors in form, if any.
    if (\Drupal::formBuilder()->getErrors($form_state)) {
      unset($form['#prefix'], $form['#suffix']);
      $status_messages = array('#theme' => 'status_messages');
      $output = drupal_render($form);
      $output = '<div>' . drupal_render($status_messages) . $output . '</div>';
      # Using drupal_html_class() to obtain hypen separated form id. Using
      # drupal_html_id() instead results in adding an unnecessary counter at the
      # end of the string.
      $form_id = '#' . drupal_html_class($form_state['values']['form_id']);
      $response->addCommand(new HtmlCommand($form_id, $output));
    }
    else {
      $response->addCommand(new EntityEmbedSubmitDialogSave($form_state['values']));
      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }

  /**
   * Form submission handler to go back to the previous step of the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   */
  public function goBack(array &$form, array &$form_state) {
    $response = new AjaxResponse();

    $response->addCommand(new EntityEmbedSubmitDialogGoBack($form_state['values']));
    $response->addCommand(new CloseModalDialogCommand());

    return $response;
  }

}
