<form class="form-inline form-filter" method="get" action="/affs/casinoCommissions">
    <label>
        <?= _("Filter") ?>:
    </label>

  <div class="form-group">
    <select name="filter[country]" id="filterCountry" class="form-control filterSelect">
      <option value="">
          <?= _('Country'); ?>
      </option>
        <?php foreach ($countries as $key => $country): ?>
          <option value="<?= htmlspecialchars($key); ?>"<?= Input::get("filter.country") == $key ? ' selected="selected"' : ''; ?>>
              <?= htmlspecialchars($country); ?>
          </option>
        <?php endforeach; ?>
    </select>
  </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= Input::get('filter.id', '') ?>"
               class="form-control filterInput" 
               id="filterId" 
               name="filter[id]" 
               placeholder="<?= _("ID"); ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= Input::get('filter.email', '') ?>"
               class="form-control filterInput" 
               id="filterEmail" 
               name="filter[email]" 
               placeholder="<?= _("E-mail"); ?>">
    </div>

    <div class="form-group">
        <input type="text"
             value="<?= Input::get('filter.login', '') ?>"
             placeholder="Affiliate Login"
             name="filter[login]"
             id="filterAff"
             class="form-control"
        />
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= Input::get('filter.name', '') ?>"
               class="form-control filterInput" 
               id="filterName" 
               name="filter[name]" 
               placeholder="<?= _("First Name"); ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= Input::get('filter.surname', '') ?>"
               class="form-control filterInput" 
               id="filterSurname" 
               name="filter[surname]" 
               placeholder="<?= _("Last Name"); ?>">
    </div>
    
    <div class="new-filter-row">
        <div class="form-group text-nowrap">
            <label class="control-label" for="filterRange">
                <?= _("Range"); ?>:
            </label>
            <div class="input-group input-daterange input-daterange-small datepicker" 
                 data-date-end-date="0d">
                <input id="filterRange" 
                       name="filter[range_start]" 
                       autocomplete="off" 
                       type="text" 
                       class="form-control filterInput" 
                       value="<?= Input::get('filter.range_start', '') ?>">
                <span class="input-group-addon">
                    <?= _("to"); ?>
                </span>
                <input type="text" 
                       name="filter[range_end]" 
                       autocomplete="off" 
                       class="form-control filterInput" 
                       value="<?= Input::get('filter.range_end', '') ?>">
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

