<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Tests\EntityEmbedHooksTest.
 */

namespace Drupal\entity_embed\Tests;

use Drupal\entity_embed\EntityHelperTrait;

/**
 * Tests the hooks provided by entity_embed module.
 *
 * @group entity_embed
 */
class EntityEmbedHooksTest extends EntityEmbedTestBase {
  use EntityHelperTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('entity_reference', 'entity_embed', 'entity_embed_test', 'node');

  /**
   * Tests hook_entity_embed_display_plugins_alter().
   */
  public function testDisplayPluginAlterHooks() {
    // Enable entity_embed_test.module's
    // hook_entity_embed_display_plugins_alter() implementation and ensure it is
    // working as designed.
    \Drupal::state()->set('entity_embed_test_entity_embed_display_plugins_alter', TRUE);
    $plugins = $this->displayPluginManager()->getDefinitionOptionsForEntity($this->node);
    // Ensure that name of each plugin is prefixed with 'testing_hook:'.
    foreach ($plugins as $plugin => $plugin_info) {
      $this->assertTrue(strpos($plugin, 'testing_hook:') === 0, 'Name of the plugin is prefixed by hook_entity_embed_display_plugins_alter()');
    }
  }

  /**
   * Tests the hooks provided by entity_embed module.
   *
   * This method tests all the hooks provided by entity_embed except
   * hook_entity_embed_display_plugins_alter, which is tested by a separate
   * method.
   */
  public function testEntityEmbedHooks() {
    // Enable entity_embed_test.module's hook_entity_preembed() implementation
    // and ensure it is working as designed.
    \Drupal::state()->set('entity_embed_test_entity_preembed', TRUE);
    $content = '<div data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="default" data-entity-embed-settings=\'{"view_mode":"teaser"}\'>This placeholder should not be rendered.</div>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test hook_entity_preembed()';
    $settings['body'] = array(array('value' => $content));
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertText($this->node->body->value, 'Embedded node exists in page.');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appears in the output when embed is successful.');
    // Ensure that embedded node's title has been replaced.
    $this->assertText('Title set by hook_entity_preembed', 'Title of the embedded node is replaced by hook_entity_preembed()');
    $this->assertNoText($this->node->title->value, 'Original title of the embedded node is not visible.');
    \Drupal::state()->set('entity_embed_test_entity_preembed', FALSE);

    // Enable entity_embed_test.module's hook_entity_embed_alter()
    // implementation and ensure it is working as designed.
    \Drupal::state()->set('entity_embed_test_entity_embed_alter', TRUE);
    $content = '<div data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="default" data-entity-embed-settings=\'{"view_mode":"teaser"}\'>This placeholder should not be rendered.</div>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test hook_entity_embed_alter()';
    $settings['body'] = array(array('value' => $content));
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertText($this->node->body->value, 'Embedded node exists in page.');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appears in the output when embed is successful.');
    // Ensure that embedded node's title has been replaced.
    $this->assertText('Title set by hook_entity_embed_alter', 'Title of the embedded node is replaced by hook_entity_embed_alter()');
    $this->assertNoText($this->node->title->value, 'Original title of the embedded node is not visible.');
    \Drupal::state()->set('entity_embed_test_entity_embed_alter', FALSE);

    // Enable entity_embed_test.module's hook_entity_embed_context_alter()
    // implementation and ensure it is working as designed.
    \Drupal::state()->set('entity_embed_test_entity_embed_context_alter', TRUE);
    $content = '<div data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="default" data-entity-embed-settings=\'{"view_mode":"teaser"}\'>This placeholder should not be rendered.</div>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test hook_entity_embed_context_alter()';
    $settings['body'] = array(array('value' => $content));
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertNoText(strip_tags($content), 'Placeholder does not appears in the output when embed is successful.');
    // To ensure that 'label' plugin is used, verify that the body of the
    // embedded node is not visible and the title links to the embedded node.
    $this->assertNoText($this->node->body->value, 'Body of the embedded node does not exists in page.');
    $this->assertText($this->node->title->value, 'Title of the embedded node exists in page.');
    $this->assertLinkByHref('node/' . $this->node->id(), 0, 'Link to the embedded node exists.');
    \Drupal::state()->set('entity_embed_test_entity_embed_context_alter', FALSE);
  }

}
