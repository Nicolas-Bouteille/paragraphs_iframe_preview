<?php

namespace Drupal\paragraphs_iframe_preview\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\Core\Template\TwigEnvironment;

/**
 * Paragraphs iframe preview handler
 */
class ParagraphsIframePreviewController extends ControllerBase {

  /**
   * Retrieves the temporary paragraph from PrivateTempStoreFactory and renders it with TwigEnvironment::renderInline
   *
   * @param string $paragraph_uuid
   *
   * @return array
   */
  public function paragraphIframePreview(string $paragraph_uuid) {
    /** @var PrivateTempStoreFactory $tempstore_factory */
    $tempstore_factory = \Drupal::service('tempstore.private'); // table key_value_expire
    $paragraph_preview_store = $tempstore_factory->get('paragraph_preview'); // collection = tempstore.private.paragraph_preview
    $paragraph = $paragraph_preview_store->get($paragraph_uuid); // name = uuid
    if ( ! $paragraph instanceof ParagraphInterface) {
      return [
        "#markup" => $this->t("An error occured while trying to preview this component.")
      ];
    }
    /** @var \Drupal\Core\Template\TwigEnvironment $twig */
    $twig = \Drupal::service('twig');
    $output = $twig->renderInline("{{ paragraph | view }}", ['paragraph' => $paragraph]);
    return [
      "#markup" => Markup::create($output),
    ];
  }
}
