<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EmbedButtonInterface.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a embed button entity.
 */
interface EmbedButtonInterface extends ConfigEntityInterface {

  /**
   * Returns the entity type for which this button is enabled.
   *
   * @return string
   *   Machine name of the entity type.
   */
  public function getEntityTypeMachineName();

  /**
   * Returns the label of the entity type for which this button is enabled.
   *
   * @return string
   *   Human readable label of the entity type.
   */
  public function getEntityTypeLabel();

  /**
   * Returns the label for the button to be shown in CKEditor toolbar.
   *
   * @return string
   *   Label for the button.
   */
  public function getButtonLabel();

}
