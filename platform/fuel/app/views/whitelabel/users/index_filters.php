<?php
$filters_fields = [
    "id",
    "email",
    "name",
    "surname",
    "range_start",
    "range_end"
];

$filters = [];

foreach ($filters_fields as $key => $value) {
    $filter_temp = '';
    $key_text = "filter." . $value;
    if (null !== Input::get($key_text)) {
        $filter_temp = Input::get($key_text);
    }
    $filters[$value] = Security::htmlentities($filter_temp);
}
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
                    $selected = '';
                    if (Input::get("filter.language") == $language['id']) {
                        $selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $language['id']; ?>"<?= $selected; ?>>
                        <?php 
                            echo Lotto_View::format_language($language['code']);
                        ?>
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
                    $selected = '';
                    if (Input::get("filter.country") == $key) {
                        $selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $key; ?>"<?= $selected; ?>>
                        <?= $countries[$key]; ?>
                    </option>
            <?php 
                endforeach;
            ?>
        </select>
    </div>
    
    <div class="form-group">
        <select name="filter[register_ip_country]"
                id="filterRegisterIpCountry"
                class="form-control filterSelect">
            <option value="a">
                <?= _("Register IP Country"); ?>
            </option>
            <?php
                foreach ($countries as $key => $country):
                    $selected = '';
                    if (Input::get("filter.register_ip_country") == $key) {
                        $selected = ' selected="selected"';
                    }
            ?>
                <option value="<?= $key; ?>"<?= $selected; ?>>
                    <?= $countries[$key]; ?>
                </option>
            <?php
                endforeach;
            ?>
        </select>
    </div>

    <div class="form-group">
        <select name="filter[last_ip_country]"
                id="filterLastIpCountry"
                class="form-control filterSelect">
            <option value="a">
                <?= _("Last IP Country"); ?>
            </option>
            <?php
                foreach ($countries as $key => $country):
                    $selected = '';
                    if (Input::get("filter.last_ip_country") == $key) {
                        $selected = ' selected="selected"';
                    }
            ?>
                <option value="<?= $key; ?>"<?= $selected; ?>>
                    <?= $countries[$key]; ?>
                </option>
            <?php
                endforeach;
            ?>
        </select>
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $filters['id']; ?>" 
               class="form-control filterInput" 
               id="filterId" 
               name="filter[id]" 
               placeholder="<?= _("ID"); ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $filters['email']; ?>" 
               class="form-control filterInput" 
               id="filterEmail" 
               name="filter[email]" 
               placeholder="<?= _("E-mail"); ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $filters['name']; ?>" 
               class="form-control filterInput" 
               id="filterName" 
               name="filter[name]" 
               placeholder="<?= _("First Name"); ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $filters['surname']; ?>" 
               class="form-control filterInput" 
               id="filterSurname" 
               name="filter[surname]" 
               placeholder="<?= _("Last Name"); ?>">
    </div>
    
    <div class="new-filter-row">
        <div class="form-group text-nowrap">
            <label class="control-label" for="filterRangeStart">
                <?= _("Range"); ?>:
            </label>
            <div class="input-group input-daterange input-daterange-small datepicker"
                 data-date-end-date="0d">
                <input id="filterRangeStart"
                       name="filter[range_start]"
                       autocomplete="off"
                       type="text"
                       class="form-control filterInput"
                       value="<?= $filters['range_start']; ?>">
                <span class="input-group-addon">
                    <?= _("to"); ?>
                </span>
                <input type="text"
                       id="filterRangeEnd"
                       name="filter[range_end]"
                       autocomplete="off"
                       class="form-control filterInput"
                       value="<?= $filters['range_end']; ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <?= _("Filter"); ?>
        </button>
        <button type="reset" class="btn btn-success" id="filter-form-whitelabel">
            <?= _("Reset"); ?>
        </button>
    </div>
</form>