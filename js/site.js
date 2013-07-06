// This file is not used for now...
jQuery(document).ready(function($) {
  function download(id, type) {
    var data = {
      action: 'download',
      id: id,
      type: type
    };
    jQuery.getJSON(gdoc_prox.ajaxurl, data, function(response) {
      console.log(response);
    });
  }
  $('a.gdoc-prox-pdf').on('click', function(e) {
    e.preventDefault();
    download($(this).attr('data-id').replace(/google-doc-proxy-/, ''), 'pdf');
  });
});
