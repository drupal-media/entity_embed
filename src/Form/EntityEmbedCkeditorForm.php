<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Form\EntityEmbedCkeditorForm
 */

namespace Drupal\entity_embed\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\entity_embed\Ajax\EntityEmbedDialogSave;

/**
 * Provides a form to embed entities by specifying data attributes.
 */
class EntityEmbedCkeditorForm extends FormBase {

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
    $form['entity_type'] = array(
      '#type' => 'select',
      '#name' => 'entity_type',
      '#title' => 'Entity type',
      '#options' => array(
        'node' => 'Node',
        'others' => 'Others',
      ),
    );
    $form['embed_method'] = array(
      '#type' => 'select',
      '#name' => 'embed_method',
      '#title' => 'Embed using',
      '#options' => array(
        'uuid' => 'UUID',
        'id' => 'ID',
      ),
    );
    $form['entity'] = array(
      '#type' => 'textfield',
      '#name' => 'entity',
      '#title' => 'Entity',
      '#placeholder' => 'Enter ID/UUID of the entity',
    );
    $form['view_mode'] = array(
      '#type' => 'select',
      '#name' => 'view_mode',
      '#title' => 'View Mode',
      '#options' => array(
        'teaser' => 'Teaser',
        'others' => 'Others',
      ),
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
    $form['actions'] =array('#type' => 'actions');
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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $response = new AjaxResponse();

    $response->addCommand(new EntityEmbedDialogSave($form_state['values']));
    $response->addCommand(new CloseModalDialogCommand());

    return $response;
  }

}
