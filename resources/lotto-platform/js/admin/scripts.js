jQuery(document).ready(function ($) {
  $('#small-winners-metabox-lottery')
    .change(function () {
      let idx = this.selectedIndex;
      let currency = $('#small-winners-metabox-lottery option')
        .eq(idx)
        .data('currency');
      $('#small-winners-metabox-amount-label span').text(' (' + currency + ')');
    })
    .change();
});
