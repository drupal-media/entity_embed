<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Tests\EntityEmbedPreviewTest.
 */

namespace Drupal\entity_embed\Tests;

/**
 * Tests the entity_embed preview controller and route.
 *
 * @group entity_embed
 */
class EntityEmbedPreviewTest extends EntityEmbedTestBase {

  /**
   * URL of the preview route.
   *
   * @var string
   */
  protected $previewUrl;

  protected function setUp() {
    parent::setUp();

    // Define URL that will be used to access the preview route.
    $this->previewUrl = 'entity-embed/preview/custom_format';
  }

  /**
   * Tests the route used for generating preview of embedding entities.
   */
  public function testPreviewRoute() {
    // Test preview route with a valid request.
    $content = '<div data-entity-type="node" data-entity-id="' . $this->node->id() . '" data-view-mode="teaser">This placeholder should not be rendered.</div>';
    $this->drupalGet($this->previewUrl, array('query' => array('value' => $content)));
    $this->assertResponse(200, 'The preview route exists.');
    $this->assertText($this->node->body->value, 'Embedded node exists in page');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appears in the output when embed is successful.');

    // Test preview route with an invalid request.
    $content = 'Testing preview route without valid values';
    $this->drupalGet($this->previewUrl, array('query' => array('value' => $content)));
    $this->assertResponse(200, 'The preview route exists.');
    $this->assertText($content, 'Placeholder appears in the output when embed is unsuccessful.');

    // Test preview route with an empty request.
    $this->drupalGet($this->previewUrl);
    $this->assertResponse(404, "The preview returns 'Page not found' when GET parameters are not provided.");
  }

}
