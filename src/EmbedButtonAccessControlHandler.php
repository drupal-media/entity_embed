<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EmbedButtonAccessControlHandler.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the embed button entity type.
 *
 * @see \Drupal\entity_embed\Entity\EmbedButton
 */
class EmbedButtonAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $embed_button, $operation, $langcode, AccountInterface $account) {
    if ($operation == 'use') {
      return AccessResult::allowedIfHasPermission($account, $embed_button->getPermissionName());
    }

    // No opinion.
    return AccessResult::neutral();
  }

}
