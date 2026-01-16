<?php

use Fuel\Core\Input;

$id_t = '';
if (null !== Input::get("filter.id")) {
    $id_t = Input::get("filter.id");
}
$id_text = Security::htmlentities($id_t);

$email_t = '';
if (null !== Input::get("filter.email")) {
    $email_t = Input::get("filter.email");
}
$email_text = Security::htmlentities($email_t);

$login_t = '';
if (null !== Input::get("filter.login")) {
    $login_t = Input::get("filter.login");
}
$login_text = Security::htmlentities($login_t);

$name_t = '';
if (null !== Input::get("filter.name")) {
    $name_t = Input::get("filter.name");
}
$name_text = Security::htmlentities($name_t);

$surname_t = '';
if (null !== Input::get("filter.surname")) {
    $surname_t = Input::get("filter.surname");
}
$surname_text = Security::htmlentities($surname_t);

?>
<form class="form-inline form-filter" method="get" action="<?= $link; ?>">
    <label>
        <?= _("Filter"); ?>: 
    </label>
    
    <div class="form-group">
        <select name="filter[language]" 
                id="filterLanguage" 
                class="form-control filterSelect">
            <option value="a">
                <?= _("Language"); ?>
            </option>
            <?php 
                foreach ($languages as $language):
                    $is_selected = '';
                    if (Input::get("filter.language") == $language['id']) {
                        $is_selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $language['id']; ?>"<?= $is_selected; ?>>
                        <?= Lotto_View::format_language($language['code']); ?>
                    </option>
            <?php 
                endforeach;
            ?>
        </select>
    </div>
    
    <div class="form-group">
        <select name="filter[country]" 
                id="filterCountry" 
                class="form-control filterSelect">
            <option value="a">
                <?= _("Country"); ?>
            </option>
            <?php 
                foreach ($countries as $key => $country):
                    $is_selected = '';
                    if (Input::get("filter.country") == $key) {
                        $is_selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $key; ?>"<?= $is_selected; ?>>
                        <?= $countries[$key]; ?>
                    </option>
            <?php 
                endforeach;
            ?>
        </select>
    </div>
    
    <div class="form-group">
        <select name="filter[lotteryGroup]"
                id="filterLotteryGroup"
                class="form-control filterSelect">
            <option value="a">
               Lottery Group
            </option>
            <option value="default" <?= (Input::get('filter.group') == 'default') ? 'selected="selected"' : '' ?>>
                Default Lottery Group
            </option>
            <?php 
                foreach ($lotteryGroups as $key => $lotteryGroup):
                    $isSelected = '';
                    if (Input::get('filter.lotteryGroup') !== null &&
                        Input::get('filter.lotteryGroup') != 'default' &&
                        Input::get('filter.lotteryGroup') != 'a' &&
                        Input::get('filter.lotteryGroup') == $key
                    ) {
                        $isSelected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $key ?>"<?= $isSelected ?>>
                        <?= $lotteryGroups[$key]['name'] ?>
                    </option>
            <?php 
                endforeach;
            ?>
        </select>
    </div>

    <div class="form-group">
        <select name="filter[casinoGroup]"
                id="filterCasinoGroup"
                class="form-control filterSelect">
            <option value="a">
                Casino Group
            </option>
            <option value="default" <?= Input::get('filter.group') == 'default' ? ' selected="selected"' : '' ?>>
                Default Casino Group
            </option>
            <?php
            foreach ($casinoGroups as $casinoGroup):
                $isSelected = '';
                if (Input::get('filter.casinoGroup') !== null &&
                    Input::get('filter.casinoGroup') != 'default' &&
                    Input::get('filter.casinoGroup') != 'a' &&
                    Input::get('filter.casinoGroup') == $casinoGroup['id']
                ) {
                    $isSelected = ' selected="selected"';
                }
                ?>
                <option value="<?= $key ?>"<?= $isSelected ?>>
                    <?= $casinoGroup['name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <select id="filterisAffUser"
                class="form-control filterInput"
                name="filter[isAffUser]">
            <option value="default">Is Aff User</option>
            <option value="1" <?= Input::get('filter.isAffUser') === '1' ? ' selected' : '' ?>>Aff User (1)</option>
            <option value="0" <?= Input::get('filter.isAffUser') === '0' ? ' selected' : '' ?>>Not Aff User (0)</option>
        </select>
    </div>

    <div class="form-group">
        <input type="text" 
               value="<?= $id_t; ?>" 
               class="form-control filterInput" 
               id="filterId" 
               name="filter[id]" 
               placeholder="<?= _("ID"); ?>">
    </div>

    <div class="form-group">
        <input type="text" 
               value="<?= $email_text; ?>" 
               class="form-control filterInput" 
               id="filterEmail" 
               name="filter[email]" 
               placeholder="<?= _("E-mail"); ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $login_text; ?>" 
               class="form-control filterInput" 
               id="filterLogin" 
               name="filter[login]" 
               placeholder="<?= _("Login"); ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $name_text; ?>" 
               class="form-control filterInput" 
               id="filterName" 
               name="filter[name]" 
               placeholder="<?= _("First Name"); ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $surname_text; ?>" 
               class="form-control filterInput" 
               id="filterSurname" 
               name="filter[surname]" 
               placeholder="<?= _("Last Name"); ?>">
    </div>
    
    <button type="submit" class="btn btn-primary">
        <?= _("Filter"); ?>
    </button>
    <button type="reset" class="btn btn-success" id="filter-form-whitelabel">
        <?= _("Reset"); ?>
    </button>
</form>

