<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Form\EntityEmbedCKEditorSelectForm
 */

namespace Drupal\entity_embed\Form;

use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\entity_embed\Ajax\EntityEmbedSelectDialogSave;
use Drupal\entity_embed\EntityHelperTrait;

/**
 * Provides a form to embed entities by specifying data attributes.
 */
class EntityEmbedCKEditorSelectForm extends FormBase {
  use EntityHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_embed_ckeditor_select_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $form['#attached']['library'][] = 'entity_embed/entity_embed.ajax';

    $entity_type = null;
    $entity = null;
    // Get the existing values (if any).
    $existing_values = $form_state['input']['editor_object'];
    if (isset($existing_values['entity_type'])) {
      $entity_type = $existing_values['entity-type'];
    }
    if (isset($existing_values['entity'])) {
      $entity = $existing_values['entity'];
    }

    $form['entity_type'] = array(
      '#type' => 'select',
      '#name' => 'entity_type',
      '#title' => $this->t('Entity type'),
      '#options' => $this->entityManager()->getEntityTypeLabels(TRUE),
      '#required' => TRUE,
    );
    $form['entity'] = array(
      '#type' => 'textfield',
      '#name' => 'entity',
      '#title' => 'Entity',
      '#maxlength' => 128,
      '#title' => $this->t('Entity'),
      '#required' => TRUE,
      '#placeholder' => $this->t('Enter ID/UUID of the entity'),
    );
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['save_modal'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => array(),
      '#ajax' => array(
        'callback' => array($this, 'submitForm'),
        'event' => 'click',
      ),
    );

    // Set default values if existing values were set.
    if ($entity_type) {
      $form['entity_type']['#default_value'] = $entity_type;
    }
    if ($entity) {
      $form['entity']['#default_value'] = $entity;
    }

    // Set editor instance as a hidden field.
    $editor_instance = $form_state['input']['editor_object']['editor-id'];
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
    $id = trim($form_state['values']['entity']);
    if ($entity = $this->loadEntity($entity_type, $id)) {
      // @todo Should probably be setting the embed_mode value here since we
      // can grab $entity->uuid() if needed.
    }
    else {
      $this->setFormError('entity', $form_state, $this->t('Unable to load @type entity @id.', array('@type' => $entity_type, '@id' => $id)));
    }
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
      // Detect if a valid UUID was specified. Set embed method based based on
      // whether or not it is a valid UUID.
      $values = $form_state['values'];
      $entity = $values['entity'];
      if (Uuid::isValid($entity)) {
        $values['embed_method'] = 'uuid';
      }
      else {
        $values['embed_method'] = 'id';
      }

      $response->addCommand(new EntityEmbedSelectDialogSave($values));
      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }

}
