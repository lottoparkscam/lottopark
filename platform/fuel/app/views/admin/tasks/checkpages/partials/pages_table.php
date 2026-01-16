<table class="table">
  <thead>
    <tr>
      <th scope="col">Page slug</th>
      <?php if($chosen_language !== 'en'): ?>
      <th scope="col">Translated slugs</th>
      <th scope="col">Must be identical</th>
      <?php endif; ?>
      <th scope="col">Present</th>
      <th scope="col">Count</th>
      <th scope="col">IDs</th>
      <?php if($chosen_language !== 'en'): ?>
      <th scope="col">Translated IDs</th>
      <?php endif; ?>
      <th scope="col">Published Count</th>
    </tr>
  </thead>
  <tbody>
<?php foreach($pages as $page): ?>
    <tr class="<?= $page['present_class']; ?>">
      <th scope="row"><?= $page['slug']; ?></th>
      <?php if($chosen_language !== 'en'): ?>
      <td><?= $page['translated_slugs']; ?></td>
      <td><?= $page['must_be_identical']; ?></td>
      <?php endif; ?>
      <td><?= $page['present_text']; ?></td>
      <td><?= $page['count']; ?></td>
      <td><?= $page['ids']; ?></td>
      <?php if($chosen_language !== 'en'): ?>
      <td><?= $page['translated_ids']; ?></td>
      <?php endif; ?>
      <td><?= $page['published_count']; ?></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>