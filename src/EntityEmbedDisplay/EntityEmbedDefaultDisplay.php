<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDefaultDisplay.
 */

namespace Drupal\entity_embed\EntityEmbedDisplay;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default embed display, which renders the entity using entity_view().
 *
 * @EntityEmbedDisplay(
 *   id = "default",
 *   label = @Translation("Default")
 * )
 *
 * @todo Should this use a derivative? http://cgit.drupalcode.org/page_manager/tree/src/Plugin/Deriver/EntityViewDeriver.php
 */
class EntityEmbedDefaultDisplay extends EntityEmbedDisplayBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   *
   * @param EntityManagerInterface $entity_manager
   *   Needed for displaying entities.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'view_mode' => 'embed',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['view_mode'] = array(
      '#type' => 'select',
      '#title' => t('View mode'),
      '#options' => $this->entityManager->getDisplayModeOptions('view_mode', $this->getAttributeValue('entity-type')),
      '#default_value' => $this->getConfigurationValue('view_mode'),
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity = $this->getContextValue('entity');
    $view_mode = $this->getConfigurationValue('view_mode');
    $langcode = $this->getAttributeValue('langcode');

    // These two lines are essentially entity_view() without the $reset variable
    // which is not needed here. The injected entityManager property is used
    // instead of entity_view() for eventual unit-testability.
    $render_controller = $this->entityManager->getViewBuilder($entity->getEntityTypeId());
    $build = $render_controller->view($entity, $view_mode, $langcode);

    // Hide entity links by default.
    if (isset($build['links'])) {
      $build['links']['#access'] = FALSE;
    }

    return $build;
  }
}
