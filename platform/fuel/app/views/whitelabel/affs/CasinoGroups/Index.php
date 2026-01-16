<?php include(APPPATH . 'views/whitelabel/shared/navbar.php') ?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . 'views/whitelabel/affs/menu.php') ?>
    </div>
    <div class="col-md-10">
        <div class="pull-right">
            <a href="/affs/casino-groups/create" class="btn btn-success">
                <span class="glyphicon glyphicon-plus"></span> Add New
            </a>
        </div>
        <h2>
            Affiliate casino groups
        </h2>
        <p class="help-block">
            You can manage affiliate casino groups here.
        </p>
        <div class="container-fluid container-admin">
            <?php include(APPPATH . 'views/whitelabel/shared/messages.php') ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered table-sort">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Commissions</th>
                            <th class="text-center">Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>0</td>
                        <td>Default Group</td>
                        <td>
                            <?php if (!empty($defaultCasinoGroups['commission_percentage_value_tier_1'])): ?>
                                <div>
                                    1st-tier sale commission value:
                                    <?= $defaultCasinoGroups['commission_percentage_value_tier_1'] ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($defaultCasinoGroups['commission_percentage_value_tier_2'])): ?>
                                <div>
                                    2nd-tier sale commission value:
                                    <?= $defaultCasinoGroups['commission_percentage_value_tier_2'] ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="/affs/casino-groups/edit/0" class="btn btn-xs btn-success">
                                <span class="glyphicon glyphicon-edit"></span> Edit
                            </a>
                        </td>
                    </tr>
                    <?php foreach ($casinoGroups as $index => $casinoGroup): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= $casinoGroup['name'] ?></td>
                            <td>
                                <div>
                                    1st-tier sale commission value:
                                    <?= $casinoGroup['commissionPercentageValueForTier1'] ?>
                                </div>
                                <div>
                                    2nd-tier sale commission value:
                                    <?= $casinoGroup['commissionPercentageValueForTier2'] ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="/affs/casino-groups/edit/<?= $casinoGroup['id'] ?>"
                                   class="btn btn-xs btn-success">
                                    <span class="glyphicon glyphicon-edit"></span> Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
