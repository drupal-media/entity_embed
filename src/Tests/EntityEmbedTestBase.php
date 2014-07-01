<?php

/**
 * @file
 * contains \Drupal\entity_embed\Tests\EntityEmbedTestBase.
 */

namespace Drupal\entity_embed\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Base class for all entity_embed tests.
 */
class EntityEmbedTestBase extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('editor', 'entity_embed', 'filter', 'node');

  protected function setUp() {
    parent::setUp();

    // Create a page content type.
    $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Basic page'));

    // Create Filtered HTML text format and enable entity_embed filter.
    $format = entity_create('filter_format', array(
      'format' => 'custom_format',
      'name' => 'Custom format',
      'filters' => array(
        'entity_embed' => array(
          'status' => 1,
        ),
      ),
    ));
    $format->save();

    // Create a user with required permissions.
    $this->webUser = $this->drupalCreateUser(array(
      'access content',
      'create page content',
      'use text format custom_format',
    ));
    $this->drupalLogin($this->webUser);

    // Create a sample node to be embedded.
    $this->embedContent = 'This node is to be used for embedding in other nodes.';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Embed Test Node';
    $settings['body'] = array(array('value' => $this->embedContent));
    $this->node = $this->drupalCreateNode($settings);
  }
}
