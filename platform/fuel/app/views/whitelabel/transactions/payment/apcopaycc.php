<?php
if (isset($adata['result']['params'])):
    $doc = new DOMDocument();
    $doc->loadXML(stripslashes($adata['result']['params']));
?>
    <h3>
        <?= _("Apcopay CC"); ?>
    </h3>
    <?php
    $children = $doc->childNodes;
    foreach ($children as $child):
        $children2 = $child->childNodes;
        foreach ($children2 as $child2):?>
            <span class="details-label">
                <?= Security::htmlentities($child2->tagName); ?>:
            </span>
            <span class="details-value">
                <?= Security::htmlentities($child2->nodeValue); ?>
            </span>
            <br>
        <?php endforeach;?>
    <?php endforeach; ?>
<?php endif; ?>
