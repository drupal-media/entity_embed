<?php

namespace Drupal\entity_embed\Tests;

use Drupal\system\Tests\Update\UpdatePathTestBase;

/**
 * Tests the update hooks in entity_embed module.
 *
 * @group entity_embed
 */
class EntityEmbedUpdateHookTest extends UpdatePathTestBase {

  /**
   * Set database dump files to be used.
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      DRUPAL_ROOT . '/core/modules/system/tests/fixtures/update/drupal-8.bare.standard.php.gz',
      __DIR__ . '/../../tests/fixtures/update/entity_embed.update-hook-test.php',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function doSelectionTest() {
    parent::doSelectionTest();
    $this->assertRaw('8002 -   Updates the default mode settings.');
  }

  /**
   * Tests entity_embed_update_8002().
   */
  public function testPostUpdate() {
    $this->runUpdates();
    $mode = $this->container->get('config.factory')
      ->get('entity_embed.settings')
      ->get('rendered_entity_mode');
    $this->assertTrue($mode, 'Render entity mode settings after update is correct.');
  }

}
