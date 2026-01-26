<?php

namespace Drupal\paragraphs_iframe_preview\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\Core\Template\TwigEnvironment;

class ParagraphsIframePreviewController extends ControllerBase {

  public function paragraphIframePreview(string $paragraph_uuid) {
    /** @var PrivateTempStoreFactory $tempstore_factory */
    $tempstore_factory = \Drupal::service('tempstore.private'); // from table key_value_expire, collection starts with tempstore.private.xxxxx
    $paragraph_preview_store = $tempstore_factory->get('paragraph_preview'); // collection = tempstore.private.paragraph_preview
    $paragraph = $paragraph_preview_store->get($paragraph_uuid); // where column name is the paragraph uuid
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
