<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Form\EntityEmbedDialog
 */

namespace Drupal\entity_embed\Form;

use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\editor\Ajax\EditorDialogSave;
use Drupal\entity_embed\Ajax\EntityEmbedSelectDialogSave;
use Drupal\entity_embed\EntityHelperTrait;

/**
 * Provides a form to embed entities by specifying data attributes.
 */
class EntityEmbedDialog extends FormBase {
  use EntityHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_embed_dialog';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    // The default values are set directly from \Drupal::request()->request,
    // provided by the editor plugin opening the dialog.
    if (!isset($form_state['entity_element'])) {
      $form_state['entity_element'] = isset($form_state['input']['editor_object']) ? $form_state['input']['editor_object'] : array();
    }
    $entity_element = $form_state['entity_element'];
    if (!empty($form_state['values']['attributes'])) {
      $entity_element += $form_state['values']['attributes'];
    }
    $entity_element += array(
      'data-entity-type' => NULL,
      'data-entity-uuid' => '',
      'data-entity-id' => '',
      'data-view-mode' => 'default',
    );

    if (!isset($form_state['step'])) {
      // If an entity has been selected, then always skip to the embed options.
      if (!empty($entity_element['data-entity-type']) && (!empty($entity_element['data-entity-uuid']) || !empty($entity_element['data-entity-id']))) {
        $form_state['step'] = 'embed';
      }
      else {
        $form_state['step'] = 'select';
      }
    }

    $form['#tree'] = TRUE;
    $form['#prefix'] = '<div id="entity-embed-dialog-form">';
    $form['#suffix'] = '</div>';
    $form['#attached']['library'][] = 'editor/drupal.editor.dialog';

    switch ($form_state['step']) {
      case 'select':
        $form['attributes']['data-entity-type'] = array(
          '#type' => 'select',
          '#title' => $this->t('Entity type'),
          '#default_value' => $entity_element['data-entity-type'],
          '#options' => $this->entityManager()->getEntityTypeLabels(TRUE),
          '#required' => TRUE,
        );
        $form['attributes']['data-entity-id'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Entity ID or UUID'),
          '#default_value' => $entity_element['data-entity-uuid'] ?: $entity_element['data-entity-id'],
          '#required' => TRUE,
        );
        $form['attributes']['data-entity-uuid'] = array(
          '#type' => 'value',
          '#title' => $entity_element['data-entity-uuid'],
        );
        $form['actions'] = array(
          '#type' => 'actions',
        );
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
        break;

      case 'embed':
        $entity = $this->loadEntity($entity_element['data-entity-type'], $entity_element['data-entity-uuid'] ?: $entity_element['data-entity-id']);
        $form['entity'] = array(
          '#type' => 'item',
          '#title' => $this->t('Selected entity'),
          '#markup' => $entity->label(),
        );
        $form['attributes']['data-entity-type'] = array(
          '#type' => 'value',
          '#value' => $entity_element['data-entity-type'],
        );
        $form['attributes']['data-entity-id'] = array(
          '#type' => 'value',
          '#value' => $entity_element['data-entity-id'],
        );
        $form['attributes']['data-entity-uuid'] = array(
          '#type' => 'value',
          '#value' => $entity_element['data-entity-uuid'],
        );
        $form['attributes']['data-view-mode'] = array(
          '#type' => 'select',
          '#name' => 'view_mode',
          '#title' => $this->t('View Mode'),
          '#options' => $this->entityManager()->getViewModeOptions($entity_element['data-entity-type']),
          '#default_value' => $entity_element['data-view-mode'],
          '#required' => TRUE,
        );
        // @todo Re-add caption and alignment attributes.
        $form['actions'] = array(
          '#type' => 'actions',
        );
        $form['actions']['save_modal'] = array(
          '#type' => 'submit',
          '#value' => $this->t('Embed'),
          // No regular submit-handler. This form only works via JavaScript.
          '#submit' => array(),
          '#ajax' => array(
            'callback' => array($this, 'submitForm'),
            'event' => 'click',
          ),
        );
        break;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
    switch ($form_state['step']) {
      case 'select':
        if ($entity_type = $form_state['values']['attributes']['data-entity-type']) {
          $id = trim($form_state['values']['attributes']['data-entity-id']);
          if ($entity = $this->loadEntity($entity_type, $id)) {
            if (!$this->accessEntity($entity, 'view')) {
              $this->setFormError('entity', $form_state, $this->t('Unable to access @type entity @id.', array('@type' => $entity_type, '@id' => $id)));
            }
            elseif ($uuid = $entity->uuid()) {
              \Drupal::formBuilder()->setValue($form['attributes']['data-entity-uuid'], $uuid, $form_state);
              \Drupal::formBuilder()->setValue($form['attributes']['data-entity-id'], '', $form_state);
            }
            else {
              \Drupal::formBuilder()->setValue($form['attributes']['data-entity-uuid'], '', $form_state);
              \Drupal::formBuilder()->setValue($form['attributes']['data-entity-id'], $entity->id(), $form_state);
            }
          }
          else {
            $this->setFormError('entity', $form_state, $this->t('Unable to load @type entity @id.', array('@type' => $entity_type, '@id' => $id)));
          }
        }
        break;
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
      $response->addCommand(new HtmlCommand('#entity-embed-dialog-form', $output));
    }
    else {
      switch ($form_state['step']) {
        case 'select':
          $form_state['rebuild'] = TRUE;
          $form_state['step'] = 'embed';
          $rebuild_form = \Drupal::formBuilder()->rebuildForm('entity_embed_dialog', $form_state, $form);
          unset($rebuild_form['#prefix'], $rebuild_form['#suffix']);
          $status_messages = array('#theme' => 'status_messages');
          $output = drupal_render($rebuild_form);
          $output = '<div>' . drupal_render($status_messages) . $output . '</div>';
          $response->addCommand(new HtmlCommand('#entity-embed-dialog-form', $output));
          break;

        case 'embed':
          // @todo Fix our JS plugin to work with the new attribute values.
          $response->addCommand(new EditorDialogSave($values));
          $response->addCommand(new CloseModalDialogCommand());
          break;
      }
    }

    return $response;
  }

}
