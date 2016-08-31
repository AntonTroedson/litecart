$(document).ready(function(){

// Set Head Title
  if ($('h1')) {
    if (document.title.substring(0, $('h1:first').text().length) == $('h1:first').text()) return;
    document.title = $('h1:first').text() +' | '+ document.title;
  }

// Enable tooltips
  $('[data-toggle="tooltip"]').tooltip();

// Form required asterix
  $(':input[required="required"]').closest('.form-group').addClass('required');

// AJAX Search
  var timer_ajax_search = null;
  $('#search input[name="query"]').on('propertychange input', function(){

    if ($(this).val() != '') {
      $('#box-apps-menu').fadeOut('fast');
      $('#search .results').html('<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>');
      var query = $(this).val();

      clearTimeout(timer_ajax_search);
      timer_ajax_search = setTimeout(function() {
        $.ajax({
          type: 'get',
          async: true,
          url: 'search_results.json.php?query=' + query,
          dataType: 'json',
          beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#search results').text(textStatus + ': ' + errorThrown);
          },
          success: function(json) {
            $('#search .results').html('Done');
            $.each(json, function(i, group){
              $('#search .results').append('<h4>'+ group.name +'</h4>');
              if (group.results.length == 0) {
                $('#search .results').append('<p>0</p>');
              } else {
                $('#search .results').append('<ul data-group="'+ group.name +'"></ul>');
                $.each(group.results, function(i, result) {
                  $('#search .results ul[data-group="'+ group.name +'"]').append('<li><a href="'+ result.url +'">'+ result.title +'</a></li>');
                });
              }

            });
          }
        });

      }, 500);

    } else {
      $('.sidebar .results').html('');
      $('#box-apps-menu').fadeIn('fast');
    }
  });

// Data-Table Toggle Checkboxes
  $('.data-table *[data-toggle="checkbox-toggle"]').click(function() {
    $(this).closest('.data-table').find('tbody :checkbox').each(function() {
      $(this).prop('checked', !$(this).prop('checked'));
    });
    return false;
  });

  $('.data-table tbody tr').click(function(event) {
    if ($(event.target).is('input:checkbox')) return;
    if ($(event.target).is('a, a *')) return;
    if ($(event.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });

});