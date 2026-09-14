<?php
/**
 * Admin panel routes — extracted from index.php (SH-08).
 * Executed in index.php scope where $router is defined.
 */

$router->get('/admin', 'DashboardController@index');
$router->get('/admin/dashboard', 'DashboardController@index');
$router->get('/admin/analytics', 'AnalyticsController@index');

// Articles Management
$router->get('/admin/articles', 'ArticlesController@index');
$router->get('/admin/articles/create', 'ArticlesController@create');
$router->post('/admin/articles/store', 'ArticlesController@store');
$router->get('/admin/articles/{id}/edit', 'ArticlesController@edit');
$router->post('/admin/articles/{id}/update', 'ArticlesController@update');
$router->post('/admin/articles/{id}/delete', 'ArticlesController@delete');
$router->post('/admin/articles/bulk-expire', 'ArticlesController@bulkExpire');
$router->post('/admin/articles/translate-preview', 'ArticlesController@translatePreview');
$router->post('/admin/articles/ai-generate-full', 'ArticlesController@aiGenerateFull');

// Categories Management
$router->get('/admin/categories', 'CategoriesController@index');
$router->get('/admin/categories/create', 'CategoriesController@create');
$router->post('/admin/categories/store', 'CategoriesController@store');
$router->get('/admin/categories/{id}/edit', 'CategoriesController@edit');
$router->post('/admin/categories/{id}/update', 'CategoriesController@update');
$router->post('/admin/categories/{id}/delete', 'CategoriesController@delete');

// Live Blog Moderation
$router->get('/admin/live-blog', 'AdminLiveBlogController@index');
$router->get('/admin/live-blog/create', 'AdminLiveBlogController@create');
$router->post('/admin/live-blog/store', 'AdminLiveBlogController@store');
$router->get('/admin/live-blog/{id}/entries', 'AdminLiveBlogController@entries');
$router->post('/admin/live-blog/entries/store', 'AdminLiveBlogController@storeEntry');
$router->post('/admin/live-blog/entries/{id}/update', 'AdminLiveBlogController@updateEntry');
$router->post('/admin/live-blog/entries/{id}/delete', 'AdminLiveBlogController@deleteEntry');
$router->post('/admin/live-blog/entries/{id}/toggle-pin', 'AdminLiveBlogController@togglePin');
$router->post('/admin/live-blog/chat/store', 'AdminLiveBlogController@storeChatMessage');
$router->post('/admin/live-blog/chat/delete', 'AdminLiveBlogController@deleteChatMessage');

// Interactive Polls Management (CRUD)
$router->get('/admin/polls', 'PollsController@index');
$router->get('/admin/polls/create', 'PollsController@create');
$router->post('/admin/polls/store', 'PollsController@store');
$router->get('/admin/polls/{id}/edit', 'PollsController@edit');
$router->post('/admin/polls/{id}/update', 'PollsController@update');
$router->post('/admin/polls/{id}/delete', 'PollsController@delete');
$router->post('/admin/polls/{id}/set-featured', 'PollsController@setFeatured');
$router->post('/admin/polls/{id}/reset-votes', 'PollsController@resetVotes');

// Tutorials Management
$router->get('/admin/tutorials', 'TutorialsController@index');
$router->get('/admin/tutorials/create', 'TutorialsController@create');
$router->post('/admin/tutorials/store', 'TutorialsController@store');
$router->get('/admin/tutorials/{id}/edit', 'TutorialsController@edit');
$router->post('/admin/tutorials/{id}/update', 'TutorialsController@update');
$router->post('/admin/tutorials/{id}/delete', 'TutorialsController@delete');
$router->post('/admin/tutorials/upload-step-image', 'TutorialsController@ajaxUploadStepImage');

// RSS Aggregator & News Feeds
$router->get('/admin/news-feeds', 'AggregatorController@index');
$router->get('/admin/aggregator', 'AggregatorController@index');
$router->post('/admin/news-feeds/fetch-feed', 'AggregatorController@fetchFeed');
$router->post('/admin/aggregator/fetch-feed', 'AggregatorController@fetchFeed');
$router->post('/admin/news-feeds/bulk-action', 'AggregatorController@bulkAction');
$router->post('/admin/aggregator/bulk-action', 'AggregatorController@bulkAction');
$router->post('/admin/news-feeds/translate-publish', 'AggregatorController@translatePublish');
$router->post('/admin/aggregator/translate-publish', 'AggregatorController@translatePublish');
$router->post('/admin/news-feeds/fast-publish', 'AggregatorController@quickPublish');
$router->post('/admin/news-feeds/quick-publish', 'AggregatorController@quickPublish');
$router->post('/admin/aggregator/quick-publish', 'AggregatorController@quickPublish');
$router->post('/admin/news-feeds/draft-article', 'AggregatorController@draftArticle');
$router->post('/admin/news-feeds/draft', 'AggregatorController@draftArticle');
$router->post('/admin/aggregator/draft', 'AggregatorController@draftArticle');
$router->get('/admin/news-feeds/export/opml', 'AggregatorController@exportOpml');
$router->get('/admin/news-feeds/export/json', 'AggregatorController@exportJson');
$router->post('/admin/news-feeds/export-opml', 'AggregatorController@exportOpml');
$router->post('/admin/news-feeds/export-json', 'AggregatorController@exportJson');
$router->post('/admin/aggregator/export-opml', 'AggregatorController@exportOpml');
$router->post('/admin/aggregator/export-json', 'AggregatorController@exportJson');
$router->post('/admin/news-feeds/import', 'AggregatorController@importFeeds');
$router->post('/admin/aggregator/import', 'AggregatorController@importFeeds');
$router->post('/admin/news-feeds/auto-sync-all', 'AggregatorController@autoSyncAll');
$router->post('/admin/aggregator/auto-sync-all', 'AggregatorController@autoSyncAll');
// فحص صحة خلاصات RSS (يُرجع JSON)
$router->get('/admin/news-feeds/health-check', 'AggregatorController@healthCheck');
$router->post('/admin/news-feeds/health-check', 'AggregatorController@healthCheck');
$router->get('/admin/aggregator/health-check', 'AggregatorController@healthCheck');
$router->post('/admin/aggregator/health-check', 'AggregatorController@healthCheck');
$router->get('/admin/rss-sources/health-check', 'AggregatorController@healthCheck');

// RSS Sources (CRUD)
$router->get('/admin/rss-sources', 'RssSourcesController@index');
$router->get('/admin/rss-sources/create', 'RssSourcesController@create');
$router->post('/admin/rss-sources/store', 'RssSourcesController@store');
$router->get('/admin/rss-sources/{id}/edit', 'RssSourcesController@edit');
$router->post('/admin/rss-sources/{id}/update', 'RssSourcesController@update');
$router->post('/admin/rss-sources/{id}/delete', 'RssSourcesController@delete');

// Classifier Rules (AI & Source Mapping)
$router->get('/admin/classifier-rules', 'ClassifierRulesController@index');
$router->post('/admin/classifier-rules/source-rule', 'ClassifierRulesController@updateSourceRule');
$router->post('/admin/classifier-rules/add', 'ClassifierRulesController@add');
$router->post('/admin/classifier-rules/check-conflict', 'ClassifierRulesController@checkConflict');
$router->post('/admin/classifier-rules/delete', 'ClassifierRulesController@delete');
$router->post('/admin/classifier-rules/reclassify-all', 'ClassifierRulesController@reclassifyAll');

// Comments Moderation
$router->get('/admin/comments', 'CommentsController@index');
$router->post('/admin/comments/approve/{id}', 'CommentsController@approve');
$router->get('/admin/comments/approve/{id}', 'CommentsController@approve');
$router->post('/admin/comments/reject/{id}', 'CommentsController@reject');
$router->get('/admin/comments/reject/{id}', 'CommentsController@reject');
$router->post('/admin/comments/spam/{id}', 'CommentsController@spam');
$router->get('/admin/comments/spam/{id}', 'CommentsController@spam');
$router->post('/admin/comments/delete/{id}', 'CommentsController@delete');
$router->get('/admin/comments/delete/{id}', 'CommentsController@delete');

// Contact Messages Inbox
$router->get('/admin/messages', 'ContactMessagesController@index');
$router->get('/admin/messages/{id}', 'ContactMessagesController@show');
$router->post('/admin/messages/{id}/toggle-read', 'ContactMessagesController@toggleRead');
$router->post('/admin/messages/{id}/mark-replied', 'ContactMessagesController@markReplied');
$router->post('/admin/messages/{id}/delete', 'ContactMessagesController@delete');

// Ads & Monetization Management
$router->get('/admin/ads', 'AdsController@index');
$router->get('/admin/ads/create', 'AdsController@create');
$router->post('/admin/ads/store', 'AdsController@store');
$router->get('/admin/ads/{id}/edit', 'AdsController@edit');
$router->post('/admin/ads/{id}/update', 'AdsController@update');
$router->post('/admin/ads/{id}/delete', 'AdsController@delete');

// Newsletter Campaigns & Subscribers
$router->get('/admin/newsletter', 'NewsletterCampaignController@index');
$router->get('/admin/newsletter/create', 'NewsletterCampaignController@create');
$router->post('/admin/newsletter/store', 'NewsletterCampaignController@store');
$router->post('/admin/newsletter/{id}/send', 'NewsletterCampaignController@send');
$router->post('/admin/newsletter/send/{id}', 'NewsletterCampaignController@send');
$router->post('/admin/newsletter/subscribers/store', 'NewsletterCampaignController@addSubscriber');
$router->post('/admin/newsletter/subscribers/add', 'NewsletterCampaignController@addSubscriber');
$router->post('/admin/newsletter/subscribers/{id}/toggle', 'NewsletterCampaignController@toggleSubscriber');
$router->post('/admin/newsletter/subscribers/{id}/delete', 'NewsletterCampaignController@deleteSubscriber');
$router->post('/admin/newsletter/smtp', 'NewsletterCampaignController@saveSmtpSettings');
$router->post('/admin/newsletter/smtp-settings', 'NewsletterCampaignController@saveSmtpSettings');
$router->post('/admin/newsletter/smtp/test', 'NewsletterCampaignController@testSmtp');
$router->post('/admin/newsletter/test-smtp', 'NewsletterCampaignController@testSmtp');

// Static Pages Management
$router->get('/admin/pages', 'PagesController@index');
$router->get('/admin/pages/create', 'PagesController@create');
$router->post('/admin/pages/store', 'PagesController@store');
$router->get('/admin/pages/{id}/edit', 'PagesController@edit');
$router->post('/admin/pages/{id}/update', 'PagesController@update');
$router->post('/admin/pages/{id}/delete', 'PagesController@delete');

// Menus Management
$router->get('/admin/menus', 'MenusController@index');
$router->get('/admin/menus/create', 'MenusController@create');
$router->post('/admin/menus/store', 'MenusController@store');
$router->get('/admin/menus/{id}/edit', 'MenusController@edit');
$router->post('/admin/menus/{id}/update', 'MenusController@update');
$router->post('/admin/menus/{id}/delete', 'MenusController@delete');
$router->get('/admin/menus/items/{id}', 'MenusController@items');
$router->get('/admin/menus/{id}/items', 'MenusController@items');
$router->post('/admin/menus/items/{id}/store', 'MenusController@itemStore');
$router->post('/admin/menus/items/{id}/delete', 'MenusController@itemDelete');

// Media Library
$router->get('/admin/media', 'MediaController@index');
$router->post('/admin/media/upload', 'MediaController@upload');
$router->post('/admin/media/{id}/delete', 'MediaController@delete');

// Settings & Brand Assets
$router->get('/admin/settings', 'SettingsController@index');
$router->post('/admin/settings/update', 'SettingsController@update');
$router->post('/admin/settings/upload-asset', 'SettingsController@uploadAsset');
$router->post('/admin/settings/quick-switch-provider', 'SettingsController@quickSwitchProvider');

// API Keys & Providers
$router->get('/admin/api-keys', 'ApiKeysController@index');
$router->post('/admin/api-keys/store', 'ApiKeysController@store');
$router->post('/admin/api-keys/{id}/update', 'ApiKeysController@update');
$router->post('/admin/api-keys/{id}/regenerate', 'ApiKeysController@regenerate');
$router->post('/admin/api-keys/{id}/toggle', 'ApiKeysController@toggle');
$router->post('/admin/api-keys/{id}/delete', 'ApiKeysController@delete');

// Translation Logs & AI Testing
$router->get('/admin/translation-logs', 'TranslationLogsController@index');
$router->get('/admin/translation-logs/{id}', 'TranslationLogsController@show');
$router->post('/admin/translation-logs/{id}/delete', 'TranslationLogsController@delete');
$router->post('/admin/translation-logs/clear-all', 'TranslationLogsController@clearAll');
$router->post('/admin/translation-logs/clear', 'TranslationLogsController@clearAll');
$router->post('/admin/translation-logs/test', 'TranslationLogsController@testProvider');
$router->get('/admin/ai-logs', 'AiLogsController@index');
$router->get('/admin/ai-logs/conversation', 'AiLogsController@conversation');
$router->post('/admin/ai-logs/reset', 'AiLogsController@reset');
$router->post('/admin/ai-logs/boost', 'AiLogsController@boost');
$router->post('/admin/ai/test-provider', 'TranslationLogsController@testProvider');
$router->get('/admin/ai/test-provider', 'TranslationLogsController@testProvider');
$router->get('/admin/ai/models', 'TranslationLogsController@getModels');
$router->post('/admin/ai/models', 'TranslationLogsController@getModels');

// Activity Log & Traffic Radar
$router->get('/admin/activity-log', 'ActivityLogController@index');
$router->get('/admin/activity-log/export-csv', 'ActivityLogController@exportCsv');
$router->get('/admin/activity-log/export-json', 'ActivityLogController@exportJson');
$router->post('/admin/activity-log/cleanup', 'ActivityLogController@cleanup');

$router->get('/admin/traffic-radar', 'TrafficRadarController@index');
$router->get('/admin/traffic-radar/export-csv', 'TrafficRadarController@exportCsv');
$router->post('/admin/traffic-radar/purge', 'TrafficRadarController@purge');

// Security Alerts
$router->get('/admin/security-alerts', 'SecurityAlertsController@index');
$router->post('/admin/security-alerts/{id}/resolve', 'SecurityAlertsController@resolve');
$router->post('/admin/security-alerts/{id}/delete', 'SecurityAlertsController@delete');
$router->post('/admin/security-alerts/clear-all', 'SecurityAlertsController@clearAll');

// Admin Profile
$router->get('/admin/profile', 'AdminProfileController@show');
$router->post('/admin/profile/update', 'AdminProfileController@update');
$router->post('/admin/profile/change-password', 'AdminProfileController@changePassword');

// Comments, Users & Backup
$router->get('/admin/comments', 'CommentsController@index');
$router->post('/admin/comments/{id}/status', 'CommentsController@updateStatus');
$router->post('/admin/comments/{id}/delete', 'CommentsController@delete');

$router->get('/admin/users', 'UsersController@index');
$router->get('/admin/users/create', 'UsersController@create');
$router->post('/admin/users/store', 'UsersController@store');
$router->get('/admin/users/{id}/edit', 'UsersController@edit');
$router->post('/admin/users/{id}/update', 'UsersController@update');
$router->post('/admin/users/{id}/ban', 'UsersController@ban');
$router->post('/admin/users/{id}/activate', 'UsersController@activate');
$router->post('/admin/users/{id}/delete', 'UsersController@delete');

// Backup & Data Export / Import Center
$router->get('/admin/backup', 'BackupController@index');
$router->get('/admin/backup/export-db', 'BackupController@exportDatabase');
$router->post('/admin/backup/import-db', 'BackupController@importDatabase');
$router->get('/admin/backup/export-articles-json', 'BackupController@exportArticlesJson');
$router->get('/admin/backup/export-articles-csv', 'BackupController@exportArticlesCsv');
$router->post('/admin/backup/import-articles-json', 'BackupController@importArticlesJson');
$router->get('/admin/backup/export-settings-json', 'BackupController@exportSettingsJson');
$router->post('/admin/backup/import-settings-json', 'BackupController@importSettingsJson');
$router->get('/admin/backup/export-subscribers-csv', 'BackupController@exportSubscribersCsv');
$router->post('/admin/backup/import-subscribers-csv', 'BackupController@importSubscribersCsv');
$router->get('/admin/backup/export-polls-json', 'BackupController@exportPollsJson');
$router->get('/admin/backup/export-polls-csv', 'BackupController@exportPollsCsv');
$router->get('/admin/backup/export-tutorials-json', 'BackupController@exportTutorialsJson');
$router->get('/admin/backup/export-rss-opml', 'BackupController@exportRssOpml');
$router->get('/admin/backup/export-classifier-rules-json', 'BackupController@exportClassifierRulesJson');
$router->get('/admin/backup/export-contact-messages-csv', 'BackupController@exportContactMessagesCsv');
$router->get('/admin/backup/export-live-blogs-json', 'BackupController@exportLiveBlogJson');
$router->get('/admin/backup/export-activity-logs-csv', 'BackupController@exportActivityLogsCsv');

// Diagnostics Center & Tools
$router->get('/admin/diagnostics', 'DiagnosticsController@index');
$router->get('/admin/diagnostics/seo', 'DiagnosticsController@seo');
$router->get('/admin/diagnostics/security', 'DiagnosticsController@security');
$router->get('/admin/diagnostics/database', 'DiagnosticsController@database');
$router->get('/admin/diagnostics/media', 'DiagnosticsController@media');
$router->get('/admin/diagnostics/health', 'DiagnosticsController@health');

// Cron Jobs & Live Auto-Publish Engine
$router->get('/admin/cron', 'CronController@index');
$router->post('/admin/cron', 'CronController@index');
$router->post('/admin/cron/run-now', 'CronController@index');
$router->post('/admin/cron/pause', 'CronController@pause');
$router->post('/admin/cron/resume', 'CronController@resume');
$router->get('/admin/cron/status-json', 'CronController@statusJson');
$router->post('/admin/cron/stop', 'CronController@stop');
