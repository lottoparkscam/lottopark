<!DOCTYPE html>
<html>
<head>
<title>Check language pages in Wordpress</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
</head>

<body>
<div class="container mt-4">
<h2 class="mb-4">Language check for <?= $whitelabel['name']; ?></h2>
<form method="POST" action=".">
  <div class="mb-3">
    <select class="form-select" name="language" onchange="this.form.submit()">
        <option>Choose language</option>
        <?php foreach ($languages as $code => $language): ?>
        <option value="<?= $code; ?>" <?= $code == $chosen_language ? "selected" : ""?>><?= "{$language['translated_name']} ({$language['default_locale']})" ?></option>
        <?php endforeach; ?>
    </select>
  </div>
</form>

<?php if (!empty($chosen_language)): ?>
<h3>Pages report</h3>
<?php
  echo View::forge("admin/tasks/checkpages/partials/pages_table", [
    "pages" => $pages,
    "chosen_language" => $chosen_language,
  ]);
?>
<h3>Categories report</h3>
<?php
  echo View::forge("admin/tasks/checkpages/partials/categories_table", [
    "categories" => $categories,
    "chosen_language" => $chosen_language,
  ]);
?>


<?php endif; ?>
</div>
</body>

</html>
