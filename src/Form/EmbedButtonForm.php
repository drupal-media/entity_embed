<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Form\EmbedButtonForm.
 */

namespace Drupal\entity_embed\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EmbedButtonForm extends EntityForm {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The entity query factory service.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Constructs a new EmbedButtonForm.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   */
  public function __construct(QueryFactory $entity_query, EntityManagerInterface $entity_manager) {
    $this->entityQuery = $entity_query;
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $embed_button = $this->entity;

    // Get default for button image. If its uuid is set, get the id of the file
    // to be used as default in the form.
    $button_icon = NULL;
    if ($embed_button->button_icon_uuid) {
      $file = $this->entityManager->loadEntityByUuid('file', $embed_button->button_icon_uuid);
      $button_icon = array($file->id());
    }

    $file_scheme = \Drupal::config('entity_embed.settings')->get('file_scheme');
    $upload_directory = \Drupal::config('entity_embed.settings')->get('upload_directory');
    $upload_location = $file_scheme . '://' . $upload_directory . '/';

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $embed_button->label(),
      '#description' => $this->t("Label for the Embed Button."),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $embed_button->id(),
      '#machine_name' => array(
        'exists' => array($this, 'exists'),
      ),
      '#disabled' => !$embed_button->isNew(),
    );
    $form['entity_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Entity type'),
      '#options' => $this->getFilteredEntityTypes(),
      '#default_value' => $embed_button->entity_type,
      '#description' => $this->t("Entity type for which this button is to enabled."),
      '#required' => TRUE,
      '#ajax' => array(
        'callback' => array($this, 'updateEntityTypeDependentFields'),
        'effect' => 'fade',
      ),
    );
    $form['entity_type_bundles'] = array(
      '#type' => 'checkboxes',
      '#default_value' => $embed_button->entity_type_bundles ?: array(),
      '#prefix' => '<div id="bundle-entity-type-wrapper">',
      '#suffix' => '</div>',
    );
    $form['button_label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Button Label'),
      '#maxlength' => 255,
      '#default_value' => $embed_button->button_label,
      '#description' => $this->t("Label for the button to be shown in CKEditor toolbar."),
      '#required' => TRUE,
    );
    $form['button_icon'] = array(
      '#title' => $this->t('Button image'),
      '#type' => 'managed_file',
      '#description' => $this->t("Image for the button to be shown in CKEditor toolbar. Leave empty to use the default Entity icon."),
      '#upload_location' => $upload_location,
      '#default_value' => $button_icon,
      '#upload_validators' => array(
        'file_validate_extensions' => array('gif png jpg jpeg'),
        'file_validate_image_resolution' => array('16x16'),
      ),
    );
    $form['display_plugins'] = array(
      '#type' => 'checkboxes',
      '#default_value' => $embed_button->display_plugins ?: array(),
      '#prefix' => '<div id="display-plugins-wrapper">',
      '#suffix' => '</div>',
    );

    $entity_type_id = $form_state->getValue('entity_type') ?: $embed_button->entity_type;
    if ($entity_type_id) {
      $entity_type = $this->entityManager->getDefinition($entity_type_id);
      // If the entity has bundles, allow option to restrict to bundle(s).
      if ($entity_type->hasKey('bundle')) {
        foreach ($this->entityManager->getBundleInfo($entity_type_id) as $bundle_id => $bundle_info) {
          $bundle_options[$bundle_id] = $bundle_info['label'];
        }

        // Hide selection if there's just one option, since that's going to be
        // allowed in either case.
        if (count($bundle_options) > 1) {
          $form['entity_type_bundles'] += array(
            '#title' => $entity_type->getBundleLabel() ?: $this->t('Bundles'),
            '#options' => $bundle_options,
            '#description' => $this->t('If none are selected, all are allowed.'),
          );
        }
      }

      // Allow option to limit display plugins.
      $form['display_plugins'] += array(
        '#title' => $this->t('Allowed display plugins'),
        '#options' => \Drupal::service('plugin.manager.entity_embed.display')->getDefinitionOptionsForEntityType($entity_type_id),
        '#description' => $this->t('If none are selected, all are allowed. Note that these are the plugins which are allowed for this entity type, all of these might not be available for the selected entity.'),
      );
    }
    // Set options to an empty array if it hasn't been set so far.
    if (!isset($form['entity_type_bundles']['#options'])) {
      $form['entity_type_bundles']['#options'] = array();
    }
    if (!isset($form['display_plugins']['#options'])) {
      $form['display_plugins']['#options'] = array();
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $embed_button = $this->entity;

    $status = $embed_button->save();
    if ($status) {
      drupal_set_message($this->t('Saved the %label Embed Button.', array(
        '%label' => $embed_button->label(),
      )));
      $form_state->setRedirect('embed_button.list');
    }
    else {
      drupal_set_message($this->t('The %label Embed Button was not saved.', array(
        '%label' => $embed_button->label(),
      )), 'error');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $button_icon_fid = $form_state->getValue(array('button_icon', '0'));
    // If a file was uploaded to be used as the icon, get its UUID to be stored
    // in the config entity.
    if (!empty($button_icon_fid) && $file = $this->entityManager->getStorage('file')->load($button_icon_fid)) {
      $button_icon_uuid = $file->uuid();
    }
    else {
      $button_icon_uuid = NULL;
    }

    // Set all form values in the entity except the button icon since it is a
    // managed file element in the form but we want its UUID instead, which
    // will be separately set later.
    foreach ($values as $key => $value) {
      if ($key != 'button_icon') {
        $entity->set($key, $value);
      }
    }

    // Set the UUID of the button icon.
    $entity->set('button_icon_uuid', $button_icon_uuid);
  }

  /**
   * Builds a list of entity type labels suitable for embed button options.
   *
   * Configuration entity types without a view builder are filtered out while
   * all other entity types are kept.
   *
   * @return array
   *   An array of entity type labels, keyed by entity type name.
   */
  protected function getFilteredEntityTypes() {
    $options = array();
    $definitions = $this->entityManager->getDefinitions();

    foreach ($definitions as $entity_type_id => $definition) {
      // Don't include configuration entities which do not have a view builder.
      if ($definition->getGroup() != 'configuration' || $definition->hasViewBuilderClass()) {
        $options[$definition->getGroupLabel()][$entity_type_id] = $definition->getLabel();
      }
    }

    // Group entity type labels.
    foreach ($options as &$group_options) {
      // Sort the list alphabetically by group label.
      array_multisort($group_options, SORT_ASC, SORT_NATURAL);
    }

    // Make sure that the 'Content' group is situated at the top.
    $content = $this->t('Content', array(), array('context' => 'Entity type group'));
    $options = array($content => $options[$content]) + $options;

    return $options;
  }

  /**
   * Determines if the button already exists.
   *
   * @param string $button_id
   *   The button ID.
   *
   * @return bool
   *   TRUE if the button exists, FALSE otherwise.
   */
  public function exists($button_id) {
    $entity = $this->entityQuery->get('embed_button')
      ->condition('id', $button_id)
      ->execute();
    return (bool) $entity;
  }

  /**
   * Ajax callback to update the form fields which depend on entity type.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @response AjaxResponse
   *   Ajax response with updated options for entity type bundles.
   */
  public function updateEntityTypeDependentFields(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // Update options for entity type bundles.
    $response->addCommand(new ReplaceCommand(
      '#bundle-entity-type-wrapper',
      $form['entity_type_bundles']
    ));

    // Update options for display plugins.
    $response->addCommand(new ReplaceCommand(
      '#display-plugins-wrapper',
      $form['display_plugins']
    ));

    return $response;
  }
}
