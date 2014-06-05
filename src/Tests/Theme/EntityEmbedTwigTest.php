<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Theme\TwigExtensionTest.
 */

namespace Drupal\entity_embed\Tests\Theme;

use Drupal\simpletest\WebTestBase;

/**
 * Tests Twig extensions.
 */
class EntityEmbedTwigTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('entity_embed', 'entity_embed_twig_test', 'node');

  public static function getInfo() {
    return array(
      'name' => 'Entity Embed Twig Test',
      'description' => 'Testing of {{ entity_embed() }}',
      'group' => 'Theme',
    );
  }

  function setUp() {
    parent::setUp();
    theme_enable(array('test_theme'));
  }

  /**
   * Tests that the provided Twig extension loads the service appropriately.
   */
  function testTwigExtensionLoaded() {
    $twigService = \Drupal::service('twig');

    $ext = $twigService->getExtension('entity_embed.twig.entity_embed_twig_extension');

    // @todo why is the string
    // 'Drupal\\entity_embed\\Twig\\EntityEmbedTwigExtension'
    // and not '\Drupal\entity_embed\Twig\EntityEmbedTwigExtension' ?
    $this->assertEqual(get_class($ext), 'Drupal\\entity_embed\\Twig\\EntityEmbedTwigExtension', 'Extension loaded successfully.');
  }

  /**
   * Tests that the Twig extension's filter produces expected output.
   */
  function testEntityEmbedTwigFunction() {

    // Create a node and then verify whether it was embedded.
    $node = $this->drupalCreateNode();
    $this->drupalGet('entity_embed_twig_test');
    $this->assertText($node->getTitle(), 'Node was embedded');
  }
}
