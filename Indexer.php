<?php

namespace Depage\Search;

use Depage\Http\Request;

/**
 * brief Indexer
 * Class Indexer
 */
class Indexer
{
    // {{{ variables
    /**
     * @brief xpathBase
     **/
    protected $xpathBase = "/html/head/base/@href";

    /**
     * @brief xpathExcluded
     **/
    protected $xpathExcluded = "//script | //nav | //*[@data-search-index='noindex']";

    /**
     * @brief xpathTitle
     **/
    protected $xpathTitle = "/html/head/title//text()";

    /**
     * @brief xpathOGTitle
     **/
    protected $xpathOGTitle = "/html/head/meta[@property = 'og:title']/@content";

    /**
     * @brief xpathDescription
     **/
    protected $xpathDescription = "/html/head/meta[@name = 'description']/@content";

    /**
     * @brief xpathHeadlines
     **/
    protected $xpathHeadlines = ".//h1//text() | .//h2//text() | .//h3//text() | .//h4//text() | .//h5//text() | .//h6//text()";

    /**
     * @brief xpathContent
     **/
    protected $xpathContent = ".//article | .//section | .//main";

    /**
     * @brief xpathImgAlt
     **/
    protected $xpathImgAlt = ".//img/@alt | .//img/@title";

    /**
     * @brief xpathImages
     **/
    protected $xpathImages = ".//img/@src | .//img/@srcset";

    /**
     * @brief xpathLinks
     **/
    protected $xpathLinks = ".//a/@href";

    /**
     * @brief xpathLastModified
     **/
    protected $xpathLastModified = "/html/head/meta[@property = 'article:modified_time']/@content";

    /**
     * @brief xpathPublished
     **/
    protected $xpathPublished = "/html/head/meta[@property = 'article:published_time']/@content";

    /**
     * @brief contentNodes
     **/
    protected $contentNodes = null;

    /**
     * @brief title
     **/
    protected $title = null;

    /**
     * @brief description
     **/
    protected $description = null;

    /**
     * @brief headlines
     **/
    protected $headlines = null;

    /**
     * @brief content
     **/
    protected $content = null;

    /**
     * @brief images
     **/
    protected $images = null;

    /**
     * @brief links
     **/
    protected $links = null;

    /**
     * @brief xpath
     **/
    protected $xpath = null;
    // }}}

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
    // {{{ index()
    /**
     * @brief index
     *
     * @param mixed $
     * @return void
     **/
    public function index($url)
    {
        $this->load($url);

        $title = $this->getTitle();
        $description = $this->getDescription();
        $headlines = $this->getHeadlines();
        $content = $this->getContent();
        $lastModified = $this->getLastModified();
        $published = $this->getPublished();

        $this->db->add($url, $title, $description, $headlines, $content, $lastModified, $published);

        return $this;
    }
    // }}}
    // {{{ remove()
    /**
     * @brief remove
     *
     * @param mixed $url
     * @return void
     **/
    public function remove($url)
    {
        $this->db->remove($url);
    }
    // }}}
    // {{{ load()
    /**
     * @brief load
     *
     * @param mixed $
     * @return void
     **/
    public function load($url)
    {
        $request = new Request($url);
        //$request->allowUnsafeSSL = true;

        $response = $request->execute();
        $this->doc = $response->getXml();

        $this->title = [];
        $this->description = [];
        $this->headlines = [];
        $this->content = [];
        $this->images = [];
        $this->links = [];

        $this->lastModified = $response->getLastModified();
        $this->published = $response->getLastModified();

        $this->contentNodes = new \SplObjectStorage();

        if (is_a($this->doc, "DOMDocument")) {
            $this->xpath = new \DOMXPath($this->doc);

            $this->extractContentNodes();
        } else {
            error_log("parse error for $url");
        }

        return $this;
    }
    // }}}

    // {{{ extractContentNodes()
    /**
     * @brief extractContentNodes
     *
     * @return void
     **/
    protected function extractContentNodes()
    {
        // remove excluded nodes from document
        $nodes = $this->xpath->query($this->xpathExcluded);
        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }

        // extract content nodex
        $nodes = $this->xpath->query($this->xpathContent);
        foreach ($nodes as $node) {
            $this->contentNodes->attach($node);
        }

        // remove node if parent node is already included
        foreach ($this->contentNodes as $node) {
            $parentNode = $node->parentNode;
            while ($parentNode != null) {
                if ($this->contentNodes->contains($parentNode)) {
                    $this->contentNodes->detach($node);
                }
                $parentNode = $parentNode->parentNode;
            }
        }
    }
    // }}}

    // {{{ cleanContent()
    /**
     * @brief cleanContent
     *
     * @param mixed $param
     * @return void
     **/
    protected function cleanContent($content)
    {
        $content = implode(" ", $content);
        $content = preg_replace('/[\s\r\n]+/mu', ' ', $content);
        $content = trim($content);

        return $content;
    }
    // }}}
    // {{{ getTitle()
    /**
     * @brief getTitle
     *
     * @param mixed
     * @return void
     **/
    public function getTitle()
    {
        // extract title from og:title first
        $nodes = $this->xpath->query($this->xpathOGTitle);
        foreach ($nodes as $node) {
            $this->title[] = $node->nodeValue;
        }

        if (empty($this->cleanContent($this->title))) {
            // extract title normal html title
            $nodes = $this->xpath->query($this->xpathTitle);
            foreach ($nodes as $node) {
                $this->title[] = $node->nodeValue;
            }
        }

        return $this->cleanContent($this->title);
    }
    // }}}
    // {{{ getDescription()
    /**
     * @brief getDescription
     *
     * @param mixed
     * @return void
     **/
    public function getDescription()
    {
        // extract description
        $nodes = $this->xpath->query($this->xpathDescription);
        foreach ($nodes as $node) {
            $this->description[] = $node->nodeValue;
        }

        return $this->cleanContent($this->description);
    }
    // }}}
    // {{{ getHeadlines()
    /**
     * @brief getHeadlines
     *
     * @return void
     **/
    public function getHeadlines()
    {
        foreach ($this->contentNodes as $contentNode) {
            // search for headline
            $nodes = $this->xpath->query($this->xpathHeadlines, $contentNode);
            foreach ($nodes as $node) {
                $this->headlines[] = $node->nodeValue;
            }
        }

        return $this->cleanContent($this->headlines);
    }
    // }}}
    // {{{ getContent()
    /**
     * @brief getContent
     *
     * @return void
     **/
    public function getContent()
    {
        foreach ($this->contentNodes as $contentNode) {
            // search for text nodes
            $nodes = $this->xpath->query(".//text()", $contentNode);
            foreach ($nodes as $node) {
                if (!empty($node->nodeValue)) {
                    $this->content[] = $node->nodeValue;
                }
            }

            // search for image alt/title tags
            $nodes = $this->xpath->query($this->xpathImgAlt, $contentNode);
            foreach ($nodes as $node) {
                if (!empty($node->nodeValue)) {
                    $this->content[] = $node->nodeValue;
                }
            }
        }

        return $this->cleanContent($this->content);
    }
    // }}}
    // {{{ getImages()
    /**
     * @brief getImages
     *
     * @return void
     **/
    public function getImages()
    {
        $images = [];

        foreach ($this->contentNodes as $contentNode) {
            // extract images
            $nodes = $this->xpath->query($this->xpathImages, $contentNode);
            foreach ($nodes as $node) {
                $src = $node->nodeValue;
                if (preg_match_all("/([^ ]+) [^ ]+,?/", $src, $matches)) {
                    foreach ($matches[1] as $img) {
                        $images[] = $img;
                    }
                } else if (!empty($src)) {
                    $images[] = $src;
                }
            }
        }

        $this->images = array_unique($images);

        // @todo update relative image paths to be dependent on base or on current url

        return $this->images;
    }
    // }}}
    // {{{ getLinks()
    /**
     * @brief getLinks
     *
     * @return void
     **/
    public function getLinks()
    {
        $images = [];

        foreach ($this->contentNodes as $contentNode) {
            // extract links
            $nodes = $this->xpath->query($this->xpathLinks, $contentNode);
            foreach ($nodes as $node) {
                $href = $node->nodeValue;
                if (!empty($href)) {
                    $links[] = $href;
                }
            }
        }

        $this->links = array_unique($links);

        // @todo update relative image paths to be dependent on base or on current url

        return $this->links;
    }
    // }}}
    // {{{ getLastModified()
    /**
     * @brief getLastModified
     *
     * @return void
     **/
    public function getLastModified()
    {
        $nodes = $this->xpath->query($this->xpathLastModified);
        foreach ($nodes as $node) {
            $this->lastModified = new \DateTimeImmutable($node->nodeValue);
        }

        return $this->lastModified->format('Y-m-d H:i:s');
    }
    // }}}
    // {{{ getPublished()
    /**
     * @brief getPublished
     *
     * @return void
     **/
    public function getPublished()
    {
        $nodes = $this->xpath->query($this->xpathPublished);
        foreach ($nodes as $node) {
            $this->published = new \DateTimeImmutable($node->nodeValue);
        }

        return $this->published->format('Y-m-d H:i:s');
    }
    // }}}

    // {{{ updateSchema()
    /**
     * @brief updateSchema
     *
     * @param mixed $pdo
     * @return void
     **/
    public static function updateSchema($pdo)
    {
        $schema = new \Depage\Db\Schema($pdo);

        $schema->setReplace(
            function ($tableName) use ($pdo) {
                return $pdo->prefix . $tableName;
            }
        );
        $schema->loadGlob(__DIR__ . "/Sql/*.sql");
        $schema->update();
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker et : */
