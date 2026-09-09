<?php

class HomeController extends Controller
{
    public function index()
    {
        $articleModel  = new Article();
        $categoryModel = new Category();
        $tutorialModel = new Tutorial();

        $perPage = (int) Settings::get('articles_per_page', 12);
        if ($perPage < 1) $perPage = 12;

        $pollModel     = new Poll();

        $userId = Auth::user()['id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ipHash = hash('sha256', $ip . $ua);

        $activePoll = null;
        if (Settings::get('enable_polls', '1') == '1') {
            $activePoll = $pollModel->getActiveFeatured($userId, $ipHash);
        }

        $this->view('home', array(
            'articles'   => $articleModel->getLatest($perPage),
            'popular'    => $articleModel->getPopular(5),
            'radar'      => $articleModel->getTrendingRadar(),
            'categories' => $categoryModel->getAll(),
            'tutorials'  => $tutorialModel->getPublished(6),
            'activePoll' => $activePoll,
        ));
    }
}
