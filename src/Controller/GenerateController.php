<?php

declare(strict_types=1);

namespace Drupal\metatag_ai_generate\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\metatag_ai_generate\Service\MetaDescriptionGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for AJAX meta description generation.
 */
class GenerateController extends ControllerBase {

  /**
   * The meta description generator service.
   *
   * @var \Drupal\metatag_ai_generate\Service\MetaDescriptionGeneratorInterface
   */
  protected MetaDescriptionGeneratorInterface $generator;

  /**
   * Constructs a GenerateController object.
   *
   * @param \Drupal\metatag_ai_generate\Service\MetaDescriptionGeneratorInterface $generator
   *   The meta description generator service.
   */
  public function __construct(MetaDescriptionGeneratorInterface $generator) {
    $this->generator = $generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('metatag_ai_generate.meta_description_generator')
    );
  }

  /**
   * Generate meta description via AJAX.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response with modal dialog.
   */
  public function generate(Request $request): AjaxResponse {
    $response = new AjaxResponse();

    // Get the node ID from the request.
    $node_id = $request->query->get('node_id');

    // Handle 'new' node case (node creation form).
    if ($node_id === 'new' || empty($node_id)) {
      $content = $this->buildErrorContent($this->t('Cannot generate meta description for unsaved content. Please save the node first.'));
      $response->addCommand(new OpenModalDialogCommand(
        $this->t('Generation Error'),
        $content,
        $this->getModalOptions()
      ));
      return $response;
    }

    // Load the node.
    try {
      $node = $this->entityTypeManager()->getStorage('node')->load($node_id);
      if (!$node) {
        throw new \Exception('Node not found');
      }
    }
    catch (\Exception $e) {
      $content = $this->buildErrorContent($this->t('Error loading node: @error', ['@error' => $e->getMessage()]));
      $response->addCommand(new OpenModalDialogCommand(
        $this->t('Generation Error'),
        $content,
        $this->getModalOptions()
      ));
      return $response;
    }

    // Generate meta description via LLM.
    $result = $this->generator->generate($node);

    if ($result->isSuccess()) {
      // Build modal with generated text.
      $content = $this->buildPreviewContent($result->getDescription());
      $response->addCommand(new OpenModalDialogCommand(
        $this->t('AI Generated Description'),
        $content,
        $this->getModalOptions()
      ));
    }
    else {
      // Build error modal.
      $content = $this->buildErrorContent($result->getError());
      $response->addCommand(new OpenModalDialogCommand(
        $this->t('Generation Error'),
        $content,
        $this->getModalOptions()
      ));
    }

    return $response;
  }

  /**
   * Builds the preview content render array.
   *
   * @param string $generated_text
   *   The generated meta description text.
   *
   * @return array
   *   The render array for the modal content.
   */
  protected function buildPreviewContent(string $generated_text): array {
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['metatag-ai-preview-container']],
      '#cache' => [
        'max-age' => 0,
        'contexts' => ['url.query_args:node_id'],
      ],
      'textarea' => [
        '#type' => 'textarea',
        '#attributes' => [
          'id' => 'metatag-ai-generated-text',
          'class' => ['metatag-ai-textarea'],
          'aria-label' => $this->t('Generated meta description'),
        ],
        '#value' => $generated_text,
      ],
      'counter' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'id' => 'metatag-ai-char-count',
          'role' => 'status',
          'aria-live' => 'polite',
        ],
        '#value' => '0 characters',
      ],
      'info' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => ['class' => ['metatag-ai-target-info']],
        '#value' => $this->t('Optimal length: 155-160 characters (visible on all devices). Up to 200 characters accepted.'),
      ],
      'actions' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['form-actions'],
          'role' => 'group',
          'aria-label' => $this->t('Actions'),
        ],
        'approve' => [
          '#type' => 'html_tag',
          '#tag' => 'button',
          '#attributes' => [
            'type' => 'button',
            'class' => ['button', 'button--primary'],
            'data-action' => 'approve',
            'aria-label' => $this->t('Approve and use this description'),
          ],
          '#value' => $this->t('Approve'),
        ],
        'regenerate' => [
          '#type' => 'html_tag',
          '#tag' => 'button',
          '#attributes' => [
            'type' => 'button',
            'class' => ['button'],
            'data-action' => 'regenerate',
            'aria-label' => $this->t('Generate a new description'),
          ],
          '#value' => $this->t('Regenerate'),
        ],
        'reject' => [
          '#type' => 'html_tag',
          '#tag' => 'button',
          '#attributes' => [
            'type' => 'button',
            'class' => ['button'],
            'data-action' => 'reject',
            'aria-label' => $this->t('Reject and close dialog'),
          ],
          '#value' => $this->t('Reject'),
        ],
      ],
      '#attached' => [
        'library' => ['metatag_ai_generate/preview-modal'],
      ],
    ];
  }

  /**
   * Builds the error content render array.
   *
   * @param string|\Drupal\Component\Render\MarkupInterface $error_message
   *   The error message to display.
   *
   * @return array
   *   The render array for error display.
   */
  protected function buildErrorContent(string|\Stringable $error_message): array {
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['metatag-ai-error-container']],
      '#cache' => [
        'max-age' => 0,
      ],
      'message' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => ['messages', 'messages--error'],
          'role' => 'alert',
        ],
        '#value' => $error_message,
      ],
    ];
  }

  /**
   * Gets the modal dialog options.
   *
   * Buttons are rendered in the content with form-actions class,
   * so Drupal's dialog.ajax.js prepareDialogButtons() will move them
   * to the button pane automatically.
   *
   * @return array
   *   The modal options array.
   */
  protected function getModalOptions(): array {
    return [
      'width' => '600',
      'classes' => [
        'ui-dialog' => 'metatag-ai-modal',
      ],
      // Let prepareDialogButtons handle button creation from form-actions.
      'drupalAutoButtons' => TRUE,
    ];
  }

}
