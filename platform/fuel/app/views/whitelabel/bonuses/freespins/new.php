<?php

use Fuel\Core\Form;
use Fuel\Core\Input;

include(APPPATH . 'views/whitelabel/shared/navbar.php');
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . 'views/whitelabel/bonuses/menu.php'); ?>
    </div>
    <div class="col-md-10">
        <h2>New Mini Game Promo Code</h2>
        <p class="help-block">You can add a new mini-game promo code here.</p>
        <a href="/bonuses/promocodes<?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> Back
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form id="form-promocodes" method="post" action="/bonuses/freespins/new<?= Lotto_View::query_vars(); ?>">
                    <?= Form::csrf() ?>
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . 'views/whitelabel/shared/errors.php');
                        }
                    ?>
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="inputCode">Code:</label>
                                <input type="text" class="form-control" id="inputCode" name="input[code]"
                                    value="<?= Input::post('input.code', isset($promoCode['code']) ? $promoCode['code'] : ''); ?>"
                                    required placeholder="Enter promo code">
                            </div>
                            <div class="form-group">
                              <label class="control-label" for="inputMiniGame">Select Mini Game:</label>
                              <select name="input[mini_game_id]" id="inputMiniGame" class="form-control">
                                  <?php foreach ($miniGames as $miniGame): ?>
                                    <option value="<?= $miniGame['id']; ?>"
                                        <?= Input::post('input.mini_game_id', $promoCode['mini_game_id'] ?? '') == $miniGame['id'] ? 'selected="selected"' : '' ?>>
                                        <?= $miniGame['name']; ?>
                                    </option>
                                  <?php endforeach; ?>
                              </select>
                            </div>
                            <div class="form-group" id="availableBetsContainer" style="display: none;">
                              <label class="control-label" for="inputAvailableBets">Select Free Spin Value:</label>
                              <select name="input[free_spin_value]" id="inputAvailableBets" class="form-control"></select>
                            </div>
                            <script>
                                const availableBetsByGameId = <?= json_encode($availableBetsByGameId) ?>;
                                document.getElementById('inputMiniGame').addEventListener('change', function() {
                                    const selectedGameId = this.value;
                                    const availableBets = availableBetsByGameId[selectedGameId] ? JSON.parse(availableBetsByGameId[selectedGameId]) : [];
                                    const betsSelect = document.getElementById('inputAvailableBets');
                                    betsSelect.innerHTML = '';
                                    availableBets.forEach(bet => {
                                        const option = document.createElement('option');
                                        option.value = bet;
                                        option.textContent = '€' + bet;
                                        betsSelect.appendChild(option);
                                    });
                                    document.getElementById('availableBetsContainer').style.display = availableBets.length > 0 ? 'block' : 'none';
                                });
                                document.getElementById('inputMiniGame').dispatchEvent(new Event('change'));
                            </script>
                            <div class="form-group">
                                <label for="inputFreeSpinCount">Free Spin Count:</label>
                                <input min="1" max="9999" type="number" class="form-control" id="inputFreeSpinCount" name="input[free_spin_count]"
                                    value="<?= Input::post('input.free_spin_count', $promoCode['free_spin_count'] ?? 1); ?>"
                                    required placeholder="Enter free spin count">
                            </div>
                            <div class="form-group">
                                <label for="inputUsageLimit">Usage Limit:</label>
                                <input type="number" min="1" max="99999999" class="form-control" id="inputUsageLimit" name="input[usage_limit]"
                                    value="<?= Input::post('input.usage_limit', $promoCode['usage_limit'] ?? 1); ?>"
                                    required placeholder="Enter usage limit">
                            </div>
                            <div class="form-group">
                                <label for="inputUserUsageLimit">User Usage Limit:</label>
                                <input type="number" value="1" max="99999999" class="form-control" id="inputUserUsageLimit" name="input[user_usage_limit]"
                                     disabled placeholder="1">
                            </div>
                            <div class="form-group">
                                <label for="inputDateStart">Start Date:</label>
                                <input type="text" class="form-control datepicker" id="inputDateStart" name="input[date_start]"
                                    value="<?= Input::post('input.date_start', $promoCode['date_start'] ?? date('Y-m-d H:i:s', time())); ?>"
                                    required placeholder="mm/dd/yyyy">
                            </div>
                            <div class="form-group">
                                <label for="inputDateEnd">End Date:</label>
                                <input type="text" class="form-control datepicker" id="inputDateEnd" name="input[date_end]"
                                       value="<?= Input::post('input.date_end', $promoCode['date_end'] ?? date('Y-m-d H:i:s', strtotime('+1 day'))); ?>"
                                    required placeholder="mm/dd/yyyy">
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="1" name="input[is_active]"
                                        <?= Input::post('input.is_active', $promoCode['is_active'] ?? false) ? 'checked' : ''; ?>>
                                    Enabled
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
