<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Tests\FileFieldFormatterTest.
 */

namespace Drupal\entity_embed\Tests;

use Drupal\Core\Form\FormState;
use Drupal\entity_embed\EntityHelperTrait;

/**
 * Tests the file field formatter provided by entity_embed.
 *
 * @group entity_embed
 */
class FileFieldFormatterTest extends EntityEmbedTestBase {
  use EntityHelperTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('entity_reference', 'entity_embed', 'file', 'node');

  /**
   * Created file entity.
   *
   * @var \Drupal\file\Entity\File
   */
  protected $file;

  protected function setUp() {
    parent::setUp();

    file_put_contents('public://example.txt', $this->randomMachineName());
    $this->file = entity_create('file', array(
      'uri' => 'public://example.txt',
    ));
    $this->file->save();
  }

  /**
   * Tests file field formatter display plugins.
   */
  public function testFileFieldFormatter() {
    // Ensure that file field formatters are available as plugins.
    $plugin_options = $this->displayPluginManager()->getDefinitionOptionsForEntity($this->file);
    // Ensure that 'default' plugin is available.
    $this->assertTrue(array_key_exists('default', $plugin_options), "The 'Default' plugin is available.");
    // Ensure that 'entity_reference' plugins are available.
    $this->assertTrue(array_key_exists('entity_reference:entity_reference_entity_id', $plugin_options), "The 'Entity ID' plugin is available.");
    $this->assertTrue(array_key_exists('entity_reference:entity_reference_entity_view', $plugin_options), "The 'Rendered entity' plugin is available.");
    $this->assertTrue(array_key_exists('entity_reference:entity_reference_label', $plugin_options), "The 'Label' plugin is available.");
    // Ensure that 'file' plugins are available.
    $this->assertTrue(array_key_exists('file:file_table', $plugin_options), "The 'Table of files' plugin is available.");
    $this->assertTrue(array_key_exists('file:file_rss_enclosure', $plugin_options), "The 'RSS enclosure' plugin is available.");
    $this->assertTrue(array_key_exists('file:file_default', $plugin_options), "The 'Generic file' plugin is available.");
    $this->assertTrue(array_key_exists('file:file_url_plain', $plugin_options), "The 'URL to file' plugin is available.");

    // Ensure that correct form attributes are returned for the file field
    // formatter plugins.
    $form = array();
    $form_state = new FormState();
    $plugins = array('file:file_table', 'file:file_rss_enclosure', 'file:file_default', 'file:file_url_plain');
    // Ensure that description field is available for all the 'file' plugins.
    foreach ($plugins as $plugin) {
      $display = $this->displayPluginManager()->createInstance($plugin, array());
      $display->setContextValue('entity', $this->file);
      $conf_form = $display->buildConfigurationForm($form, $form_state);
      $this->assertIdentical(array_keys($conf_form), array('description'));
      $this->assertIdentical($conf_form['description']['#type'], 'textfield');
      $this->assertIdentical($conf_form['description']['#title'], 'Description');
    }

    // Test entity embed using 'Generic file' display plugin.
    $description = "This is sample description";
    $content = '<div data-entity-type="file" data-entity-uuid="' . $this->file->uuid() . '" data-entity-embed-display="file:file_default" data-entity-embed-settings=\'{"description":"' . $description . '"}\'>This placeholder should not be rendered.</div>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test entity embed with file:file_default';
    $settings['body'] = array(array('value' => $content));
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertText($description, 'Description of the embedded file exists in page.');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appears in the output when embed is successful.');
    $this->assertLinkByHref('files/example.txt', 0, 'Link to the embedded file exists.');
  }

}
