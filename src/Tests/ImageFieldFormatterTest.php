<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Tests\ImageFieldFormatterTest.
 */

namespace Drupal\entity_embed\Tests;

use Drupal\entity_embed\EntityHelperTrait;

/**
 * Tests the image field formatter provided by entity_embed.
 *
 * @group entity_embed
 */
class ImageFieldFormatterTest extends EntityEmbedTestBase {
  use EntityHelperTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('entity_embed', 'file', 'image', 'node');

  /**
   * Created file entity.
   *
   * @var \Drupal\file\Entity\File
   */
  protected $image;

  /**
   * Created file entity.
   *
   * @var \Drupal\file\Entity\File
   */
  protected $file;

  protected function setUp() {
    parent::setUp();

    file_unmanaged_copy(DRUPAL_ROOT . '/core/misc/druplicon.png', 'public://example.png');
    $this->image = entity_create('file', array(
      'uri' => 'public://example.png',
    ));
    $this->image->save();

    file_put_contents('public://example.txt', $this->randomMachineName());
    $this->file = entity_create('file', array(
      'uri' => 'public://example.txt',
    ));
    $this->file->save();
  }

  /**
   * Tests image field formatter display plugin.
   */
  public function testImageFieldFormatter() {
    // Ensure that image field formatters are available as display plugins.
    $plugin_options = $this->displayPluginManager()->getDefinitionOptionsForEntity($this->image);
    $this->assertTrue(array_key_exists('image:image', $plugin_options), "The 'Image' plugin is available.");

    // Ensure that image plugin is not available to files other than images.
    $plugin_options = $this->displayPluginManager()->getDefinitionOptionsForEntity($this->file);
    $this->assertFalse(array_key_exists('image:image', $plugin_options), "The 'Image' plugin is not available for text file.");

    // Ensure that correct form attributes are returned for the image plugin.
    $form = array();
    $form_state = array();
    $display = $this->displayPluginManager()->createInstance('image:image', array());
    $display->setContextValue('entity', $this->image);
    $conf_form = $display->buildConfigurationForm($form, $form_state);
    $this->assertIdentical(array_keys($conf_form), array('image_style', 'image_link', 'alt', 'title'));
    $this->assertIdentical($conf_form['image_style']['#type'], 'select');
    $this->assertIdentical($conf_form['image_style']['#title'], 'Image style');
    $this->assertIdentical($conf_form['image_link']['#type'], 'select');
    $this->assertIdentical($conf_form['image_link']['#title'], 'Link image to');
    $this->assertIdentical($conf_form['alt']['#type'], 'textfield');
    $this->assertIdentical($conf_form['alt']['#title'], 'Alternate text');
    $this->assertIdentical($conf_form['title']['#type'], 'textfield');
    $this->assertIdentical($conf_form['title']['#title'], 'Title');

    // Test entity embed using 'Image' display plugin.
    $alt_text = "This is sample description";
    $title = "This is sample title";
    $content = '<div data-entity-type="file" data-entity-uuid="' . $this->image->uuid() . '" data-entity-embed-display="image:image" data-entity-embed-settings=\'{"image_link":"file", "alt":"' . $alt_text . '", "title":"' . $title . '"}\'>This placeholder should not be rendered.</div>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test entity embed with image:image';
    $settings['body'] = array(array('value' => $content));
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertNoText($alt_text, 'Alternate text for the embedded image is not visible when embed is successful.');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appears in the output when embed is successful.');
    $this->assertLinkByHref('files/example.png', 0, 'Link to the embedded image exists.');
  }

}
