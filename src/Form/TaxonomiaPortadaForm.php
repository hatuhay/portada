<?php

namespace Drupal\portada\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TaxonomiaPortadaForm.
 */
class TaxonomiaPortadaForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $vid = 'tags';

    $taxonomia_portada = $this->entity;
    $form['id'] = [
      '#type' => 'select',
      '#title' => $this->t('Categoría'),
      '#options' => get_taxonomy_terms($vid),
      '#default_value' => $taxonomia_portada->id(),
      '#disabled' => !$taxonomia_portada->isNew(),
    ];
    $form['active'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Active'),
      '#default_value' => $taxonomia_portada->isNew() ? TRUE : $taxonomia_portada->IsActive(),
    );
    $form['ad_middle'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Publicidad Interna'),
      '#rows' => '15',
      '#rezisable' => 'both',
      '#default_value' => $taxonomia_portada->getAdMiddle(),
    );
    $form['ad_end'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Publicidad Larga Arriba'),
      '#rows' => '15',
      '#rezisable' => 'both',
      '#default_value' => $taxonomia_portada->getAdEnd(),
    );
    $form['ad_end_responsive'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Publicidad Final Arriba Responsive'),
      '#rows' => '15',
      '#rezisable' => 'both',
      '#default_value' => $taxonomia_portada->getAdEndResponsive(),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $taxonomia_portada = $this->entity;
    $key = $form_state->getValue('id');
    $val = $form['id']['#options'][$key];
    $taxonomia_portada->set('label', $val);

    $status = $taxonomia_portada->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created the %label Taxonomia portada.', [
          '%label' => $taxonomia_portada->label(),
        ]));
        break;

      default:
      $this->messenger()->addStatus($this->t('Saved the %label Taxonomia portada.', [
          '%label' => $taxonomia_portada->label(),
        ]));
    }
    $form_state->setRedirectUrl($taxonomia_portada->toUrl('collection'));
  }

}
