<?php

/**
 * @file
 * contains \Drupal\entity_embed\Tests\EntityEmbedPreviewTest.
 */

namespace Drupal\entity_embed\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the entity_embed preview controller and route.
 *
 * @see Drupal\entity_embed\EntityEmbedController
 */
class EntityEmbedPreviewTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('editor', 'entity_embed', 'filter', 'node');

  public static function getInfo() {
    return array(
      'name' => 'Entity Embed Preview Test',
      'description' => 'Tests the entity_embed controller and route',
      'group' => 'Entity Embed',
    );
  }

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

    // Define URL that will be used to access the preview route
    $this->preview_url = 'entity-embed/preview/custom_format';
  }

  /**
   * Tests preview route with a valid request.
   */
  public function testPreviewController() {
    $content = '<div data-entity-type="node" data-entity-id="' . $this->node->id() . '" data-view-mode="teaser">This placeholder should not be rendered.</div>';

    $html = $this->drupalGet($this->preview_url, array('value' => 'Test'));

    $this->assertResponse(200, 'The preview route exists.');
    $this->assertText($this->embedContent, 'Embedded node exists in page');
    $this->assertNoText('This placeholder should not be rendered.', 'Placeholder does not appears in the output when embed is successful.');
  }

  /**
   * Tests preview route with an invalid request.
   */
  public function testPreviewControllerInvalidRequest() {
    $html = $this->drupalGet($this->preview_url, array('value' => 'Testing preview route without valid values'));

    $this->assertResponse(200, 'The preview route exists.');
    $this->assertText('Testing preview route without valid values', 'Placeholder appears in the output when embed is unsuccessful.');
  }

  /**
   * Tests preview route with an empty request.
   */
  public function testPreviewControllerEmptyRequest() {
    $html = $this->drupalGet($this->preview_url);

    $this->assertResponse(404, "The preview returns 'Page not found' when GET parameters are not provided.");
  }

}
