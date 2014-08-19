<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Tests\EmbedButtonCrudTest.
 */

namespace Drupal\entity_embed\Tests;

use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\entity_embed\EmbedButtonInterface;
use Drupal\simpletest\KernelTestBase;

/**
 * Tests creation, loading and deletion of embed buttons.
 *
 * @group entity_embed
 */
class EmbedButtonCrudTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('entity_embed', 'node');

  /**
   * The embed button storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface.
   */
  protected $controller;

  protected function setUp() {
    parent::setUp();

    $this->controller = $this->container->get('entity.manager')->getStorage('embed_button');
  }

  /**
   * Tests CRUD operations for embed buttons.
   */
  public function testEntityEmbedCrud() {
    $this->assertTrue($this->controller instanceof ConfigEntityStorage, 'The embed_button storage is loaded.');

    // Run each test method in the same installation.
    $this->createTests();
    $this->loadTests();
    $this->deleteTests();
  }

  /**
   * Tests the creation of embed_button.
   */
  protected function createTests() {
    $plugin = array(
      'id' => 'test_button',
      'label' => 'Testing embed button instance',
      'button_label' => 'Test',
      'entity_type' => 'node',
    );

    // Create an embed_button with required values.
    $entity = $this->controller->create($plugin);
    $entity->save();

    $this->assertTrue($entity instanceof EmbedButtonInterface, 'The newly created entity is an Embed button.');

    // Verify all the properties.
    $actual_properties = $this->container->get('config.factory')->get('entity_embed.embed_button.test_button')->get();

    $this->assertTrue(!empty($actual_properties['uuid']), 'The embed button UUID is set.');
    unset($actual_properties['uuid']);

    $expected_properties = array(
      'langcode' => $this->container->get('language_manager')->getDefaultLanguage()->id,
      'status' => TRUE,
      'dependencies' => array(),
      'label' => 'Testing embed button instance',
      'id' => 'test_button',
      'button_label' => 'Test',
      'entity_type' => 'node',
    );

    $this->assertIdentical($actual_properties, $expected_properties, 'Actual config properties are structured as expected.');
  }

  /**
   * Tests the loading of embed_button.
   */
  protected function loadTests() {
    $entity = $this->controller->load('test_button');

    $this->assertTrue($entity instanceof EmbedButtonInterface, 'The loaded entity is an embed button.');

    // Verify several properties of the embed button.
    $this->assertEqual($entity->label(), 'Testing embed button instance');
    $this->assertEqual($entity->getButtonLabel(), 'Test');
    $this->assertEqual($entity->getEntityTypeMachineName(), 'node');
    $this->assertTrue($entity->uuid());
  }

  /**
   * Tests the deletion of embed_button.
   */
  protected function deleteTests() {
    $entity = $this->controller->load('test_button');

    // Ensure that the storage isn't currently empty.
    $config_storage = $this->container->get('config.storage');
    $config = $config_storage->listAll('entity_embed.embed_button.');
    $this->assertFalse(empty($config), 'There are embed buttons in config storage.');

    // Delete the embed button.
    $entity->delete();

    // Ensure that the storage is now empty.
    $config = $config_storage->listAll('entity_embed.embed_button.');
    $this->assertTrue(empty($config), 'There are no embed buttons in config storage.');
  }

}
