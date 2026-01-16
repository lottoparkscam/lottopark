<?php
include(APPPATH . 'views/whitelabel/shared/navbar.php');
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
            include(APPPATH . 'views/whitelabel/bonuses/menu.php');
        ?>
    </div>
    <div class="col-md-10">
        <div class="pull-right">
            <a href="/bonuses/freespins/new" class="btn btn-success">
                <span class="glyphicon glyphicon-plus"></span> Add New
            </a>
        </div>
        <h2>Mini Games - Free Spins</h2>
        <p class="help-block">You can manage free spins here.</p>
        <div class="container-fluid container-admin">
            <?php
                include(APPPATH . 'views/whitelabel/shared/messages.php');
                if (isset($freeSpins) && count($freeSpins) > 0):
            ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered table-sort">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Mini Game</th>
                            <th>Free spins</th>
                            <th>Spin value</th>
                            <th>Usage limit</th>
                            <th>Per user limit</th>
                            <th>Start date</th>
                            <th>End date</th>
                            <th>Active</th>
                            <th>Created at</th>
                            <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($freeSpins as $row): ?>
                            <tr>
                                <td>
                                    <?= $row['id'] ?>
                                </td>
                                <td>
                                    <?= $row['code'] ?>
                                </td>
                                <td>
                                    <?= $row['mini_game']['name'] ?>
                                </td>
                                <td>
                                    <?= $row['free_spin_count'] ?>
                                </td>
                                <td>
                                  €<?= $row['free_spin_value'] ?>
                                </td>
                                <td>
                                    <?= $row['usage_limit'] ?>
                                </td>
                                <td>
                                    <?= $row['user_usage_limit'] ?>
                                </td>
                                <td>
                                    <?= $row['date_start'] ?>
                                </td>
                                <td>
                                    <?= $row['date_end'] ?>
                                </td>
                                <td>
                                    <?= $row['is_active'] ? 'Yes' : 'No' ?>
                                </td>
                                <td>
                                    <?= $row['created_at']; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/bonuses/freespins/<?= $row['id']; ?>/s/users" class="btn btn-primary btn-xs">
                                            <span class="glyphicon glyphicon-user"></span> View users
                                        </a>
                                    </div>
                                    <div class="btn-group">
                                        <a href="/bonuses/freespins/<?= $row['id']; ?>/s/edit" class="btn btn-primary btn-xs">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
               <p class="text-info">No Free spins.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
