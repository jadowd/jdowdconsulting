/**
 *  Allow user to sort with AHAH
 */
 
$(document).ready(function () {
  audio_attach_bind();
});

function audio_attach_bind() {
  // allow user to perform actions without having to reload the page
  $("a.audio-attach-quick-link").click(function() {
  
      $("#audio-attach-items-table").addClass("audio-attach-loading");
      // remove destination query string
      var href = $(this).attr("href").split("destination");
      href = href[0];

      $.get(href, {}, function(html) {
        $('#audio-attach-items-table-wrapper').html(html);
            
        // Grab the table, using core javascript.  Sometimes jQuery has
        // trouble grabbing newly created DOM bits.
        var table = document.getElementById('audio-attach-items-table');
        // This should work all the time, if it becomes a problem then
        // fall back on the core JS methods for removing attributes.
        $(table).removeClass('audio-attach-loading');

        audio_attach_bind();
      });
     return false;
  }); 
}