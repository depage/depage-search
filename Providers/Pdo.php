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
    //protected $searchMode = "IN BOOLEAN MODE";

    /**
     * @brief prio1
     *
     * Priority for title and headlines
     **/
    protected $prio1 = 10;

    /**
     * @brief prio2
     *
     * Priority for content
     **/
    protected $prio2 = 1;

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
            ON DUPLICATE KEY UPDATE
                title=VALUES(title),
                description=VALUES(description),
                headlines=VALUES(headlines),
                content=VALUES(content),
                lastModified=VALUES(lastModified),
                lastPublished=VALUES(lastPublished)
            "
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
            "SELECT
                url,
                title,
                description,
                content,
                priority,
                lastModified,
                lastPublished,
                MATCH (title, headlines) AGAINST (:search1 {$this->searchMode}) as score1,
                MATCH (description, content) AGAINST (:search2 {$this->searchMode}) as score2,
                ABS(DATEDIFF(NOW(), lastPublished)) as dateDiff
            FROM {$this->table}
            WHERE $this->urlFilter
                (
                    MATCH (title, headlines) AGAINST (:search3 {$this->searchMode}) OR
                    MATCH (description, content) AGAINST (:search4 {$this->searchMode})
                )
            ORDER BY
                (
                    (score1 * {$this->prio1} + score2 * {$this->prio2})
                    * (priority + 0.1)
                    * (1 / (dateDiff + 0.1))
                ) DESC
            LIMIT :start, :count"
        );
        $query->execute([
            'search1' => $search,
            'search2' => $search,
            'search3' => $search,
            'search4' => $search,
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
                (
                    MATCH (title, headlines) AGAINST (:search1 {$this->searchMode}) OR
                    MATCH (description, content) AGAINST (:search2 {$this->searchMode})
                )"
        );
        $query->execute([
            'search1' => $search,
            'search2' => $search,
        ]);

        return $query->fetchObject()->count;
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker et : */
