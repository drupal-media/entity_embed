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
use Drupal\entity_embed\EntityHelperTrait;
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
    $button_image = $embed_button->button_icon_uuid ? array($embed_button->button_icon_uuid) : array();
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
      '#options' => $this->entityManager->getEntityTypeLabels(TRUE),
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
    $form['button_icon_uuid'] = array(
      '#title' => $this->t('Button image'),
      '#type' => 'managed_file',
      '#description' => $this->t("Image for the button to be shown in CKEditor toolbar. Leave empty to use the default Entity icon."),
      '#upload_location' => $upload_location,
      '#default_value' => $button_image,
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

    // $embed_button->button_icon_uuid = count($embed_button->button_icon_uuid) ? $embed_button->button_icon_uuid[0] : NULL;
    if (count($embed_button->button_icon_uuid) && $file = entity_load('file', $embed_button->button_icon_uuid[0])) {
      $embed_button->button_icon_uuid = $file->uuid();
    }
    else {
      $embed_button->button_icon_uuid = NULL;
    }

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
