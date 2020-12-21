<form class="search-form" action="<?php self::t($this->searchUrl); ?>" method="GET">
    <input class="query" name="q" type="search" placeholder="<?php self::t(_("Search")); ?>" value="<?php self::t($this->query); ?>" autofocus="autofocus">
    <input class="submit" type="submit" value="<?php self::t(_("Search")); ?>">
</form>

<?php // vim:set ft=php sw=4 sts=4 fdm=marker et : */
