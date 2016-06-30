<?php

namespace Depage\Search;

/**
 * brief Search
 * Class Search
 */
class Search
{
    // {{{ __construct()
    /**
     * @brief __construct
     *
     * @param mixed
     * @return void
     **/
    public function __construct($db)
    {
        $this->db = new Providers\Pdo($db);
    }
    // }}}

    // {{{ setUrlFilter()
    /**
     * @brief setUrlFilter
     *
     * @param mixed $
     * @return void
     **/
    public function setUrlFilter($filter)
    {
        $this->db->setUrlFilter($filter);
    }
    // }}}
    // {{{ query()
    /**
     * @brief query
     *
     * @param mixed $
     * @return void
     **/
    public function query($search, $start = 0, $count = 20)
    {
        if (!empty($search)) {
            $results = $this->db->query($search, $start, $count);
            $maxCount = $this->db->queryCount($search);

            return new Results($results, $maxCount);
        } else {
            return [];
        }
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker et : */
