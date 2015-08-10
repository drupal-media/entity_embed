<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Entity\EmbedButton.
 */

namespace Drupal\entity_embed\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\editor\EditorInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\entity_embed\EmbedButtonInterface;
use Drupal\entity_embed\EntityHelperTrait;
use Drupal\file\FileUsage\FileUsageInterface;

/**
 * Defines the EmbedButton entity.
 *
 * @ConfigEntityType(
 *   id = "entity_embed_button",
 *   label = @Translation("Entity embed button"),
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
 *   config_export = {
 *     "id",
 *     "label",
 *     "entity_type",
 *     "entity_type_bundles",
 *     "button_icon_uuid",
 *     "display_plugins",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/content/embed-button/{entity_embed_button}",
 *     "delete-form" = "/admin/config/content/embed-button/{entity_embed_button}/delete"
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
   * Selected entity type.
   *
   * @var string
   */
  public $entity_type;

  /**
   * Array of bundles allowed for the entity type.
   *
   * An empty array signifies that all are allowed.
   *
   * @var array
   */
  public $entity_type_bundles;

  /**
   * UUID of the button's icon file.
   *
   * @var string
   */
  public $button_icon_uuid;

  /**
   * Array of allowed display plugins for the entity type.
   *
   * An empty array signifies that all are allowed.
   *
   * @var array
   */
  public $display_plugins;

  /**
   * The file usage service.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

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
  public function getEntityTypeBundles() {
    return $this->entity_type_bundles;
  }

  /**
   * {@inheritdoc}
   */
  public function getButtonImage() {
    if ($this->button_icon_uuid && $image = $this->entityManager()->loadEntityByUuid('file', $this->button_icon_uuid)) {
      return $image->url();
    }
    else {
      return file_create_url(drupal_get_path('module', 'entity_embed') . '/js/plugins/drupalentity/entity.png');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAllowedDisplayPlugins() {
    $allowed_display_plugins = array();
    // Include only those plugin ids in result whose value is set.
    foreach ($this->display_plugins as $key => $value) {
      if ($value) {
        $allowed_display_plugins[$key] = $value;
      }
    }
    return $allowed_display_plugins;
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

  /**
   * {@inheritdoc}
   */
  public function isEnabledInEditor(EditorInterface $editor) {
    if ($id = $this->id()) {
      $settings = $editor->getSettings();
      foreach ($settings['toolbar']['rows'] as $row_number => $row) {
        foreach ($row as $group) {
          if (in_array($id, $group['items'])) {
            return TRUE;
          }
        }
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    $new_button_icon_uuid = $this->get('button_icon_uuid');
    if (isset($this->original)) {
      $old_button_icon_uuid = $this->original->get('button_icon_uuid');

      if (!empty($old_button_icon_uuid) && $old_button_icon_uuid != $new_button_icon_uuid) {
        if ($file = $this->entityManager()->loadEntityByUuid('file', $old_button_icon_uuid)) {
          $this->fileUsage()->delete($file, 'entity_embed', $this->getEntityTypeId(), $this->id());
        }
      }
    }

    if ($new_button_icon_uuid) {
      if ($file = $this->entityManager()->loadEntityByUuid('file', $new_button_icon_uuid)) {
        $usage = $this->fileUsage()->listUsage($file);
        if (empty($usage['entity_embed'][$this->getEntityTypeId()][$this->id()])) {
          $this->fileUsage()->add($file, 'entity_embed', $this->getEntityTypeId(), $this->id());
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    foreach ($entities as $entity) {
      $button_icon_uuid = $entity->get('button_icon_uuid');
      if ($button_icon_uuid) {
        if ($file = \Drupal::entityManager()->loadEntityByUuid('file', $button_icon_uuid)) {
          \Drupal::service('file.usage')->delete($file, 'entity_embed', $entity->getEntityTypeId(), $entity->id());
        }
      }
    }
  }

  /**
   * Returns the file usage service.
   *
   * @return \Drupal\file\FileUsage\FileUsageInterface
   *   The file usage service.
   */
  protected function fileUsage() {
    if (!isset($this->fileUsage)) {
      $this->fileUsage = \Drupal::service('file.usage');
    }
    return $this->fileUsage;
  }

  /**
   * Sets the file usage service.
   *
   * @param \Drupal\file\FileUsage\FileUsageInterface $file_usage
   *   The file usage service.
   *
   * @return self
   */
  public function setFileUsage(FileUsageInterface $file_usage) {
    $this->fileUsage = $file_usage;
    return $this;
  }

}
