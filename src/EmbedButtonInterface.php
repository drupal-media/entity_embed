<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EmbedButtonInterface.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining embed button entity.
 */
interface EmbedButtonInterface extends ConfigEntityInterface {

  // public function setButtonConfig($instance_id, array $configuration);

  public function getEntityTypes();

  public function getButtonLabel();

}

