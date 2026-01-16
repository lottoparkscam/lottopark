<?php

use Fuel\Core\Form;

include(APPPATH . 'views/whitelabel/shared/navbar.php') ?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . 'views/whitelabel/affs/menu.php') ?>
    </div>
    <div class="col-md-10">
        <h2>Create new casino affiliate group</h2>
        <p class="help-block">
            You can add affiliate casino group here.
        </p>
        <a href="/affs/casino-groups" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> Back
        </a>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" autocomplete="off" action="/affs/casino-groups/store">
                    <?= Form::csrf() ?>
                    <?php include(APPPATH . 'views/whitelabel/shared/messages.php') ?>
                    <div class="form-group">
                        <label class="control-label" for="inputGroupName">
                            Name
                        </label>
                        <div class="input-group">
                            <input type="text"
                                   class="form-control"
                                   id="inputGroupName"
                                   name="input[groupName]"
                                   placeholder="Enter commission value"
                                   required>
                            <div class="input-group-addon">%</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="inputCommissionPercentageValueForTier1">
                            1st-tier sale commission value
                        </label>
                        <div class="input-group">
                            <input type="text"
                                   class="form-control"
                                   id="inputCommissionPercentageValueForTier1"
                                   name="input[commissionPercentageValueForTier1]"
                                   placeholder="Enter commission value"
                                   required>
                            <div class="input-group-addon">%</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="inputCommissionPercentageValueForTier2">
                            2st-tier sale commission value
                        </label>
                        <div class="input-group">
                            <input type="text"
                                   class="form-control"
                                   id="inputCommissionPercentageValueForTier2"
                                   name="input[commissionPercentageValueForTier2]"
                                   placeholder="Enter commission value"
                                   required>
                            <div class="input-group-addon">%</div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Create
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
