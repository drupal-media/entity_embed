<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Form\EmbedButtonForm.
 */

namespace Drupal\entity_embed\Form;

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
    $button_icon = array();
    if ($embed_button->button_icon_uuid) {
      $file = $this->entityManager->loadEntityByUuid('file', $embed_button->button_icon_uuid);
      $button_icon = array($file->id());
    }

    $file_scheme = \Drupal::config('entity_embed.settings')->get('file_scheme');
    $upload_directory = \Drupal::config('entity_embed.settings')->get('upload_directory');
    $upload_location = $file_scheme . '://' . $upload_directory . '/';

    $entity_types = $this->entityManager->getEntityTypeLabels(TRUE);
    $filtered_entity_types = array();
    // Add all Content entites by default.
    $filtered_entity_types['Content'] = $entity_types['Content'];
    // Select only those config entities which have a view builder.
    $filtered_config_entities = array();
    foreach ($entity_types['Configuration'] as $entity_type => $label) {
      if ($this->entityManager->hasHandler($entity_type, 'view_builder')) {
        $filtered_config_entities[$entity_type] = $label;
      }
    }
    // Add a group for config entities, only if there's at least one config
    // entity with view builder.
    if ($filtered_config_entities) {
      $filtered_entity_types['Configuration'] = $filtered_config_entities;
    }

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
      '#options' => $filtered_entity_types,
      '#default_value' => $embed_button->entity_type,
      '#description' => $this->t("Entity type for which this button is to enabled."),
      '#required' => TRUE,
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
    $button_icons = $values['button_icon'];

    $button_icon_uuid = NULL;
    // If a file was uploaded to be used as the icon, get its UUID to be stored
    // in the config entity.
    if ($button_icons && $file = $this->entityManager->getStorage('file')->load($button_icons[0])) {
      $button_icon_uuid = $file->uuid();
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
}
