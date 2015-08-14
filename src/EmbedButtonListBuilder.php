<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EmbedButtonListBuilder.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Component\Utility\SafeMarkup;

/**
 * Provides a listing of EmbedButton.
 */
class EmbedButtonListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];
    $header['label'] = $this->t('Embed button');
    $header['entity_type'] = $this->t('Entity Type');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [];
    /** @var \Drupal\entity_embed\EmbedButtonInterface $entity */
    $row['label'] = SafeMarkup::checkPlain($entity->label());
    $row['entity_type'] = SafeMarkup::checkPlain($entity->getEntityTypeLabel());
    return $row + parent::buildRow($entity);
  }

}
