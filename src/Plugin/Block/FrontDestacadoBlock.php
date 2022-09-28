<?php

namespace Drupal\portada\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'FrontDestacadoBlock' block.
 *
 * @Block(
 *  id = "front_destacado_block",
 *  admin_label = @Translation("Front destacado block"),
 * )
 */
class FrontDestacadoBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
            'numero_noticias' => 7,
          ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['numero_noticias'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Número de Noticias'),
      '#description' => $this->t('Número de noticias a mostrar en vista'),
      '#default_value' => $this->configuration['numero_noticias'],
      '#weight' => '0',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['numero_noticias'] = $form_state->getValue('numero_noticias');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['front_destacado_block'] = self::RenderDestacado($this->configuration['numero_noticias']);
//    $build['front_destacado_block']['#cache']['max-age'] = 0;
    $build['front_destacado_block']['#cache']['tags'][] = "node_list";

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function RenderDestacado( $num = 7 ) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'article')
      ->condition('status', 1)
      ->condition('field_destacado_portada', TRUE)
      ->range(0, $num)
      ->sort('sticky' , 'DESC') 
      ->sort('created' , 'DESC'); 
    $group = NULL;
    $nids = $query->execute();
    if ( !is_null($nids) ) {
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $nodes = $node_storage->loadMultiple($nids);
      $list = self::RenderList($nodes, $group, 'desatacado_principal', 'destacado_pequeno');
      return [
        '#theme' => 'news_destacado_front',
        '#items' => $list,
      ];
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function RenderList($nodes, $group, $view_mode_destacado = 'card_destacado', $view_mode = 'teaser') {
    $i = 0;
    $elements = [];

    foreach ($nodes as $nid => $node) {
      switch ($i) {
        case 0:
          $view = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, $view_mode_destacado);
          $elements[] = [
            '#theme' => 'news_item',
            '#classes' => 'col-12 col-sm-12 col-md-12 col-lg-9 news-destacado-destacado',
            '#children' => render($view),
          ];
          break;
        default:
          $view = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, $view_mode);
          $elements[] = [
            '#theme' => 'news_item',
            '#classes' => 'col-6 col-md-4 col-lg-3 news-destacado-item',
            '#children' => render($view),
          ];
          break;
      }
      $i++;
    }
    return $elements;
  }

}
