<form class="form-inline form-filter" method="get" action="/reports">
    <div class="form-group text-nowrap">
        <label class="control-label" for="filterRange">
            <?= _("Range"); ?>:
        </label>
        <div class="input-group input-daterange datepicker" data-date-end-date="0d">
            <input id="filterRange" 
                   required 
                   name="filter[range_start]" 
                   type="text" 
                   class="form-control" 
                   value="<?= $date_filters['range_start']; ?>">
            <span class="input-group-addon">
                <?= _("to"); ?>
            </span>
            <input type="text" 
                   required 
                   name="filter[range_end]" 
                   class="form-control" 
                   value="<?= $date_filters['range_end']; ?>">
        </div>
    </div>
    
    <div class="form-group">
        <select name="filter[language]" id="filterLanguage" class="form-control">
            <option value="a">
                <?= _("Language"); ?>
            </option>
            <?php 
                foreach ($languages as $language):
            ?>
                    <option value="<?= $language['id']; ?>" <?= $language['selected']; ?>>
                        <?= $language['lang_code']; ?>
                    </option>
            <?php 
                endforeach;
            ?>
        </select>
    </div>
    
    <div class="form-group">
        <select name="filter[country]" id="filterCountry" class="form-control">
            <option value="a">
                <?= _("Country"); ?>
            </option>
            <?php 
                foreach ($countries as $country):
            ?>
                    <option value="<?= $country['id']; ?>" <?= $country['selected']; ?>>
                        <?= $country['name']; ?>
                    </option>
            <?php 
                endforeach;
            ?>
        </select>
    </div>
    
    <button type="submit" class="btn btn-primary">
        <?= _("Report"); ?>
    </button>
</form>
