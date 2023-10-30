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

    /**
     * @brief excerptLength
     **/
    protected $excerptLength = 200;

    // {{{ __construct()
    /**
     * @brief __construct
     *
     * @param mixed $results
     * @return void
     **/
    public function __construct($query, $results, $maxCount = null)
    {
        $this->query = $query;
        $this->results = $results;
        $this->position = 0;
        $this->maxCount = $maxCount;

        $query = trim(preg_replace('/[\s\r\n]+/mu', ' ', $query));
        $words = explode(" ", $query);
        array_walk($words, function (&$word) {
            $word = trim($word, " \n\r\t\v\x00+");
        });

        foreach($this->results as $key => &$result) {
            if (!empty($result->description)) {
                $description = $result->description;
            } else {
                $description = $result->content;
            }
            if (strlen($description) > $this->excerptLength) {
                $resultPos = mb_strpos(strtolower($description), $words[0]);
                if ($resultPos > $this->excerptLength / 2) {
                    $resultPos -= 30;
                    $description = "..." . mb_substr($description, $resultPos, $this->excerptLength) . "...";
                } else {
                    $description = mb_substr($description, 0, $this->excerptLength) . "...";
                }
            }

            for ($i = 0; $i < count($words); $i++) {
                $word = preg_quote($words[$i]);
                $description = preg_replace("/($word)/iu", "<b>$1</b>", $description);
            }

            $result->excerpt = $description;
        }
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

    // {{{ getHtml()
    /**
     * @brief getHtml
     *
     * @param mixed
     * @return void
     **/
    public function getHtml()
    {
        $html = new \Depage\Html\Html("Results.tpl", [
            "results" => $this,
        ], [
            "template_path" => __DIR__ . "/tpl/",
        ]);

        return (string) $html;
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker et : */
