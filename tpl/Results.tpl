<?php

?>
<div class="search-results">
    <ul>
    <?php
        foreach($this->results as $result) {
            $lastModified = new DateTime($result->lastModified);
            $lastPublished = new DateTime($result->lastPublished);
            ?>
                <li class="teaser">
                    <h1><a href="<?php self::t($result->url) ?>"><?php self::t($result->title) ?></a></h1>
                    <p class="description">
                        <span class="date">
                            <?php self::t($lastPublished->format("d.m.Y")) ?> –
                        </span>
                        <?php self::e($result->excerpt) ?>
                    </p>
                    <p class="more"><a href="<?php self::t($result->url) ?>"><?php self::t($result->url) ?></a></p>
                </li>
            <?php
        }
    ?>
    </ul>
</div>
<?php // vim:set ft=php sw=4 sts=4 fdm=marker et : */
