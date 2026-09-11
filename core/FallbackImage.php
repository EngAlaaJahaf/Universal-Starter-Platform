<?php
/**
 * FallbackImage — المصدر المركزي لصور «بدون صورة» في المنصة.
 *
 * تتحكم الإدارة بهذه الروابط من لوحة الإدارة ← الإعدادات ← المظهر والتصميم
 * (مفاتيح fallback_image_*). يجري استخدامها في:
 *   - الكرون (cron/rss_auto_publish.php) عند تعذّر إيجاد صورة حقيقية.
 *   - لوحة تجميع الأخبار (AggregatorController) كصورة افتراضية للمعاينة والنشر.
 *   - واجهة الموقع كصورة احتياطية عند فشل تحميل صورة المقال (onerror).
 *   - الكشف عن الصور الاحتياطية في FetchOg::isPlaceholder لاستبدالها بالحقيقية.
 */
class FallbackImage
{
    /** الصورة الافتراضية العامة إن لم يُضبط أي إعداد بعد. */
    public const BUILTIN_GENERAL = 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80';

    /** المعرفات الافتراضية المدمجة لكل تصنيف (مفاتيح slugs). */
    private const BUILTIN_CATEGORY = [
        'artificial-intelligence' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=1200&q=80',
        'cybersecurity'           => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=1200&q=80',
        'hardware-devices'        => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&q=80',
        'software-development'    => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1200&q=80',
        'general-tech'            => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200&q=80',
    ];

    /** كل المفاتيح المعروفة (للكشف عن أي صورة احتياطية). */
    public static function categoryKeys(): array
    {
        return array_keys(self::BUILTIN_CATEGORY);
    }

    /** مفتاح الإعداد في جدول settings لتصنيف معيّن. */
    public static function settingKey(string $categoryKey): string
    {
        return 'fallback_image_' . $categoryKey;
    }

    public static function general(): string
    {
        $v = trim((string) Settings::get('fallback_image_general', self::BUILTIN_GENERAL));
        return $v !== '' ? $v : self::BUILTIN_GENERAL;
    }

    public static function forCategory(string $categoryKey): string
    {
        if (!isset(self::BUILTIN_CATEGORY[$categoryKey])) {
            $categoryKey = 'general-tech';
        }
        $v = trim((string) Settings::get(self::settingKey($categoryKey), ''));
        if ($v === '') {
            $v = self::BUILTIN_CATEGORY[$categoryKey];
        }
        return $v;
    }

    /** استخراج معرّف Unsplash (photo-xxxx) من رابط، أو سلسلة فارغة. */
    public static function photoId(string $url): string
    {
        if (preg_match('#(photo-[A-Za-z0-9\-]+)#i', $url, $m)) {
            return strtolower($m[1]);
        }
        return '';
    }

    /**
     * هل الرابط صورة احتياطية عامة (من الإعداد fallback_image_*) يجب
     * استبدالها بالصورة الحقيقية عند توفّرها؟
     */
    public static function isPlaceholder(?string $url): bool
    {
        $u = trim((string) $url);
        if ($u === '') {
            return false;
        }

        // مطابقة كاملة للرابط المُضبَّط (تغطي صوراً مستضافة خارج Unsplash أيضاً).
        $configured = [self::general()];
        foreach (self::categoryKeys() as $k) {
            $configured[] = self::forCategory($k);
        }
        if (in_array($u, $configured, true)) {
            return true;
        }

        // مطابقة بمعرّف Unsplash (يتحمّل اختلاف w/q في الواجهات المختلفة).
        $id = self::photoId($u);
        if ($id === '') {
            return false;
        }
        $known = [self::photoId(self::general())];
        foreach (self::categoryKeys() as $k) {
            $known[] = self::photoId(self::forCategory($k));
        }
        return in_array($id, array_filter($known), true);
    }
}