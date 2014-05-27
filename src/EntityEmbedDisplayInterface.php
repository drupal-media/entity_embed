<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplayInterface.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Entity\EntityInterface;

interface EntityEmbedDisplayInterface {

  public function __construct(EntityInterface $entity, array $context = array());

  public static function defaultSettings();

  public function settingsForm(array $form, array &$form_state);

  public function access();

  public function getContext($name = NULL);

  public function getSettings();

  public function getSetting($name);

  public static function postRender(array $element, array $context);

  public function viewEntity();

}
