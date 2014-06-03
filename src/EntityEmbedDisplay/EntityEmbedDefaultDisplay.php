<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDefaultDisplay.
 */

namespace Drupal\entity_embed\EntityEmbedDisplay;

/**
 * Default embed display, which renders the entity using entity_view().
 *
 * @EntityEmbedDisplay(
 *   id = "default",
 *   label = @Translation("Default")
 * )
 *
 * @todo Should this use a derivative? http://cgit.drupalcode.org/page_manager/tree/src/Plugin/Deriver/EntityViewDeriver.php
 */
class EntityEmbedDefaultDisplay extends EntityEmbedDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'view_mode' => 'embed',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['view_mode'] = array(
      '#type' => 'select',
      '#title' => t('View mode'),
      '#options' => \Drupal::entityManager()->getDisplayModeOptions('view_mode', $this->getAttributeValue('entity-type')),
      '#default_value' => $this->getConfigurationValue('view_mode'),
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Clone the entity since we're going to set some additional properties we
    // don't want kept around afterwards.
    $entity = clone $this->entity;
    $entity->entity_embed_attributes = $this->getAttributes();

    // Build the rendered entity.
    $build = entity_view($this->entity, $this->getConfigurationValue('view_mode'), $this->getAttributeValue('langcode'));

    // Hide entity links by default.
    if (isset($build['links'])) {
      $build['links']['#access'] = FALSE;
    }

    return $build;
  }
}
