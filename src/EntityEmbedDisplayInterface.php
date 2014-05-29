<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplayInterface.
 */

namespace Drupal\entity_embed;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the required interface for all entity embed display plugins.
 *
 * @ingroup entity_embed_api
 */
interface EntityEmbedDisplayInterface extends ConfigurablePluginInterface, PluginInspectionInterface {

  /**
   * Indicates whether the block should be shown.
   *
   * This method allows base implementations to add general access restrictions
   * that should apply to all extending block plugins.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session for which to check access.
   *
   * @return bool
   *   TRUE if the block should be shown, or FALSE otherwise.
   */
  public function access(AccountInterface $account = NULL);

  /**
   * Builds and returns the renderable array for this display plugin.
   *
   * @return array
   *   A renderable array representing the content of the embedded entity.
   */
  public function build();

  /**
   * Returns the configuration form elements specific to this block plugin.
   *
   * Blocks that need to add form elements to the normal block configuration
   * form should implement this method.
   *
   * @param array $form
   *   The form definition array for the block configuration form.
   * @param array $form_state
   *   An array containing the current state of the configuration form.
   *
   * @return array $form
   *   The renderable form array representing the entire configuration form.
   */
  //public function settingsForm($form, &$form_state);

  /**
   * Adds block type-specific validation for the block form.
   *
   * Note that this method takes the form structure and form state arrays for
   * the full block configuration form as arguments, not just the elements
   * defined in BlockPluginInterface::blockForm().
   *
   * @param array $form
   *   The form definition array for the full block configuration form.
   * @param array $form_state
   *   An array containing the current state of the configuration form.
   *
   * @see \Drupal\entity_embed\BlockPluginInterface::blockForm()
   * @see \Drupal\entity_embed\BlockPluginInterface::blockSubmit()
   */
  //public function settingsValidate($form, &$form_state);

  /**
   * Adds block type-specific submission handling for the block form.
   *
   * Note that this method takes the form structure and form state arrays for
   * the full block configuration form as arguments, not just the elements
   * defined in BlockPluginInterface::blockForm().
   *
   * @param array $form
   *   The form definition array for the full block configuration form.
   * @param array $form_state
   *   An array containing the current state of the configuration form.
   *
   * @see \Drupal\entity_embed\BlockPluginInterface::blockForm()
   * @see \Drupal\entity_embed\BlockPluginInterface::blockValidate()
   */
  //public function settingsSubmit($form, &$form_state);

}
