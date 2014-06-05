<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Tests\Twig\EntityEmbedTwigUnitTest.
 */

namespace Drupal\entity_embed\Tests\Twig;
use Drupal\Tests\UnitTestCase;
use Drupal\entity_embed\Twig\EntityEmbedTwigExtension;

use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * Tests the XmlEncoder class.
 *
 * @see \Drupal\serialization\Encoder\XmlEncoder
 */
class EntityEmbedTwigUnitTest extends UnitTestCase {

  /**
   * The entity manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'EntityEmbedTwigUnitTest',
      'description' => 'Tests the EntityEmbedTwigExtension class.',
      'group' => 'Theme',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $container = new ContainerBuilder();
    $container->set('entity.manager', $this->entityManager);
    \Drupal::setContainer($container);
  }

  /**
   * Tests the supportsEncoding() method.
   */
  public function testGetName() {
    $extension = new EntityEmbedTwigExtension($this->entityManager);
    $this->assertEquals('entity_embed.twig.entity_embed_twig_extension', $extension->getName(), 'Service name matches the expected value');
  }
}
