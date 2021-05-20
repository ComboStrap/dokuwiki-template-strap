<?php


namespace ComboStrap;


class BarCache extends \dokuwiki\Cache\Cache
{
    public $page;
    public $file;
    public $mode;

    /**
     * BarCache constructor.
     * @param $page - logical id
     * @param $file - file used
     * @param string $mode
     */
    public function __construct($page, $file, $mode)
    {

        $this->page = $page;
        $this->file = $file;
        $this->mode = $mode;

        $cacheKey = $page . $_SERVER['HTTP_HOST'] . $_SERVER['SERVER_PORT'];
        $this->setEvent('PARSER_CACHE_USE');
        $ext = '.' . $mode;
        parent::__construct($cacheKey, $ext);

    }
}
