<?php
/**
 * Web (public) routes — extracted from index.php (SH-08).
 * Executed in index.php scope where $router is defined.
 */

$router->get('/', 'HomeController@index');
$router->get('/articles', 'ArticleController@index');
$router->get('/article/{slug}', 'ArticleController@show');
$router->get('/p/{id}', 'ArticleController@shortlink');
$router->get('/category/{slug}', 'ArticleController@category');
$router->get('/search', 'SearchController@index');

// Tutorials & How-To Guides
$router->get('/tutorials', 'TutorialController@index');
$router->get('/tutorial/{slug}', 'TutorialController@show');
$router->get('/tutorials/{slug}', 'TutorialController@show');

// Live Blog Coverage
$router->get('/live-blog', 'LiveBlogController@index');
$router->get('/live-blog/{id}', 'LiveBlogController@show');
$router->get('/live-blog/{id}/poll', 'LiveBlogController@poll');
$router->get('/live-blog/{id}/updates', 'LiveBlogController@poll');
$router->get('/live-blog/{id}/chat-messages', 'LiveBlogController@poll');
$router->post('/live-blog/{id}/chat/send', 'LiveBlogController@sendChat');
$router->post('/live-blog/{id}/chat', 'LiveBlogController@sendChat');
$router->post('/live-blog/{id}/reaction', 'LiveBlogController@react');

// Series & Topic Hubs
$router->get('/series', 'SeriesController@index');
$router->get('/series/{slug}', 'SeriesController@show');

// Stories
$router->get('/stories', 'StoryController@index');
$router->get('/story/{id}', 'StoryController@show');

// Archive (الأخبار المتقادمة/المؤرشفة)
$router->get('/archive', 'ArchiveController@index');

// Interactions, Comments & Newsletter
$router->post('/comment/store', 'CommentController@store');
$router->post('/api/reaction/{id}/{type}', 'ReactionController@react');
$router->post('/newsletter/subscribe', 'NewsletterController@subscribe');
$router->get('/newsletter/unsubscribe/{token}', 'NewsletterController@unsubscribe');

// Interactive Polls
$router->post('/poll/vote', 'PollController@vote');
$router->get('/poll/active', 'PollController@active');

// AI Assistant Chat (RAG over published articles)
$router->post('/ai-assistant/ask', 'AiAssistantController@ask');
$router->post('/ai-assistant/reaction', 'AiAssistantController@reaction');
$router->get('/ai-assistant/pending', 'AiAssistantController@pending');

// Bookmarks & Push Notifications
$router->get('/bookmarks', 'BookmarkController@index');
$router->post('/push/subscribe', 'PushController@subscribe');

// Feeds & RSS
$router->get('/rss.xml', 'FeedController@rss');
$router->get('/rss', 'FeedController@rss');
$router->get('/feed', 'FeedController@rss');
$router->get('/feed/rss', 'FeedController@rss');
$router->get('/feed.xml', 'FeedController@rss');
$router->get('/feed/{slug}.xml', 'FeedController@rssByCategory');
$router->get('/feed/category/{slug}', 'FeedController@rssByCategory');
$router->get('/rss/category/{slug}', 'FeedController@rssByCategory');
$router->get('/feed/{slug}', 'FeedController@rssByCategory');

// Static Pages
$router->get('/privacy', 'PageController@privacy');
$router->get('/privacy-policy', 'PageController@privacy');
$router->get('/terms', 'PageController@terms');
$router->get('/terms-of-service', 'PageController@terms');
$router->get('/about', 'PageController@about');
$router->get('/about-us', 'PageController@about');
$router->get('/offline', 'PageController@offline');
$router->get('/page/{slug}', 'PageController@show');

// Sitemap, SEO & Monetization
$router->get('/sitemap.xml', 'SitemapController@index');
$router->get('/news-sitemap.xml', 'SitemapController@index');
$router->get('/robots.txt', 'SitemapController@robotsTxt');
$router->get('/ads.txt', 'SitemapController@adsTxt');

// Contact
$router->get('/contact', 'ContactController@show');
$router->get('/contact-us', 'ContactController@show');
$router->post('/contact', 'ContactController@send');
$router->post('/contact/send', 'ContactController@send');

// Auth, Password Recovery & Profile
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password/{token}', 'AuthController@showResetPassword');
$router->get('/reset-password', 'AuthController@showForgotPassword');
$router->post('/reset-password', 'AuthController@resetPassword');
$router->get('/profile', 'ProfileController@show');
$router->get('/profile/edit', 'ProfileController@edit');
$router->post('/profile/edit', 'ProfileController@update');
$router->post('/profile/update', 'ProfileController@update');
$router->post('/profile/change-password', 'ProfileController@changePassword');
$router->post('/profile/password', 'ProfileController@changePassword');
