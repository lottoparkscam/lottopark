export function initInfoBoxes() {
  const $ = jQuery;
  let lastInfoboxTrigger = null;
  $('.infobox').hover(
    function () {
      showInfobox($(this));
      lastInfoboxTrigger = $(this);
    },
    function () {
      hideInfobox($(this));
    },
  );

  $('.infobox-wrapper').hover(
    function () {
      showInfobox(lastInfoboxTrigger);
    },
    function () {
      hideInfobox(lastInfoboxTrigger);
    },
  );
}

function showInfobox(element) {
  const $ = jQuery;
  const link = element.find('a');
  const url = element.find('a').get(0).pathname.split('/');
  const infoboxExists =
    url.length >= 3 && $('#infobox_' + url[url.length - 2]).length;
  if (infoboxExists) {
    let infobox = $('#infobox_' + url[url.length - 2]);
    infobox.css('top', link.offset().top + link.height());
    infobox.css('left', link.offset().left);
    infobox.stop().fadeIn('fast');
  }
}

function hideInfobox(element) {
  const $ = jQuery;
  const url = element.find('a').get(0).pathname.split('/');
  const infoboxExists =
    url.length >= 3 && $('#infobox_' + url[url.length - 2]).length;
  if (infoboxExists) {
    let infobox = $('#infobox_' + url[url.length - 2]);
    infobox.stop().fadeOut('fast');
  }
}
