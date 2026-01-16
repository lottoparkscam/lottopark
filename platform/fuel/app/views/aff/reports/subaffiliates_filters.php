<form class="form-inline form-filter" method="get" action="<?= $action_full ?>">
    <label>
        <?= _("Filter") ?>: 
    </label>
    
    <div class="form-group">
        <select name="filter[language]" 
                id="filterLanguage" 
                class="form-control filterSelect">
            <?php 
                foreach ($languages as $key => $language):
            ?>
                    <option value="<?= $key ?>" <?= $get_selected("filter.language", $key) ?>>
                        <?= $language ?>
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
            <?php 
                foreach ($countries as $key => $country):
            ?>
                    <option value="<?= $key ?>" <?= $get_selected("filter.country", $key) ?>>
                        <?= $country ?>
                    </option>
            <?php 
                endforeach;
            ?>
        </select>
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $get_last_text("filter.id") ?>" 
               class="form-control filterInput" 
               id="filterId" 
               name="filter[id]" 
               placeholder="<?= _("ID") ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $get_last_text("filter.email") ?>" 
               class="form-control filterInput" 
               id="filterEmail" 
               name="filter[email]" 
               placeholder="<?= _("E-mail") ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $get_last_text("filter.login") ?>" 
               class="form-control filterInput" 
               id="filterLogin" 
               name="filter[login]" 
               placeholder="<?= _("Login") ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $get_last_text("filter.name") ?>" 
               class="form-control filterInput" 
               id="filterName" 
               name="filter[name]" 
               placeholder="<?= _("First Name") ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $get_last_text("filter.surname") ?>" 
               class="form-control filterInput" 
               id="filterSurname" 
               name="filter[surname]" 
               placeholder="<?= _("Last Name") ?>">
    </div>
    
    <button type="submit" class="btn btn-primary">
        <?= _("Filter"); ?>
    </button>
    <button type="reset" class="btn btn-success" id="filter-form-whitelabel">
        <?= _("Reset"); ?>
    </button>
</form>
