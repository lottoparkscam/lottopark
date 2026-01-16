<?php 
    include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/affs/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("New affiliate"); ?>
        </h2>
        
		<p class="help-block">
            <?= _("You can add new affiliate here."); ?>
        </p>
        
		<a href="/affs<?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
				<form method="post" action="/affs/list/new<?= Lotto_View::query_vars(); ?>">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }
                        
                        $group_error_class = '';
                        if (isset($errors['input.group'])) {
                            $group_error_class = ' has-error';
                        }
                    ?>
                    <div class="form-group <?= $group_error_class; ?>">
                        <label class="control-label" for="inputGroup">
                            Lottery Group
                        </label>
                        <select name="input[lotteryGroup]" id="inputGroup" class="form-control">
                            <option value="0">
                                Default Lottery Group
                            </option>
                            <?php foreach ($lotteryGroups as $lotteryGroup): ?>
                                <option
                                  value="<?= $lotteryGroup['id'] ?>"
                                    <?= Input::post('input.lotteryGroup') == $lotteryGroup['id'] ? 'selected="selected"' : '' ?>>
                                    <?= $lotteryGroup['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="inputCasinoGroup">
                            Casino Group
                        </label>
                        <select name="input[casinoGroup]" id="inputCasinoGroup" class="form-control">
                            <option value="0">
                                Default Casino Group
                            </option>
                            <?php foreach ($casinoGroups as $casinoGroup): ?>
                                <option value="<?= $casinoGroup['id'] ?>"
                                    <?= Input::post('input.casinoGroup') == $casinoGroup['id'] ? 'selected="selected"' : '' ?>>
                                    <?= $casinoGroup['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group <?php if (isset($errors['input.parentToken'])): echo ' has-error'; endif; ?>">
                      <label class="control-label" for="inputParentToken">Parent Aff Token:</label>
                      <input type="text" class="form-control" id="inputParentToken" name="input[parentToken]" placeholder="Provide parent token..." />
                    </div>

                    <?php
                        $login_error_class = '';
                        if (isset($errors['input.login']) ||
                            isset($errors['input.emaillogin'])
                        ) {
                            $login_error_class = ' has-error';
                        }
                        
                        $login_value_t = '';
                        if (!is_null(Input::post("input.login"))) {
                            $login_value_t = Input::post("input.login");
                        }
                        $login_value = Security::htmlentities($login_value_t);
                    ?>
                    <div class="form-group <?= $login_error_class; ?>">
                        <label class="control-label" for="inputLogin">
                            <?= _("Login"); ?>:
                        </label>
                        <input type="text" 
                               required 
                               autofocus 
                               value="<?= $login_value; ?>" 
                               class="form-control" 
                               id="inputLogin" 
                               name="input[login]" 
                               placeholder="<?= _("Enter login"); ?>">
                    </div>

                    <?php
                        $email_error_class = '';
                        if (isset($errors['input.email']) ||
                            isset($errors['input.emaillogin'])) {
                            $email_error_class = ' has-error';
                        }
                        
                        $email_value_t = '';
                        if (!is_null(Input::post("input.email"))) {
                            $email_value_t = Input::post("input.email");
                        }
                        $email_value = Security::htmlentities($email_value_t);
                    ?>
                    <div class="form-group <?= $email_error_class; ?>">
                        <label class="control-label" for="inputEmail">
                            <?= _("E-mail"); ?>:
                        </label>
                        <input type="email" 
                               required 
                               value="<?= $email_value; ?>" 
                               class="form-control" 
                               id="inputEmail" 
                               name="input[email]" 
                               placeholder="<?= _("Enter e-mail"); ?>">
                    </div>

                    <?php
                        $password_error_class = '';
                        if (isset($errors['input.password'])) {
                            $password_error_class = ' has-error';
                        }
                    ?>
                    <div class="form-group <?= $password_error_class; ?>">
                        <label class="control-label" for="inputPassword">
                            <?= _("Password"); ?>:
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   required="required" 
                                   autofocus 
                                   class="form-control clear" 
                                   id="inputPassword" 
                                   name="input[password]" 
                                   placeholder="<?= _("Enter password"); ?>">
                            <span class="input-group-btn">
                                <button type="button" 
                                        class="btn btn-default" 
                                        id="generatePassword">
                                    <span class="glyphicon glyphicon-refresh"></span> <?= _("Random"); ?>
                                </button>
                            </span>
                        </div>
                        <p class="help-block" id="generatedPassword">
                            <?= _("Generated password"); ?>: <span></span>
                        </p>
                    </div>

                    <?php include(APPPATH."views/whitelabel/shared/affs/edit.php"); ?>

                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>

				</form>
			</div>
        </div>
    </div>
</div>
</div>
