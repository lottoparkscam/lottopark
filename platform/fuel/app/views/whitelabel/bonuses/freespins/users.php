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
        <h2>Users</h2>
        <p class="help-block">You can view users who used promo codes of your campaign here.</p>
        <a href="/bonuses/freespins<?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> Back
        </a>
        <div class="container-fluid container-admin">
            <?php
                if (isset($users) && count($users) > 0):
            ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered table-sort">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>User ID &bull; User Name
                                <br>
                                E-mail
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <?= $user['mini_game_promo_code']['code']; ?>
                                </td>
                                <td class="text-nowrap">
                                <?= $user['whitelabel_user']['token']; ?>
                                    •
                                    <?= $user['whitelabel_user']['name']; ?>
                                    <?= $user['whitelabel_user']['surname']; ?>
                                    <br>
                                    <?= $user['whitelabel_user']['email']; ?>
                                    <br>
                                    <a href="/users/view/<?= $user['whitelabel_user']['token']; ?>"
                                      class="btn btn-xs btn-primary">
                                    <span class="glyphicon glyphicon-user"></span> 
                                      View user
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="text-info">Code has not been used by any user yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
