<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Entity\EmbedButton.
 */

namespace Drupal\entity_embed\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\entity_embed\EmbedButtonInterface;
use Drupal\entity_embed\EntityHelperTrait;

/**
 * Defines the EmbedButton entity.
 *
 * @ConfigEntityType(
 *   id = "embed_button",
 *   label = @Translation("Embed Button"),
 *   handlers = {
 *     "list_builder" = "Drupal\entity_embed\EmbedButtonListBuilder",
 *     "form" = {
 *       "add" = "Drupal\entity_embed\Form\EmbedButtonForm",
 *       "edit" = "Drupal\entity_embed\Form\EmbedButtonForm",
 *       "delete" = "Drupal\entity_embed\Form\EmbedButtonDeleteForm"
 *     }
 *   },
 *   config_prefix = "embed_button",
 *   admin_permission = "administer embed buttons",
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
  use EntityHelperTrait;

  /**
   * The EmbedButton ID.
   *
   * @var string
   */
  public $id;

  /**
   * Label of EmbedButton.
   *
   * @var string
   */
  public $label;

  /**
   * Label of the button shown in CKEditor toolbar.
   *
   * @var string
   */
  public $button_label;

  /**
   * Selected entity type.
   *
   * @var string
   */
  public $entity_type;

  /**
   * File id of the button's icon.
   *
   * @var string
   */
  public $button_icon_fid;

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeMachineName() {
    return $this->entity_type;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeLabel() {
    return $this->entityManager()->getDefinition($this->entity_type)->getLabel();
  }


  /**
   * {@inheritdoc}
   */
  public function getButtonLabel() {
    return $this->button_label;
  }

  /**
   * {@inheritdoc}
   */
  public function getButtonImage() {
    if ($this->button_icon_fid) {
      $image = file_load($this->button_icon_fid);
      return $image->url();
    }
    else {
      return drupal_get_path('module', 'entity_embed') . '/js/plugins/drupalentity/entity.png';
    }
  }
}
