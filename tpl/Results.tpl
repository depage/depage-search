<?php

?>
<div class="search-results">
    <ul>
    <?php
        foreach($this->results as $result) {
            ?>
                <li class="teaser">
                    <h1><a href="<?php self::t($result->url) ?>"><?php self::t($result->title) ?></a></h1>
                    <p class="description"><?php self::e($result->excerpt) ?></p>
                    <p class="more"><a href="<?php self::t($result->url) ?>"><?php self::t($result->url) ?></a></p>
                </li>
            <?php
        }
    ?>
    </ul>
</div>
<?php // vim:set ft=php sw=4 sts=4 fdm=marker et : */
