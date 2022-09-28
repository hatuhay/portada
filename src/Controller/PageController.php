<?php

namespace Drupal\portada\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class PageController.
 */
class PageController extends ControllerBase {

  /**
   * Front.
   *
   * @return string
   *   Return front page.
   */
  public function front() {
    return self::RenderFront();
  }

  /**
   * Noticias.
   *
   * @return string
   *   Return noticias page.
   */
  public function noticias() {
    $group = NULL;
    $name = NULL;
    $term_id = NULL;
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'article')
      ->condition('status', 1)
      ->range(0,200)
      ->sort('created' , 'DESC'); 

    $nids = $query->execute();
    if ( !is_null($nids) ) {
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $nodes = $node_storage->loadMultiple($nids);
      $list = self::RenderList($nodes, $group);
      return [
        '#theme' => 'news_list',
        '#items' => $list,
        '#title' => $name,
        '#link' => $term_id,
      ];
    }
    else {
      throw new NotFoundHttpException();
    }
  }

  /**
   * Videos.
   *
   * @return string
   *   Return videos page.
   */
  public function videos() {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'article')
      ->condition('status', 1)
      ->condition('field_es_video', TRUE)
      ->range(0,200)
      ->sort('created' , 'DESC'); 

    $nids = $query->execute();
    if ( !is_null($nids) ) {
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $nodes = $node_storage->loadMultiple($nids);
      $list = self::RenderVideoList($nodes, $group);
      return [
        '#theme' => 'news_list',
        '#items' => $list,
        '#title' => $name,
        '#link' => $term_id,
      ];
    }
    else {
      throw new NotFoundHttpException();
    }
  }
  
  /**
   * Taxonomy.
   *
   * @return string
   *   Return taxonomy page.
   */
  public function taxonomy($term_id) {
    if (!is_numeric($term_id)) {
      // We will just show a standard "access denied" page in this case.
      throw new AccessDeniedHttpException();
    }
    return self::RenderTaxonomy($term_id);
  }

  /**
   * Estadistica.
   *
   * @return string
   *   Return estadictica iframe.
   */
  public function estadisticas($option, $view, $p) {
    if (!is_numeric($p)) {
      // We will just show a standard "access denied" page in this case.
      throw new AccessDeniedHttpException();
    }

    $render_array = [
      '#theme' => 'estadisticas',
      '#option' => $option,
      '#view' => $view,
      '#p' => $p,
      '#type' => NULL,
    ];
    return $render_array;
  }

  public function estadisticas_extra($option, $view, $p, $type, $id) {
    if (!is_numeric($p)) {
      // We will just show a standard "access denied" page in this case.
      throw new AccessDeniedHttpException();
    }

    $render_array = [
      '#theme' => 'estadisticas',
      '#option' => $option,
      '#view' => $view,
      '#p' => $p,
      '#type' => $type,
      '#id' => $id,
    ];
    return $render_array;
  }

  /**
   * {@inheritdoc}
   */
  public function RenderFront() {
    // Get groups from config entity 'taxonomia_portada'
    $groups = \Drupal::entityTypeManager()->getStorage('taxonomia_portada')->loadMultiple();
    $list = [];
    $render_array = [];
    $i = 0;

    // Iterate over group of taxonomies
    foreach($groups as $term_id => $group) {
      if ($i==2) {
        $query = \Drupal::entityQuery('node')
          ->condition('status', 1)
          ->condition('type', 'article')
          ->condition('field_es_video', TRUE)
          ->range(0,7)
          ->sort('created' , 'DESC');

        $nids = $query->execute();
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $nodes = $node_storage->loadMultiple($nids);
        $list = self::RenderVideo($nodes, NULL);
        $render_array[] = [
          '#theme' => 'videos_list',
          '#items' => $list,
          '#title' => 'Ovación TV',
          '#link' => 'videos', 
          '#ad' => '<div class="block-simple-ad-code pb-3">
            <!-- Revive Adserver Etiqueta JS asincrónica - Generated with Revive Adserver v4.1.4 -->
            <ins data-revive-zoneid="190" data-revive-id="2976ba624bc75843d4901f7507fb71cb"></ins></div>',
          '#admiddle' => '<div class="block-simple-ad-code">
            <!-- Revive Adserver Etiqueta JS asincrónica - Generated with Revive Adserver v4.1.4 -->
            <ins data-revive-zoneid="192" data-revive-id="2976ba624bc75843d4901f7507fb71cb"></ins></div>',
        ];
      }
      // Grab nodes from specify taxonomy
      if ($group != NULL) {
        $ad = $group->getAdEnd();
        $ad_responsive = $group->getAdEndResponsive() ? $group->getAdEndResponsive() : $ad;
      }
      $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'article')
        ->condition('field_tags.entity', $term_id)
        ->range(0,9)
        ->sort('created' , 'DESC');

      $nids = $query->execute();
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $nodes = $node_storage->loadMultiple($nids);
      $list = self::RenderList($nodes, $group);
      $render_array[] = [
        '#theme' => 'news_list',
        '#items' => $list,
        '#title' => $group->label(),
        '#link' => $group->id(),
        '#ad' => $ad ? '<div class="d-none d-lg-block block-simple-ad-code pb-3"><div class="add-inner">' . $ad . '</div></div>' : NULL,
        '#ad_responsive' => $ad_responsive ? '<div class="d-lg-none block-simple-ad-code pb-3"><div class="add-inner">' . $ad_responsive . '</div></div>' : NULL,
      ];
      $i++;
    }
    return  [
      '#theme' => 'news_group',
      '#children' => $render_array,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function RenderTaxonomy($term_id) {
    $term = Term::load($term_id);
    $name = $term->getName();
    $group = NULL;
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'article')
      ->condition('status', 1)
      ->condition('field_tags.entity', $term_id)
      ->range(0,200)
      ->sort('created' , 'DESC'); 

    $nids = $query->execute();
    if ( !is_null($nids) ) {
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $nodes = $node_storage->loadMultiple($nids);
      $list = self::RenderList($nodes, $group);
      return [
        '#theme' => 'news_list',
        '#items' => $list,
        '#title' => $name,
        '#link' => $term_id,
      ];
    }
    else {
      throw new NotFoundHttpException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function RenderList($nodes, $group, $view_mode_destacado = 'card_destacado', $view_mode = 'teaser') {
    $i = 0;
    $elements = [];
    $count = count($nodes);

    foreach ($nodes as $nid => $node) {
      $field_es_video = $node->get('field_es_video')->getValue();
      if (isset($field_es_video[0])) {
        $video = $field_es_video[0]['value'];
        if ($view_mode == 'teaser') {
          $destacado = $video ? 'video_destacado' : $view_mode_destacado;
          $listado = $video ? 'video_card_image_top' : $view_mode;
        }
      } else {
        $destacado = $view_mode_destacado;
        $listado = $view_mode;
      }
      switch ($i) {
        case 0:
          $view = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, $destacado);
          $elements[] = [
            '#theme' => 'news_item',
            '#classes' => 'col-12 col-lg-8 news-portada-item-destacado item-' . $i,
            '#children' => render($view),
          ];
          break;
        case 3:
          if ($group != NULL) {
            $ad = $group->getAdMiddle();
            if ( is_string($ad) && strlen($ad) ) {
              $elements[]['#markup'] = '<div class="block-simple-ad-code col-12 col-md-6 col-lg-4 news-portada-item"><div class="add-inner card">' . $ad . '</div></div>';
              $i++;
            }
          }
        default:
          $view = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, $listado);
          $classes = $i < 6 ? 'col-12 col-md-6 col-lg-4 news-portada-item item-' . $i : 'col-12 col-md-6 col-lg-4 news-portada-item item-' . $i . ' d-none d-sm-block';
          $elements[] = [
            '#theme' => 'news_item',
            '#classes' => $classes,
            '#children' => render($view),
          ];
          break;
      }
      $i++;
      if ($i >= $count && $count < 20) break;
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function RenderVideo($nodes, $group, $view_mode_destacado = 'card_destacado', $view_mode = 'teaser') {
    $i = 0;
    $elements = [];
    $count = count($nodes);

    foreach ($nodes as $nid => $node) {
      $field_es_video = $node->get('field_es_video')->getValue();
      if (isset($field_es_video[0])) {
        $video = $field_es_video[0]['value'];
        if ($view_mode == 'teaser') {
          $destacado = $video ? 'video_destacado' : $view_mode_destacado;
          $listado = $video ? 'video_listado' : $view_mode;
        }
      } else {
        $destacado = $view_mode_destacado;
        $listado = $view_mode;
      }
      switch ($i) {
        case 0:
          $view = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, $destacado);
          $elements[] = [
            '#theme' => 'news_item',
            '#classes' => 'col-sm-6 col-md-8 news-portada-item-destacado item-' . $i,
            '#children' => render($view),
          ];
          break;
        default:
          $view = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, $listado);
          $elements[] = [
            '#theme' => 'news_item',
            '#classes' => 'col-12 col-md-6 col-lg-4 news-portada-item item-' . $i,
            '#children' => render($view),
          ];
          break;
      }
      $i++;
      if ($i >= $count && $count < 20) break;
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function RenderVideoList($nodes, $group, $view_mode_destacado = 'card_destacado', $view_mode = 'teaser') {
    $i = 0;
    $elements = [];
    $count = count($nodes);

    foreach ($nodes as $nid => $node) {
      $field_es_video = $node->get('field_es_video')->getValue();
      if (isset($field_es_video[0])) {
        $video = $field_es_video[0]['value'];
        if ($view_mode == 'teaser') {
          $destacado = $video ? 'video_destacado' : $view_mode_destacado;
          $listado = $video ? 'video_card_image_top' : $view_mode;
        }
      } else {
        $destacado = $view_mode_destacado;
        $listado = $view_mode;
      }
      switch ($i) {
        case 0:
          $view = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, $destacado);
          $elements[] = [
            '#theme' => 'news_item',
            '#classes' => 'col-12 col-lg-8 news-portada-item-destacado item-' . $i,
            '#children' => render($view),
          ];
          break;
        default:
          $j = $i % 6;
          switch ($j) {
            case 5:
              $elements[]['#markup'] = '<div class="col-12 col-md-6 col-lg-4 news-portada-item block-simple-ad-code"><div class="add-inner card">
                <ins data-revive-zoneid="179" data-revive-id="2976ba624bc75843d4901f7507fb71cb"></ins></div></div>';
              $i++;
            default:
              $view = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, $listado);
              $elements[] = [
                '#theme' => 'news_item',
                '#classes' => 'col-12 col-md-6 col-lg-4 news-portada-item item-' . $i,
                '#children' => render($view),
              ];
              break;
          } 
      }
      $i++;
      if ($i >= $count && $count < 20) break;
    }
    return $elements;
  }
}

