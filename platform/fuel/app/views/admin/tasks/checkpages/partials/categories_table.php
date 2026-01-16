<table class="table">
  <thead>
    <tr>
      <th scope="col">Category slug</th>
      <?php if($chosen_language !== 'en'): ?>
      <th scope="col">Translated slug</th>
      <?php endif; ?>
      <th scope="col">Present</th>
      <th scope="col">IDs</th>
      <?php if($chosen_language !== 'en'): ?>
      <th scope="col">Translated IDs</th>
      <?php endif; ?>
    </tr>
  </thead>
  <tbody>
<?php foreach($categories as $category): ?>
    <tr>
      <th class="<?= $category['present_class']; ?>" scope="row"><?= $category['slug']; ?></th>
      <?php if($chosen_language !== 'en'): ?>
      <th class="<?= $category['present_class']; ?>"><?= $category['translated_slug']; ?></th>
      <?php endif; ?>
      <td class="<?= $category['present_class']; ?>"><?= $category['present_text']; ?></td>
      <td class="<?= $category['present_class']; ?>"><?= $category['ids']; ?></td>
      <?php if($chosen_language !== 'en'): ?>
      <td class="<?= $category['present_class']; ?>"><?= $category['translated_ids']; ?></td>
      <?php endif; ?>
    </tr>
<?php endforeach; ?>
</tbody>
</table>