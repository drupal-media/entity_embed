<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EmbedButtonListBuilder.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of EmbedButton.
 */
class EmbedButtonListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Embed button');
    $header['entity_type'] = $this->t('Entity Type');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['entity_type'] = $entity->getEntityTypeLabel();
    return $row + parent::buildRow($entity);
  }

}
