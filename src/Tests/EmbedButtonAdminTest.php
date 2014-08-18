<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Tests\EmbedButtonAdminTest.
 */

namespace Drupal\entity_embed\Tests;

/**
 * Tests the administrative UI.
 *
 * @group entity_embed
 */
class EmbedButtonAdminTest extends EntityEmbedTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('editor', 'ckeditor', 'entity_embed', 'node');

  /**
   * A user with permission to administer embed buttons.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  protected function setUp() {
    parent::setUp();

    // Create a user with admin permissions.
    $this->adminUser = $this->drupalCreateUser(array(
      'access content',
      'create page content',
      'use text format custom_format',
      'administer entity embed settings',
    ));
  }

  /**
   * Tests the embed_button administration functionality.
   */
  public function testEmbedButtonAdmin() {
    $this->drupalGet('admin/config/content/embed-button');
    $this->assertResponse(403, 'User without admin permissions are not able to visit the configuration page.');

    // Swtich to admin user.
    $this->drupalLogin($this->adminUser);

    $this->drupalGet('admin/config/content/embed-button');
    $this->assertResponse(200, 'User without admin permissions is able to visit the configuration page.');
    $this->assertText('Node', 'Node embed_button entity exists by default.');
    $this->assertText('Content', 'Node embed_button entity exists by default.');

    // Add embed button.
    $this->clickLink('Add Embed Button');
    $button_id = drupal_strtolower($this->randomMachineName());
    $name = $this->randomMachineName();
    $edit = array(
      'id' => $button_id,
      'label' => $name,
      'entity_type' => 'node',
      'button_label' => $name,
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));
    // Ensure that the newly created button exists.
    $this->drupalGet('admin/config/content/embed-button/' . $button_id);
    $this->assertResponse(200, 'Added embed button exists.');
    // Ensure that the newly created button is listed.
    $this->drupalGet('admin/config/content/embed-button');
    $this->assertText($name, 'Test embed_button appears on the list page');

    // Edit embed button.
    $this->drupalGet('admin/config/content/embed-button/' . $button_id);
    $new_name = $this->randomMachineName();
    $edit = array(
      'label' => $new_name,
      'button_label' => $new_name,
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));
    // Ensure that name and button_label has been changed.
    $this->drupalGet('admin/config/content/embed-button');
    $this->assertText($new_name, 'New label appears on the list page');
    $this->assertNoText($name, 'Old label does not appears on the list page');

    // Delete embed button.
    $this->drupalGet('admin/config/content/embed-button/' . $button_id . '/delete');
    $this->drupalPostForm(NULL, array(), t('Delete'));
    // Ensure that the deleted embed button no longer exists.
    $this->drupalGet('admin/config/content/embed-button/' . $button_id);
    $this->assertResponse(404, 'Deleted embed button no longer exists.');
    // Ensure that the deleted button is no longer listed.
    $this->drupalGet('admin/config/content/embed-button');
    $this->assertNoText($name, 'Test embed_button does not appears on the list page');
  }

}
