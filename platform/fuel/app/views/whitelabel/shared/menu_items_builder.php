<?php
foreach ($menu_settings as $setting):
    $active = '';
    if (in_array($action, $setting['action'])) {
        $active = ' class="active"';
    }
    $badge = '';
    if (isset($setting['badge'])) {
        $badge = ' <span class="badge">';
        $badge .= $setting['badge'];
        $badge .= '</span>';
    }
?>
    <li role="presentation" <?= $active; ?>>
        <a href="<?= $setting['url']; ?>">
            <?= $setting['text']; ?><?= $badge; ?>
        </a>
    </li>
<?php
    if (isset($setting['add_hr']) && $setting['add_hr']):
?>
        <hr>
<?php
    endif;
endforeach;
