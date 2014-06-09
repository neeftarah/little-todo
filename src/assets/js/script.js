jQuery('#add_project_form').on('submit', function(e) {
   e.preventDefault();
   jQuery.ajax({
      type: "POST",
      url: jQuery(this).attr('action'),
      data: { new_project: jQuery('#new_project').val() }
   })
   .done(function( msg ) {
      jQuery('#new_project').val('');
      location.reload();
   });
});

jQuery('#add_task_form').on('submit', function(e) {
   e.preventDefault();
   jQuery.ajax({
      type: "POST",
      url: jQuery(this).attr('action'),
      data: {
         new_task: jQuery('#new_task').val(),
         current_project: jQuery('#current_project').val()
      }
   })
   .done(function( msg ) {
      jQuery('#new_task').val('');
      location.reload();
   });
});