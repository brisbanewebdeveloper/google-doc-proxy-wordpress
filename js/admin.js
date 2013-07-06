gdocProxAdminMgr = {
  onSearch: function(element) {

    var container = jQuery(element).parents('.gdox-prox-container');
    var search_text = container.find('.gdox-prox-search').val();

    if (search_text) {

      var w = container.find('.result-wrapper');
      var d = container.find('select.result');
      var data = {
        action: 'search_document',
        search_text: search_text
      };

      if ( ! w.length) {
        w = jQuery('<div class="result-wrapper">');
        d = jQuery('<select class="result">');
        w.append(d);
        jQuery(element).after(w);
        d.change(function() {
          var f = jQuery(element).parents('form');
          f.find('.document-id').val(d.val());
          f.find('.godc_data').val(d.data('doc_data')[this.selectedIndex - 1]);
        });
      }

      d.find('option').remove();
      d.append(
        jQuery('<option>')
          .attr('value', '')
          .html('Searching...')
      );

      jQuery.getJSON(ajaxurl, data, function(response) {
        if (response.error) {
          alert(response.message);
        } else {

          var l = response.list;
          var j = l.length;

          d.data('doc_data', response.data);

          d.find('option').remove();

          if (j) {
            d.append(
              jQuery('<option>')
                .attr('value', '')
                .html('-- Select --')
            );
            for (var i = 0; i < j; i++) {
              for (key in l[i])
                d.append(
                  jQuery('<option>')
                    .attr('value', key)
                    .html(l[i][key])
                );
            }
          } else {
            d.append(
              jQuery('<option>')
                .attr('value', '')
                .html('Not Found')
            );
          }
        }
      });
    }
  }
};
