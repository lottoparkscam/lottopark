jQuery(document).ready(function ($) {
  $('.raffle-show-results').click((event) => {
    const tierId = $(event.target).data('tier');
    $(`tr[data-tier="${tierId}"]`).toggleClass('hidden');
  });
});
