<?php

namespace Depage\Search\Providers;

/**
 * brief Pdo
 * Class Pdo
 */
class Pdo
{
    /**
     * @brief urlFilter
     **/
    protected $urlFilter = "";

    /**
     * @brief searchMode
     **/
    //protected $searchMode = "IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION";
    protected $searchMode = "IN NATURAL LANGUAGE MODE";

    // {{{ __construct()
    /**
     * @brief __construct
     *
     * @param mixed $
     * @return void
     **/
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->table = $pdo->prefix . "_search";
    }
    // }}}

    // {{{ setUrlFilter()
    /**
     * @brief setUrlFilter
     *
     * @param mixed $filter
     * @return void
     **/
    public function setUrlFilter($filter)
    {
        if (!empty($filter)) {
            $url = $this->pdo->quote($filter . "%");
            $this->urlFilter = " url LIKE $url AND";
        } else {
            $this->urlFilter = "";
        }
    }
    // }}}

    // {{{ add()
    /**
     * @brief add
     *
     * @param mixed $param
     * @return void
     **/
    public function add($url, $title, $description, $headlines, $content, $lastModfied, $published)
    {
        $query = $this->pdo->prepare(
            "INSERT {$this->table}
            SET
                url = :url,
                title = :title,
                description = :description,
                headlines = :headlines,
                content = :content,
                lastModified = :lastModified,
                lastPublished = :published
            ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), headlines=VALUES(headlines), content=VALUES(content)"
        );
        $query->execute([
            'url' => $url,
            'title' => $title,
            'description' => $description,
            'headlines' => $headlines,
            'content' => $content,
            'lastModified' => $lastModfied,
            'published' => $published,
        ]);
    }
    // }}}
    // {{{ remove()
    /**
     * @brief remove
     *
     * @param mixed $param
     * @return void
     **/
    public function remove($url)
    {
        $query = $this->pdo->prepare(
            "DELETE FROM {$this->table}
            WHERE url = :url"
        );
        $query->execute([
            'url' => $url,
        ]);

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
        $query = $this->pdo->prepare(
            "SELECT url, title, description, content,
                MATCH (title, description, headlines, content) AGAINST (:search1 {$this->searchMode}) as score
            FROM {$this->table}
            WHERE $this->urlFilter
                MATCH (title, description, headlines, content) AGAINST (:search2 {$this->searchMode})
            ORDER BY score DESC, priority DESC, lastModified DESC
            LIMIT :start, :count"
        );
        $query->execute([
            'search1' => $search,
            'search2' => $search,
            'start' => $start,
            'count' => $count,
        ]);

        return $query->fetchAll(\PDO::FETCH_OBJ);
    }
    // }}}
    // {{{ queryCount()
    /**
     * @brief query
     *
     * @param mixed $
     * @return void
     **/
    public function queryCount($search)
    {
        $query = $this->pdo->prepare(
            "SELECT COUNT(*) AS count
            FROM {$this->table}
            WHERE $this->urlFilter
                (MATCH (title, description, headlines, content) AGAINST (:search {$this->searchMode}))"
        );
        $query->execute([
            'search' => $search,
        ]);

        return $query->fetchObject()->count;
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker et : */
