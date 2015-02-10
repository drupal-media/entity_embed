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
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "embed_button",
 *   admin_permission = "administer embed buttons",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/content/embed-button/{embed_button}",
 *     "delete-form" = "/admin/config/content/embed-button/{embed_button}/delete"
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
   * UUID of the button's icon file.
   *
   * @var string
   */
  public $button_icon_uuid;

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
    if ($this->button_icon_uuid) {
      $image = $this->entityManager()->loadEntityByUuid('file', $this->button_icon_uuid);
      return $image->url();
    }
    else {
      return file_create_url(drupal_get_path('module', 'entity_embed') . '/js/plugins/drupalentity/entity.png');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    // Add the file icon entity as dependency if an UUID was specified.
    if ($this->button_icon_uuid && $file_icon = $this->entityManager()->loadEntityByUuid('file', $this->button_icon_uuid)) {
      $this->addDependency($file_icon->getConfigDependencyKey(), $file_icon->getConfigDependencyName());
    }

    return $this->dependencies;
  }

}
