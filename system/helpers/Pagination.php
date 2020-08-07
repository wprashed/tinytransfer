<?php
namespace system\helpers;

class Pagination
{

    //Array Data
    public $target;
    //Array Total Count
    public $totalCount;
    //Page Size
    public $defaultPageSize;
    //Now
    public $pageNow;
    //Prev
    public $pagePrev;
    //Next
    public $pageNext;
    //Page Count
    public $pageCount;
    //Options
    public $options = [
        'simple' => false,
        'allCounts' => false,
        'prev_mark' => '«',
        'next_mark' => '»',
        'page'=>1,
    ];

    public function __construct($target = [], $defaultPageSize = 8, $options = [])
    {
        if (!is_array($target) || !$target) {
            $target = [];
        }
        $this->target = $target;
        $this->totalCount = count($target);
        $this->defaultPageSize = $defaultPageSize;
        $this->options = array_merge($this->options, $options);
        //Get Page Count
        $this->getPageCount();
        $this->getPage($this->options['page']);
    }

    /**
     * Get Page Count
     * @return [type] [description]
     */
    public function getPageCount()
    {
        $this->pageCount = ceil($this->totalCount / $this->defaultPageSize);
        return $this->pageCount;
    }

    /**
     * Get Item
     * @return [type] [description]
     */
    public function getItem()
    {
        if ($this->totalCount <= 0) {
            return [];
        } else {
            return array_slice($this->target, $this->offset(), $this->limit());
        }
    }

    /**
     * Render Page
     * @return [type] [description]
     */
    public function render()
    {
        if ($this->totalCount <= 0) {
            $page = "";
        } else {
            if ($this->options['simple']) {
                return sprintf(
                    '<ul class="pagination">%s  %s</ul>',
                    $this->getPrevPage(),
                    $this->getNextPage()
                );
            }
            $page = '<ul class="pagination">' . $this->getPrevPage() . $this->getLinks() . $this->getNextPage();
            if ($this->options['allCounts']) {
                $page .= $this->getAllCounts();
            }

            $page .= '</ul>';
        }

        return $page;
    }

    /**
     * Get Offset
     * @return [type] [description]
     */
    public function offset()
    {
        $page = $this->pageNow;
        return ($page - 1) * $this->defaultPageSize;
    }
    /**
     * Get Limit
     * @return [type] [description]
     */
    public function limit()
    {
        return $this->defaultPageSize;
    }

    /**
     * Get Page
     * @return [type] [description]
     */
    protected function getPage($page)
    {
        if ($page < 1) {
            $page = 1;
        }
        if ($page >= $this->pageCount) {
            $page = $this->pageCount;
        }
        $this->pagePrev = $this->pageNext = $this->pageNow = $page;
        if ($this->hasMore($page)) {
            $this->pageNext = $page + 1;
        }
        if ($this->hasMore($page, 'prev')) {
            $this->pagePrev = $page - 1;
        }
    }

    /**
     * Has More Page
     * @param  [type]  $page [description]
     * @param  string  $type [description]
     * @return boolean       [description]
     */
    protected function hasMore($page, $type = "next")
    {
        if ($type == 'next') {
            return $page < $this->pageCount;
        } else {
            return $page > 1;
        }
    }

    /**
     * Get Prev Page
     * @param  string $mark [description]
     * @return [type]       [description]
     */
    protected function getPrevPage()
    {
        $mark = $this->options['prev_mark'];
        //if one page no click
        if ($this->pageNow == 1) {
            if (!$this->options['simple']) {
                return '';
            }
            return $this->getDisabledTextWrapper($mark);
        }
        $url = $this->url($this->pagePrev);
        return $this->getAvailablePageWrapper($url, $mark);
    }

    /**
     * Get Disabled Text Wrapper
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<li class="page-item" class="disabled"><a>' . $text . '</a></li>';
    }

    /**
     * get Available Page Wrapper
     * @param  [type] $url  [description]
     * @param  [type] $page [description]
     * @return [type]       [description]
     */
    protected function getAvailablePageWrapper($url, $page)
    {
        return '<li class="page-item"><a href="' . htmlentities($url) . '">' . $page . '</a></li>';
    }

    /**
     * Get Links
     * @return [type] [description]
     */
    protected function getLinks()
    {
        $pageCount = $this->pageCount;
        $pageNow = $this->pageNow;
        $pageLink = '';
        $side = 2;
        $window = $side * 2;
        $block = [
            'first' => [],
            'last' => [],
            'slider' => [],
        ];
        if ($this->pageCount < $window + 6) {
            $block['first'] = $this->getPageRange(1, $this->pageCount);
        } elseif ($this->pageNow <= $window) {
            $block['first'] = $this->getPageRange(1, $window + 2);
            $block['last'] = $this->getPageRange($this->pageCount - 1, $this->pageCount);
        } elseif ($this->pageNow > ($this->pageCount - $window)) {
            $block['first'] = $this->getPageRange(1, 2);
            $block['last'] = $this->getPageRange($this->pageCount - ($window + 2), $this->pageCount);
        } else {
            $block['first'] = $this->getPageRange(1, 2);
            $block['slider'] = $this->getPageRange($this->pageNow - $side, $this->pageNow + $side);
            $block['last'] = $this->getPageRange($this->pageCount - 1, $this->pageCount);
        }
        if (is_array($block['first'])) {
            $pageLink .= $this->getUrlLinks($block['first']);
        }
        if (is_array($block['slider']) && !empty($block['slider'])) {
            $pageLink .= $this->getDots();
            $pageLink .= $this->getUrlLinks($block['slider']);
        }
        if (is_array($block['last']) && !empty($block['last'])) {
            $pageLink .= $this->getDots();
            $pageLink .= $this->getUrlLinks($block['last']);
        }
        return $pageLink;
    }
    /**
     * Get Url Links
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    protected function getUrlLinks($url)
    {
        $pageLink = '';
        foreach ($url as $k => $v) {
            if ($k == $this->pageNow) {
                $pageLink .= $this->getActivePageWrapper($k);
            } else {
                $pageLink .= $this->getAvailablePageWrapper($v, $k);
            }
        }
        return $pageLink;
    }
    /**
     * Get Page Range
     * @param  [type] $start [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    protected function getPageRange($start, $end)
    {
        $urls = [];
        for ($page = $start; $page <= $end; $page++) {
            $urls[$page] = $this->url($page);
        }
        return $urls;
    }

    /**
     * get Next Page
     * @param  string $mark [description]
     * @return [type]       [description]
     */
    protected function getNextPage()
    {
        $mark = $this->options['next_mark'];

        if ($this->pageNow == $this->pageNext) {
            if (!$this->options['simple']) {
                return '';
            }
            return $this->getDisabledTextWrapper($mark);
        }
        $url = $this->url($this->pageNext);
        return $this->getAvailablePageWrapper($url, $mark);
    }

    /**
     * Get Active Page Wrapper
     * @param  string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<li class="page-item active"><a>' . $text . '</a></li>';
    }
    /**
     * Get Dots
     * @return [type] [description]
     */
    protected function getDots()
    {
        return $this->getDisabledTextWrapper('...');
    }

    /**
     * Get All Counts
     * @return [type] [description]
     */
    protected function getAllCounts()
    {
        return '<span class="page-total">Total ' . $this->totalCount . '</span>';
    }

    /**
     * Url
     * @param  [type] $page [description]
     * @return [type]       [description]
     */
    protected function url($page)
    {
        if ($page < 1) {
            $page = 1;
        }

        //get url
        $baseUrl = $this->getBaseUrl();

        $param = http_build_query($this->getParams($page));
        //build url
        $url = $baseUrl . '?' . $param;

        return $url;
    }

    /**
     * Get Url
     * @param  [type] $server [description]
     * @return [type]         [description]
     */
    protected function getUrl()
    {
        return $_SERVER["REQUEST_URI"];
    }

    /**
     * Get Base Url
     * @param  [type] $server [description]
     * @return [type]         [description]
     */
    protected function getBaseUrl()
    {
        $getUrl = $this->getUrl();
        $parse = parse_url($getUrl);
        return $parse['path'];
    }

    /**
     * Get Params
     * @param  [type]  $server [description]
     * @param  integer $page   [description]
     * @return [type]          [description]
     */
    protected function getParams($page = 1)
    {
        $getUrl = $this->getUrl();

        $parse = parse_url($getUrl);
        $query = [];
        //get parse
        if (isset($parse['query'])) {
            parse_str($parse['query'], $query);
        }
        $query['page'] = $page;
        return $query;
    }
}
