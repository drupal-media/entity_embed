<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Tests\EntityEmbedFilterTest.
 */

namespace Drupal\entity_embed\Tests;

/**
 * Tests the entity_embed filter.
 *
 * @group entity_embed
 */
class EntityEmbedFilterTest extends EntityEmbedTestBase {

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

    $this->drupalGet('node/' . $node->id());

    $this->assertText($this->node->body->value, 'Embedded node exists in page');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appears in the output when embed is successful.');
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

    $this->drupalGet('node/' . $node->id());

    $this->assertText($this->node->body->value, 'Embedded node exists in page.');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appears in the output when embed is successful.');
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

    $this->drupalGet('node/' . $node->id());

    $this->assertText(strip_tags($content), 'Placeholder appears in the output when embed is unsuccessful.');
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

    $this->drupalGet('node/' . $node->id());

    $this->assertText($this->node->body->value, 'Entity specifed with UUID exists in the page.');
    $this->assertNoText($sample_node->body->value, 'Entity specifed with ID does not exists in the page.');
    $this->assertNoText(strip_tags($content), 'Placeholder not appears in the output when embed is successful.');
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

    $this->drupalGet('node/' . $node->id());

    $this->assertText($this->node->body->value, 'Embedded node exists in page.');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appears in the output when embed is successful.');
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

    $this->drupalGet('node/' . $node->id());

    $this->assertText($this->node->body->value, 'Embedded node exists in page with the view mode specified by entity-embed-settings.');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appears in the output when embed is successful.');
  }

}
