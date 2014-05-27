<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Form\EntityEmbedCkeditorForm
 */

namespace Drupal\entity_embed\Form;

use Drupal\Core\Form\FormBase;

/**
 * Provides a form to embed entities by specifying data attributes.
 */
class EntityEmbedCkeditorForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_embed_ckedtor_form';
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
      '#placeholder' => 'Enter ID/UUID of the entity'
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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
  }

}
