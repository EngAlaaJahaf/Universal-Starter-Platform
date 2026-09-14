<?php

require_once __DIR__ . '/helpers.php';

class Controller
{
    protected function view($view, array $data = array())
    {
        $viewFile = dirname(__DIR__) . '/views/' . ltrim($view, '/') . '.php';

        if (!is_file($viewFile)) {
            throw new RuntimeException('View not found: ' . $view);
        }

        extract($data, EXTR_SKIP);
        require $viewFile;
    }

    protected function render($view, array $data = array())
    {
        $this->view($view, $data);
    }

    protected function redirect($url, $statusCode = 302)
    {
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $url = app_url($url);
        }

        header('Location: ' . $url, true, $statusCode);
        exit;
    }
}
