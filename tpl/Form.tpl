<?php
$modes = [
    "by-relevance-and-date" => _("By relevance and date"),
    "by-relevance" => _("By relevance"),
    "by-date" => _("By date"),
];
?>
<form class="search-form" action="<?php self::t($this->searchUrl); ?>" method="GET">
    <input class="query" name="q" type="search" placeholder="<?php self::t(_("Search")); ?>" value="<?php self::t($this->query); ?>" autofocus="autofocus">
    <input class="submit" type="submit" value="<?php self::t(_("Search")); ?>">
    <input <?php self::attr([
        'type' => "hidden",
        'name' => 'mode',
        'value' => $this->mode,
    ]); ?>>

    <?php if (!empty($this->query)) { ?>
        <div class="search-options">
            <?php foreach ($modes as $mode => $label) {
                $class = "search-mode";
                if ($this->mode == $mode) {
                    $class .= " active";
                }
            ?>
                <button <?php self::attr([
                    'class' => $class,
                    'name' => 'mode',
                    'value' => $mode,
                ]); ?>><?php self::t($label); ?></button>
            <?php } ?>
        </div>
    <?php } ?>
</form>

<?php // vim:set ft=php sw=4 sts=4 fdm=marker et : */
