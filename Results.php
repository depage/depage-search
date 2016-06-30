<?php

namespace Depage\Search;

/**
 * brief Results
 * Class Results
 */
class Results implements \Iterator
{
    /**
     * @brief position
     **/
    protected $position = 0;

    /**
     * @brief maxCount
     **/
    protected $maxCount = null;

    /**
     * @brief results
     **/
    protected $results = [];

    // {{{ __construct()
    /**
     * @brief __construct
     *
     * @param mixed $results
     * @return void
     **/
    public function __construct($results, $maxCount = null)
    {
        $this->results = $results;
        $this->position = 0;
        $this->maxCount = $maxCount;
    }
    // }}}

    // {{{ rewind()
    /**
     * @brief rewind
     *
     * @return void
     **/
    public function rewind()
    {
        $this->position = 0;
    }
    // }}}
    // {{{ current()
    /**
     * @brief current
     *
     * @return void
     **/
    public function current()
    {
        return $this->results[$this->position];
    }
    // }}}
    // {{{ key()
    /**
     * @brief key
     *
     * @return void
     **/
    public function key()
    {
        return $this->position;
    }
    // }}}
    // {{{ next()
    /**
     * @brief next
     *
     * @return void
     **/
    public function next()
    {
        ++$this->position;
    }
    // }}}
    // {{{ valid()
    /**
     * @brief valid
     *
     * @return void
     **/
    public function valid()
    {
        return isset($this->results[$this->position]);
    }
    // }}}
    // {{{ getMaxCount()
    /**
     * @brief getMaxCount
     *
     * @param mixed
     * @return void
     **/
    public function getMaxCount()
    {
        return $this->maxCount;
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker et : */
