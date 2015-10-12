<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Tests\EntityEmbedTestBase.
 */

namespace Drupal\entity_embed\Tests;

use Drupal\Core\Entity\EntityInterface;
use Drupal\editor\Entity\Editor;
use Drupal\embed\Tests\EmbedTestBase;
use Drupal\entity_embed\EntityHelperTrait;
use Drupal\file\Entity\File;
use Drupal\filter\Entity\FilterFormat;
use Drupal\simpletest\WebTestBase;

/**
 * Base class for all entity_embed tests.
 */
abstract class EntityEmbedTestBase extends EmbedTestBase {
  use EntityHelperTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['entity_embed', 'entity_embed_test', 'node'];

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

    // Create a text format and enable the entity_embed filter.
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

  public function assertAvailableDisplayPlugins(EntityInterface $entity, array $expected_plugins, $message = '') {
    $plugin_options = $this->displayPluginManager()->getDefinitionOptionsForEntity($entity);
    // @todo Remove the sorting so we can actually test return order.
    ksort($plugin_options);
    sort($expected_plugins);
    $this->assertEqual(array_keys($plugin_options), $expected_plugins, $message);
  }
}
