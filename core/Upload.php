<?php

class Upload
{
    /**
     * Sanitize SVG content (SH-06). SVGs are XML — a user-supplied SVG can
     * smuggle <script>, foreignObject, or javascript: URLs and run them when
     * served as image/svg+xml. Returns cleaned XML or null (reject).
     */
    protected static function sanitizeSvg($content)
    {
        $content = trim((string) $content);
        if ($content === '' || stripos($content, '<svg') === false) {
            return null;
        }

        // Drop the risky prolog pieces: XML declaration + DOCTYPE (XXE vector).
        $content = (string) preg_replace('/<\?xml[^>]*\?>\s*/i', '', $content);
        $content = (string) preg_replace('/<!DOCTYPE[^>]*>\s*/is', '', $content);
        if (stripos($content, '<!ENTITY') !== false) {
            return null;
        }

        $prev = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $loaded = @$dom->loadXML($content, LIBXML_NONET | LIBXML_NOENT | LIBXML_NOERROR | LIBXML_NOWARNING);
        if ($loaded === false) {
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
            return null;
        }

        $xpath = new DOMXPath($dom);

        // Remove scripting/interactive containers entirely.
        foreach (['script', 'foreignObject', 'iframe', 'object', 'embed', 'link', 'meta', 'form', 'input', 'style'] as $tag) {
            foreach ($xpath->query('//*[local-name() = "' . $tag . '"]') as $node) {
                if ($node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        // Attribute hardening on every remaining element.
        foreach ($xpath->query('//*') as $el) {
            foreach (iterator_to_array($el->attributes) as $attr) {
                $name  = strtolower($attr->nodeName);
                $value = strtolower(trim($attr->nodeValue));

                // Event handlers: onload, onclick, onerror, ...
                if (strpos($name, 'on') === 0) {
                    $el->removeAttributeNode($attr);
                    continue;
                }
                // Active content URLs.
                if (preg_match('!^(javascript|vbscript|data|file):!i', $value)) {
                    $el->removeAttributeNode($attr);
                    continue;
                }
                // External resource links (allow same-document #fragment ids only).
                if (in_array($name, ['href', 'xlink:href'], true) && $value !== '' && $value[0] !== '#') {
                    $el->removeAttributeNode($attr);
                    continue;
                }
                // CSS inside style= can carry url()/expression().
                if ($name === 'style' && (stripos($attr->nodeValue, 'url(') !== false)) {
                    $el->removeAttributeNode($attr);
                }
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $clean = $dom->saveXML($dom->documentElement);
        return ($clean === false) ? null : $clean;
    }
    protected $allowedMimes = [
        'image/jpeg'          => 'jpg',
        'image/jpg'           => 'jpg',
        'image/png'           => 'png',
        'image/webp'          => 'webp',
        'image/gif'           => 'gif',
        'image/svg+xml'       => 'svg',
        'image/avif'          => 'avif',
        'image/bmp'           => 'bmp',
        'image/x-ms-bmp'      => 'bmp',
        'application/pdf'     => 'pdf',
        'video/mp4'           => 'mp4',
        'video/webm'          => 'webm',
        'video/ogg'           => 'ogv',
        'video/quicktime'     => 'mov'
    ];
    protected $maxSizeBytes = 52428800; // 50MB
    protected $uploadDir;
    protected $subdir;

    public function __construct($customSubdir = 'media')
    {
        $this->subdir = trim($customSubdir, '/\\');
        $base = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $this->subdir;
        if (!is_dir($base)) {
            @mkdir($base, 0777, true);
        }
        $this->uploadDir = $base;
    }

    /**
     * Flexible image upload method accepting either $_FILES key name or array
     * Returns string relative path on success (e.g., 'uploads/tutorials/xxx.png') or null on failure
     */
    public function image($fileOrKey, $customSubdir = null)
    {
        $file = null;
        if (is_string($fileOrKey) && isset($_FILES[$fileOrKey])) {
            $file = $_FILES[$fileOrKey];
        } elseif (is_array($fileOrKey) && isset($fileOrKey['tmp_name'])) {
            $file = $fileOrKey;
        }

        if (!$file || empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return null;
        }

        if (($file['size'] ?? 0) > $this->maxSizeBytes) {
            return null;
        }

        $targetSubdir = $customSubdir ? trim($customSubdir, '/\\') : $this->subdir;
        $targetDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $targetSubdir;
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        $mime = 'image/jpeg';
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
        }

        $ext = $this->allowedMimes[$mime] ?? null;
        if (!$ext) {
            // Check file extension fallback
            $origExt = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
            if (in_array($origExt, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'avif', 'bmp'])) {
                $ext = $origExt === 'jpeg' ? 'jpg' : $origExt;
            } else {
                return null;
            }
        }

        $uniqueName = uniqid('img_', true) . '.' . $ext;
        $destination = $targetDir . DIRECTORY_SEPARATOR . $uniqueName;

        // SVGs are re-serialised through the sanitizer — never stored raw (SH-06).
        if ($ext === 'svg') {
            $raw = @file_get_contents($file['tmp_name']);
            $clean = ($raw === false) ? null : self::sanitizeSvg($raw);
            if ($clean === null || !@file_put_contents($destination, $clean, LOCK_EX)) {
                return null;
            }
            return 'uploads/' . str_replace('\\', '/', $targetSubdir) . '/' . $uniqueName;
        }

        if (!@move_uploaded_file($file['tmp_name'], $destination)) {
            if (!@copy($file['tmp_name'], $destination) && !@rename($file['tmp_name'], $destination)) {
                return null;
            }
        }

        return 'uploads/' . str_replace('\\', '/', $targetSubdir) . '/' . $uniqueName;
    }

    public function handle($fileInputName)
    {
        if (empty($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'لم يتم إرسال ملف صالح أو حدث خطأ أثناء الرفع.'];
        }

        $file = $_FILES[$fileInputName];
        if ($file['size'] > $this->maxSizeBytes) {
            return ['success' => false, 'error' => 'حجم الملف يتجاوز الحد المسموح به (15 ميغابايت).'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!isset($this->allowedMimes[$mime])) {
            return ['success' => false, 'error' => 'نوع الملف غير مدعوم. الأنواع المسموحة: صور و PDF فقط.'];
        }

        $ext = $this->allowedMimes[$mime];
        $originalName = basename($file['name']);
        $uniqueName = uniqid('up_', true) . '.' . $ext;
        $destination = $this->uploadDir . DIRECTORY_SEPARATOR . $uniqueName;

        if ($ext === 'svg') {
            // Sanitize BEFORE writing to disk — store only the cleaned XML (SH-06).
            $raw = @file_get_contents($file['tmp_name']);
            $clean = ($raw === false) ? null : self::sanitizeSvg($raw);
            if ($clean === null) {
                return ['success' => false, 'error' => 'ملف SVG غير صالح أو يحتوي على محتوى ممنوع.'];
            }
            if (!@file_put_contents($destination, $clean, LOCK_EX)) {
                return ['success' => false, 'error' => 'فشل في حفظ الملف على الخادم.'];
            }
        } else if (!move_uploaded_file($file['tmp_name'], $destination)) {
            if (!@copy($file['tmp_name'], $destination)) {
                return ['success' => false, 'error' => 'فشل في حفظ الملف على الخادم.'];
            }
        }

        $relativePath = 'uploads/' . str_replace('\\', '/', $this->subdir) . '/' . $uniqueName;

        $width = null;
        $height = null;
        if (strpos($mime, 'image/') === 0 && function_exists('getimagesize')) {
            $info = @getimagesize($destination);
            if ($info) {
                $width = $info[0];
                $height = $info[1];
            }
        }

        return [
            'success'       => true,
            'file_name'     => $uniqueName,
            'original_name' => $originalName,
            'file_path'     => $relativePath,
            'mime_type'     => $mime,
            'file_size'     => $file['size'],
            'width'         => $width,
            'height'        => $height,
            'dimensions'    => ($width && $height) ? "{$width}x{$height}" : null
        ];
    }
}
