<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\EntityEmbedDisplay\FileFieldFormatter.
 */

namespace Drupal\entity_embed\Plugin\EntityEmbedDisplay;

use Drupal\entity_embed\FieldFormatterEntityEmbedDisplayBase;
use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Session\AccountInterface;

/**
 * Embed entity displays for file field formatters.
 *
 * @EntityEmbedDisplay(
 *   id = "file",
 *   label = @Translation("File"),
 *   types = {"file"},
 *   derivative = "Drupal\entity_embed\Plugin\Derivative\FieldFormatter",
 *   field_type = "file",
 *   provider = "file"
 * )
 */
class FileFieldFormatter extends FieldFormatterEntityEmbedDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    $field = FieldDefinition::create('file');
    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account = NULL) {
    if (!$this->entityMatchesAllowedTypes()) {
      return FALSE;
    }

    // Due to issues with access checking with file entities in core, we cannot
    // actually use Entity::access() which would have been called by
    // parent::access().
    // @see https://drupal.org/node/2128791
    // @see https://drupal.org/node/2148353
    // @see https://drupal.org/node/2078473
    switch (file_uri_scheme($this->entity->getFileUri())) {
      case 'public':
        return TRUE;

      case 'private':
      case 'temporary':
        $headers = $this->moduleHandler()->invokeAll('file_download', array($uri));
        foreach ($headers as $result) {
          if ($result == -1) {
            return FALSE;
          }
        }

        if (count($headers)) {
          return TRUE;
        }
        break;
    }

    return FALSE;
  }

}
