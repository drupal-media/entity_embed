<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedInsertCommand.
 */

namespace Drupal\entity_embed;

use Drupal\Core\Ajax\CommandInterface;

/**
 * AJAX command for inserting an embedded entity in a CKEditor.
 *
 * @ingroup ajax
 */
class EntityEmbedInsertCommand implements CommandInterface {

  /**
   * The HTML content that will replace the matched element(s).
   *
   * @var string
   */
  protected $html;

  /**
   * Constructs an EntityEmbedCommand object.
   *
   * @param string $html
   *   String of HTML to be inserted.
   */
  public function __construct($html) {
    $this->html = $html;
  }

  /**
   * Implements Drupal\Core\Ajax\CommandInterface:render().
   */
  public function render() {
    return array(
      'command' => 'entity_embed_insert',
      'html' => $this->html,
    );
  }

}
