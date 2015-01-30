<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Form\EmbedButtonDeleteForm.
 */

namespace Drupal\entity_embed\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Url;

/**
 * Builds the form to delete an Embed Button.
 */
class EmbedButtonDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('embed_button.list');
  }

}
