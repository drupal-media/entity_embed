<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EmbedButtonInterface.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\editor\EditorInterface;

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
   * Returns the URL of the button's icon.
   *
   * @return string
   *   URL for the button'icon.
   */
  public function getButtonImage();

  /**
   * Returns the list of bundles selected for the entity type.
   *
   * @return array
   *   List of allowed bundles.
   */
  public function getEntityTypeBundles();

  /**
   * Returns the list of display plugins allowed for the entity type.
   *
   * @return array
   *   List of allowed display plugins.
   */
  public function getAllowedDisplayPlugins();

  /**
   * Checks if the entity embed button is enabled in an editor configuration.
   *
   * @param \Drupal\editor\EditorInterface $editor
   *   The editor object to check.
   *
   * @return bool
   *   TRUE if this entity embed button is enabled in $editor. FALSE otherwise.
   */
  public function isEnabledInEditor(EditorInterface $editor);

}
