<?php

namespace Drupal\portada\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the Taxonomia portada entity.
 *
 * @ConfigEntityType(
 *   id = "taxonomia_portada",
 *   label = @Translation("Listados de portada"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\portada\TaxonomiaPortadaListBuilder",
 *     "form" = {
 *       "add" = "Drupal\portada\Form\TaxonomiaPortadaForm",
 *       "edit" = "Drupal\portada\Form\TaxonomiaPortadaForm",
 *       "delete" = "Drupal\portada\Form\TaxonomiaPortadaDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\portada\TaxonomiaPortadaHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "taxonomia_portada",
 *   admin_permission = "edit portada",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "weight" = "weight"
 *   },
 *   config_export = {
 *     "id",
 *     "active",
 *     "ad_middle",
 *     "ad_end",
 *     "ad_end_responsive",
 *     "weight",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/taxonomia_portada/{taxonomia_portada}",
 *     "add-form" = "/admin/structure/taxonomia_portada/add",
 *     "edit-form" = "/admin/structure/taxonomia_portada/{taxonomia_portada}/edit",
 *     "delete-form" = "/admin/structure/taxonomia_portada/{taxonomia_portada}/delete",
 *     "collection" = "/admin/structure/taxonomia_portada"
 *   }
 * )
 */
class TaxonomiaPortada extends ConfigEntityBase implements TaxonomiaPortadaInterface {

  /**
   * The Taxonomia portada ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Taxonomia portada label.
   *
   * @var string
   */
  protected $label;

  /**
   * If tha taxonomy list is active.
   *
   * @var int
   */
  protected $active;

  /**
   * Ad code in list.
   *
   * @var string
   */
  protected $ad_middle;

  /**
   * Ad code after list.
   *
   * @var string
   */
  protected $ad_end;

  /**
   * Ad code after list.
   *
   * @var string
   */
  protected $ad_end_responsive;

  /**
   * The weight of this role in administrative listings.
   *
   * @var int
   */
  protected $weight;

  /**
   * {@inheritdoc}
   */
  public function IsActive() {
    return $this->get('active');
  }

  /**
   * {@inheritdoc}
   */
  public function getAdMiddle() {
    return $this->get('ad_middle');
  }

  /**
   * {@inheritdoc}
   */
  public function getAdEnd() {
    return $this->get('ad_end');
  }

  /**
   * {@inheritdoc}
   */
  public function getAdEndResponsive() {
    return $this->get('ad_end_responsive');
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->get('weight');
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->set('weight', $weight);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageInterface $storage, array &$entities) {
    parent::postLoad($storage, $entities);
    // Sort the queried payment by their weight.
    // See \Drupal\Core\Config\Entity\ConfigEntityBase::sort().
    uasort($entities, 'static::sort');
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    if (!isset($this->weight) && ($entities = $storage->loadMultiple())) {
      // Set a role weight to make this new role last.
      $max = array_reduce($entities, function ($max, $entity) {
        return $max > $entity->weight ? $max : $entity->weight;
      });
      $this->weight = $max + 1;
    }
  }

}
