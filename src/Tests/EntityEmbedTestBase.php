<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Tests\EntityEmbedTestBase.
 */

namespace Drupal\entity_embed\Tests;

use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\simpletest\WebTestBase;

/**
 * Base class for all entity_embed tests.
 */
abstract class EntityEmbedTestBase extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['entity_embed', 'node', 'ckeditor'];

  /**
   * The test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $webUser;

  /**
   * A test node to be used for embedding.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  protected function setUp() {
    parent::setUp();

    // Create a page content type.
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);

    // Create Filtered HTML text format and enable entity_embed filter.
    $format = FilterFormat::create([
      'format' => 'custom_format',
      'name' => 'Custom format',
      'filters' => [
        'entity_embed' => [
          'status' => 1,
        ],
      ],
    ]);
    $format->save();

    $editor_group = [
      'name' => 'Entity Embed',
      'items' => [
        'node',
      ],
    ];
    $editor = Editor::create([
      'format' => 'custom_format',
      'editor' => 'ckeditor',
      'settings' => [
        'toolbar' => [
          'rows' => [[$editor_group]],
        ],
      ],
    ]);
    $editor->save();

    // Create a user with required permissions.
    $this->webUser = $this->drupalCreateUser([
      'access content',
      'create page content',
      'use text format custom_format',
    ]);
    $this->drupalLogin($this->webUser);

    // Create a sample node to be embedded.
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Embed Test Node';
    $settings['body'] = array('value' => 'This node is to be used for embedding in other nodes.', 'format' => 'custom_format');
    $this->node = $this->drupalCreateNode($settings);
  }
}
