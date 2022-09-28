<?php

namespace Drupal\portada\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\portada\Controller\PageController;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'CustomTaxonomyBlock' block.
 *
 * @Block(
 *  id = "custom_taxonomy_block",
 *  admin_label = @Translation("Custom taxonomy block"),
 * )
 */
class CustomTaxonomyBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['custom_taxonomy_block'] = self::RenderTaxonomy();
    $build['custom_taxonomy_block']['#cache']['contexts'][] = "route";
//    $build['custom_taxonomy_block']['#cache']['max-age'] = 0;
    $build['custom_taxonomy_block']['#cache']['tags'][] = "node_list";
    $build['custom_taxonomy_block']['#attached']['library'] = 'simple_ad_code/async';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function RenderTaxonomy() {
    $parameters = \Drupal::routeMatch()->getParameters()->all();
    if (isset($parameters['taxonomy_term'])) {
      $term = $parameters['taxonomy_term'];
      $term_id = $term->id();
      $query = \Drupal::entityQuery('node')
        ->condition('type', 'article')
        ->condition('status', 1)
        ->condition('field_tags.entity', $term_id)
        ->range(0,199)
        ->sort('created' , 'DESC'); 
      $group = NULL;
      $nids = $query->execute();
      if ( !is_null($nids) ) {
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $nodes = $node_storage->loadMultiple($nids);
        $list = self::RenderList($nodes, $group);
        return [
          '#theme' => 'news_taxonomy',
          '#items' => $list,
        ];
      }
      else {
        return "No hay resultados";
      }
    }
    else {
      return NULL;
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
        default:
          $j = $i % 6;
          switch ($j) {
            case 5:
              $elements[]['#markup'] = '<div class="col-12 col-md-6 col-lg-4 news-portada-item block-simple-ad-code"><div class="add-inner card">
                <ins data-revive-zoneid="179" data-revive-id="2976ba624bc75843d4901f7507fb71cb"></ins></div></div>';
              $i++;
              $count++;
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
