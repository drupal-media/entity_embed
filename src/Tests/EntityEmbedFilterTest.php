<?php

/**
 * @file
 * contains \Drupal\entity_embed\Tests\EntityEmbedFilterTest.
 */

namespace Drupal\entity_embed\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the entity_embed filter.
 */
class EntityEmbedFilterTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('entity_embed', 'filter', 'node');

  public static function getInfo() {
    return array(
      'name' => 'Entity Embed Filter Test',
      'description' => 'Tests the entity_embed filter',
      'group' => 'Entity Embed',
    );
  }

  protected function setUp() {
    parent::setUp();

    // Create a page content type.
    $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Basic page'));

    // Create Filtered HTML text format and enable entity_embed filter.
    $filtered_html_format = entity_create('filter_format', array(
      'format' => 'filtered_html',
      'name' => 'Filtered HTML',
      'filters' => array(
        'entity_embed' => array(
          'status' => 1,
        ),
      ),
    ));
    $filtered_html_format->save();

    // Create a user with required permissions.
    $this->webUser = $this->drupalCreateUser(array(
      'access content',
      'create page content',
      'use text format filtered_html',
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

  /**
   * Tests entity embed using entity ID and view mode.
   */
  public function testFilterIdViewMode() {
    $content = '<div data-entity-type="node" data-entity-id="' . $this->node->id() . '" data-view-mode="teaser">This placeholder should not be rendered.</div>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test entity embed with entity-id and view-mode';
    $settings['body'] = array(array('value' => $content));
    $node = $this->drupalCreateNode($settings);

    $html = $this->drupalGet('node/' . $node->id());

    $this->assertText($this->embedContent, 'Embedded node exists in page');
    $this->assertNoText('This placeholder should not be rendered.', 'Placeholder does not appears in the output when embed is successful.');
  }

  /**
   * Tests entity embed using entity UUID and view mode.
   */
  public function testFilterUuidViewMode() {
    $content = '<div data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-view-mode="teaser">This placeholder should not be rendered.</div>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test entity embed with entity-uuid and view-mode';
    $settings['body'] = array(array('value' => $content));
    $node = $this->drupalCreateNode($settings);

    $html = $this->drupalGet('node/' . $node->id());

    $this->assertText($this->embedContent, 'Embedded node exists in page.');
    $this->assertNoText('This placeholder should not be rendered.', 'Placeholder does not appears in the output when embed is successful.');
  }

  /**
   * Tests that placeholder must not be replaced when embed is unsuccessful.
   */
  public function testFilterInvalidEntity() {
    $content = '<div data-entity-type="node" data-entity-id="InvalidID" data-view-mode="teaser">This placeholder should be rendered since specified entity does not exists.</div>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test that placeholder is retained when specified entity does not exists';
    $settings['body'] = array(array('value' => $content));
    $node = $this->drupalCreateNode($settings);

    $html = $this->drupalGet('node/' . $node->id());

    $this->assertText('This placeholder should be rendered since specified entity does not exists.', 'Placeholder appears in the output when embed is unsuccessful.');
  }

  /**
   * Tests that UUID is preferred over ID when both attributes are present.
   */
  public function testFilterUuidPreference() {
    $sample_node = $this->drupalCreateNode();

    $content = '<div data-entity-type="node" data-entity-id="' . $sample_node->id() . '" data-entity-uuid="' . $this->node->uuid() . '" data-view-mode="teaser">This placeholder should not be rendered.</div>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test that entity-uuid is preferred over entity-id when both attributes are present';
    $settings['body'] = array(array('value' => $content));
    $node = $this->drupalCreateNode($settings);

    $html = $this->drupalGet('node/' . $node->id());

    $this->assertText($this->embedContent, 'Entity specifed with UUID exists in the page.');
    $this->assertNoText($sample_node->body->value, 'Entity specifed with ID does not exists in the page.');
    $this->assertNoText('This placeholder should not be rendered.', 'Placeholder not appears in the output when embed is successful.');
  }

  /**
   * Tests entity embed using display plugin.
   */
  public function testFilterDisplayPlugin() {
    $content = '<div data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="default" data-entity-embed-settings=\'{"view_mode":"teaser"}\'>This placeholder should not be rendered.</div>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test entity embed with entity-embed-display and data-entity-embed-settings';
    $settings['body'] = array(array('value' => $content));
    $node = $this->drupalCreateNode($settings);

    $html = $this->drupalGet('node/' . $node->id());

    $this->assertText($this->embedContent, 'Embedded node exists in page.');
    $this->assertNoText('This placeholder should not be rendered.', 'Placeholder does not appears in the output when embed is successful.');
  }

  /**
   * Tests that display plugin is preferred over view mode when both attributes are present.
   */
  public function testFilterDisplayPluginPreference() {
    $content = '<div data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="default" data-entity-embed-settings=\'{"view_mode":"teaser"}\' data-view-mode="some-invalid-view-mode">This placeholder should not be rendered.</div>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test entity embed with entity-embed-display and data-entity-embed-settings';
    $settings['body'] = array(array('value' => $content));
    $node = $this->drupalCreateNode($settings);

    $html = $this->drupalGet('node/' . $node->id());

    $this->assertText($this->embedContent, 'Embedded node exists in page with the view mode specified by entity-embed-settings.');
    $this->assertNoText('This placeholder should not be rendered.', 'Placeholder does not appears in the output when embed is successful.');
  }

}
