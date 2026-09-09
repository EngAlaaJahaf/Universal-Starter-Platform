<?php

class Pagination
{
    public $total;
    public $perPage;
    public $currentPage;
    public $totalPages;
    public $baseUrl;

    public function __construct($total, $perPage = 10, $currentPage = 1, $baseUrl = '')
    {
        $this->total = max(0, (int) $total);
        $this->perPage = max(1, (int) $perPage);
        $this->totalPages = (int) ceil($this->total / $this->perPage);
        $this->currentPage = min(max(1, (int) $currentPage), max(1, $this->totalPages));
        $this->baseUrl = $baseUrl ?: (string) ($_SERVER['REQUEST_URI'] ?? '');
    }

    public function getOffset()
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function getLimit()
    {
        return $this->perPage;
    }

    public function hasPages()
    {
        return $this->totalPages > 1;
    }

    public function getUrl($page)
    {
        $parsed = parse_url($this->baseUrl);
        $query = [];
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $query);
        }
        $query['page'] = $page;
        $path = $parsed['path'] ?? '/';
        return $path . '?' . http_build_query($query);
    }

    public function render($wrapperClass = 'pagination-wrap')
    {
        if (!$this->hasPages()) {
            return '';
        }

        $html = '<nav class="' . htmlspecialchars($wrapperClass) . '" aria-label="Page navigation">';
        $html .= '<ul style="display:flex;gap:6px;list-style:none;justify-content:center;padding:0;margin:24px 0">';

        // Prev
        if ($this->currentPage > 1) {
            $html .= '<li><a class="filter-btn" href="' . htmlspecialchars($this->getUrl($this->currentPage - 1)) . '">‹ السابق</a></li>';
        }

        for ($i = 1; $i <= $this->totalPages; $i++) {
            if ($i == 1 || $i == $this->totalPages || ($i >= $this->currentPage - 2 && $i <= $this->currentPage + 2)) {
                $active = ($i == $this->currentPage) ? 'active' : '';
                $html .= '<li><a class="filter-btn ' . $active . '" href="' . htmlspecialchars($this->getUrl($i)) . '">' . $i . '</a></li>';
            } elseif ($i == $this->currentPage - 3 || $i == $this->currentPage + 3) {
                $html .= '<li style="padding:6px 10px;color:var(--text-dim)">...</li>';
            }
        }

        // Next
        if ($this->currentPage < $this->totalPages) {
            $html .= '<li><a class="filter-btn" href="' . htmlspecialchars($this->getUrl($this->currentPage + 1)) . '">التالي ›</a></li>';
        }

        $html .= '</ul></nav>';
        return $html;
    }
}
