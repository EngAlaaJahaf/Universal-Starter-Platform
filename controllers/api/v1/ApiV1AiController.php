<?php

require_once __DIR__ . '/ApiV1BaseController.php';
require_once __DIR__ . '/../../../core/AiTranslator.php';

class ApiV1AiController extends ApiV1BaseController
{
    /**
     * POST /api/v1/ai/translate
     * Transforms English tech news into high-grade journalistic Arabic
     */
    public function translate()
    {
        $this->requireScope('ai:translate');
        $body = $this->getJsonBody();

        $title = trim($body['title'] ?? ($body['title_en'] ?? ''));
        $content = trim($body['content'] ?? ($body['content_en'] ?? ($body['text'] ?? '')));

        if (empty($title) && empty($content)) {
            $this->jsonError('At least "title" or "content" is required.', 422);
        }

        $res = AiTranslator::translateArticle($title, $content);
        $this->jsonSuccess($res, ['source_lang' => 'en', 'target_lang' => 'ar']);
    }

    /**
     * GET /api/v1/ai/glossary
     * Returns the platform's standardized technical terminology dictionary
     */
    public function glossary()
    {
        $this->requireScope('ai:translate');
        $this->jsonSuccess(AiTranslator::$techGlossary, [
            'total_terms' => count(AiTranslator::$techGlossary),
            'description' => 'Official Arabic Tech News Translation Standard Terminology'
        ]);
    }
}
