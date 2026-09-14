<?php

class SEO
{
    public static function renderMeta($title, $description = '', $image = '', $url = '', $type = 'article')
    {
        $siteName = 'منصتي الذكية';
        $titleFormatted = $title ? "{$title} | {$siteName}" : $siteName;
        $description = $description ?: 'منصة ويب قابلة للتخصيص تُطلق كقالب أساسي لمشروعك الرقمي متعدد الأقسام.';
        $url = $url ?: app_url();
        $image = $image ? app_url($image) : app_url('assets/images/og-default.jpg');

        $html = "<!-- SEO & Social Meta Tags -->\n";
        $html .= '<title>' . htmlspecialchars($titleFormatted, ENT_QUOTES, 'UTF-8') . "</title>\n";
        $html .= '<meta name="description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . "\">\n";
        $html .= '<link rel="canonical" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "\">\n";

        // Open Graph
        $html .= '<meta property="og:type" content="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . "\">\n";
        $html .= '<meta property="og:site_name" content="' . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . "\">\n";
        $html .= '<meta property="og:title" content="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "\">\n";
        $html .= '<meta property="og:description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . "\">\n";
        $html .= '<meta property="og:url" content="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "\">\n";
        $html .= '<meta property="og:image" content="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . "\">\n";

        // Twitter Card
        $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        $html .= '<meta name="twitter:title" content="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "\">\n";
        $html .= '<meta name="twitter:description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . "\">\n";
        $html .= '<meta name="twitter:image" content="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . "\">\n";

        // Schema.org JSON-LD
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => ($type === 'article') ? 'NewsArticle' : 'WebSite',
            'headline' => $title,
            'image' => [$image],
            'description' => $description,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $url
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteName,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => app_url('assets/images/logo.png')
                ]
            ]
        ];

        if ($type !== 'article') {
            $schema['name'] = $siteName;
            $schema['alternateName'] = 'SmartPlatform';
        }
        $schema['publisher']['alternateName'] = 'SmartPlatform';

        $html .= '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";

        return $html;
    }
}
