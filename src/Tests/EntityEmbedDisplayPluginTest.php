<?php

/**
 * @file
 * contains \Drupal\entity_embed\Tests\EntityEmbedDisplayPluginTest.
 */

namespace Drupal\entity_embed\Tests;

use Drupal\entity_embed\EntityHelperTrait;

/**
 * Tests presence of display plugins and verify their configuration forms.
 *
 * @group entity_embed
 */
class EntityEmbedDisplayPluginTest extends EntityEmbedTestBase {
  use EntityHelperTrait;

  /**
   * The test 'node' entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  protected function setUp() {
    parent::setUp();

    $this->entity = $this->loadEntity('node', $this->node->uuid());
  }

  /**
   * Tests that default display plugins are present for 'node' entity.
   */
  public function testDisplayPluginOptions() {
    $plugin_options = $this->displayPluginManager()->getDefinitionOptionsForEntity($this->entity);
    // Test that default plugin types exist in the options array.
    $this->assertTrue(array_key_exists('default', $plugin_options), "The 'Default' plugin exists.");
    $this->assertTrue(array_key_exists('entity_reference:entity_reference_entity_id', $plugin_options), "The 'Entity ID' plugin exists.");
    $this->assertTrue(array_key_exists('entity_reference:entity_reference_entity_view', $plugin_options), "The 'Rendered entity' plugin exists.");
    $this->assertTrue(array_key_exists('entity_reference:entity_reference_label', $plugin_options), "The 'Label' plugin exists.");
  }

  /**
   * Tests that correct form attributes are returned for 'default' plugin.
   */
  public function testDefaultPluginConfigurationForm() {
    $form = array();
    $form_state = array();
    $display = $this->displayPluginManager()->createInstance('default', array());
    $display->setContextValue('entity', $this->entity);
    $conf_form = $display->buildConfigurationForm($form, $form_state);
    $this->assertIdentical(array_keys($conf_form), array('view_mode'));
    $this->assertIdentical($conf_form['view_mode']['#type'], 'select');
    $this->assertIdentical($conf_form['view_mode']['#title'], 'View mode');
  }

  /**
   * Tests that correct form attributes are returned for 'entity_reference:entity_reference_entity_id' plugin.
   */
  public function testEntityIdPluginConfigurationForm() {
    $form = array();
    $form_state = array();
    $display = $this->displayPluginManager()->createInstance('entity_reference:entity_reference_entity_id', array());
    $display->setContextValue('entity', $this->entity);
    $conf_form = $display->buildConfigurationForm($form, $form_state);
    $this->assertIdentical(array_keys($conf_form), array());
  }

  /**
   * Tests that correct form attributes are returned for 'entity_reference:entity_reference_entity_view' plugin.
   */
  public function testRenderedEntityPluginConfigurationForm() {
    $form = array();
    $form_state = array();
    $display = $this->displayPluginManager()->createInstance('entity_reference:entity_reference_entity_view', array());
    $display->setContextValue('entity', $this->entity);
    $conf_form = $display->buildConfigurationForm($form, $form_state);
    $this->assertIdentical(array_keys($conf_form), array('view_mode', 'links'));
    $this->assertIdentical($conf_form['view_mode']['#type'], 'select');
    $this->assertIdentical($conf_form['view_mode']['#title'], 'View mode');
    $this->assertIdentical($conf_form['links']['#type'], 'checkbox');
    $this->assertIdentical($conf_form['links']['#title'], 'Show links');
  }

  /**
   * Tests that correct form attributes are returned for 'entity_reference:entity_reference_label' plugin.
   */
  public function testLabelPluginConfigurationForm() {
    $form = array();
    $form_state = array();
    $display = $this->displayPluginManager()->createInstance('entity_reference:entity_reference_label', array());
    $display->setContextValue('entity', $this->entity);
    $conf_form = $display->buildConfigurationForm($form, $form_state);
    $this->assertIdentical(array_keys($conf_form), array('link'));
    $this->assertIdentical($conf_form['link']['#type'], 'checkbox');
    $this->assertIdentical($conf_form['link']['#title'], 'Link label to the referenced entity');
  }

}
