<?php

/**
 * FeedFreshness — عدّاد الأخبار الجديدة (غير المنشورة) لكل مصدر RSS.
 *
 * الهدف: في صفحة لوحة المصادر يظهر عدد العناصر التي وصلت من الخلاصة
 * ولم تُنشر بعد، بدلاً من عدد المقالات التي نُشرت منها.
 * القيمة محسوبة من آخر جلب فعلي للخلاصة (لوحة الاستوديو أو دورة النشر التلقائي)
 * ومخزَّنة في عمودي new_items_count / new_items_last_at في جدول rss_sources.
 */
class FeedFreshness
{
    /**
     * إعادة حساب عدد العناصر غير المنشورة للمصدر وإعادة تاريخ أحدث عنصر منها.
     *
     * @param Database      $db            اتصال قاعدة البيانات.
     * @param int           $sourceId      معرف مصدر RSS.
     * @param array         $publishedUrls خريطة (مؤكدة بصفتها key) بروابط العناصر المنشورة مسبقاً.
     * @param array         $items         عناصر الخلاصة المجلوبة ([link], [pubDate]...).
     */
    public static function recount(?Database $db, int $sourceId, array $publishedUrls, array $items): void
    {
        if (!$db || $sourceId <= 0) {
            return;
        }

        $count    = 0;
        $latestTs = 0;
        $seen     = [];

        foreach ($items as $it) {
            $url = trim((string) ($it['link'] ?? ''));
            if ($url === '') {
                continue;
            }
            if (isset($publishedUrls[$url])) {
                continue; // منشور مسبقاً -> ليس جديداً
            }
            if (isset($seen[$url])) {
                continue; // تكرار داخل نفس الجلب لا يُحتسب مرتين
            }
            $seen[$url] = true;
            $count++;

            $pd = (string) ($it['pubDate'] ?? '');
            if ($pd !== '') {
                $t = strtotime($pd);
                if ($t !== false && $t > $latestTs) {
                    $latestTs = $t;
                }
            }
        }

        $db->query(
            "UPDATE rss_sources SET new_items_count = :c, new_items_last_at = :lt WHERE id = :id",
            [
                ':c'  => $count,
                ':lt' => $latestTs > 0 ? gmdate('Y-m-d H:i:s', $latestTs) : null,
                ':id' => $sourceId,
            ]
        );
    }
}