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

/**
 * Provides a form to embed entities by specifying data attributes.
 */
class EntityEmbedCKEditorSelectForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_embed_ckeditor_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $form['#attached']['library'][] = 'entity_embed/entity_embed.ajax';

    $form['entity_type'] = array(
      '#type' => 'select',
      '#name' => 'entity_type',
      '#title' => $this->t('Entity type'),
      '#options' => \Drupal::entityManager()->getEntityTypeLabels(TRUE),
    );
    $form['entity'] = array(
      '#type' => 'textfield',
      '#name' => 'entity',
      '#title' => $this->t('Entity'),
      '#required' => TRUE,
      '#placeholder' => 'Enter ID/UUID of the entity',
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
  public function submitForm(array &$form, array &$form_state) {
    $response = new AjaxResponse();

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

    return $response;
  }

}
