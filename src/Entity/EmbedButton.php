<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Entity\EmbedButton.
 */

namespace Drupal\entity_embed\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\entity_embed\EmbedButtonInterface;

/**
 * Defines the EmbedButton entity.
 *
 * @ConfigEntityType(
 *   id = "embed_button",
 *   label = @Translation("Embed Button"),
 *   controllers = {
 *     "list_builder" = "Drupal\entity_embed\EmbedButtonListBuilder",
 *     "form" = {
 *       "add" = "Drupal\entity_embed\Form\EmbedButtonForm",
 *       "edit" = "Drupal\entity_embed\Form\EmbedButtonForm",
 *       "delete" = "Drupal\entity_embed\Form\EmbedButtonDeleteForm"
 *     }
 *   },
 *   config_prefix = "embed_button",
 *   admin_permission = "administer entity embed settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "edit-form" = "embed_button.edit",
 *     "delete-form" = "embed_button.delete"
 *   }
 * )
 */
class EmbedButton extends ConfigEntityBase implements EmbedButtonInterface {

  /**
   * The EmbedButton ID.
   *
   * @var string
   */
  public $id;
  /**
   * The EmbedButton label.
   *
   * @var string
   */
  public $label;

  protected $button_label;

  protected $entity_types = array();

  public function getEntityTypes() {
    return $this->entity_types;
  }

  public function getButtonLabel() {
    return $this->button_label;
  }

}

