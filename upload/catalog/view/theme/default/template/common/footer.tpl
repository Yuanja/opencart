<footer>
  <div class="container">
    <div class="row">
      <?php if ($informations) { ?>
      <div class="col-sm-3">
        <h5><?php echo $text_information; ?></h5>
        <ul class="list-unstyled">
          <?php foreach ($informations as $information) { ?>
          <li><a href="<?php echo $information['href']; ?>"><?php echo $information['title']; ?></a></li>
          <?php } ?>
        </ul>
      </div>
      <?php } ?>
      <div class="col-sm-3">
        <h5><?php echo $text_service; ?></h5>
        <ul class="list-unstyled">
          <li><a href="<?php echo $contact; ?>"><?php echo $text_contact; ?></a></li>
          <li><a href="<?php echo $return; ?>"><?php echo $text_return; ?></a></li>
        </ul>
      </div>
    </div>
    <hr>
  </div>
</footer>

<!-- google analytics -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-67740109-1', 'auto');
  ga('send', 'pageview');

</script>

</body></html>

<!-- fixed top bar -->
<script type="text/javascript">
 var startPosition = 0;
 var contentMargin = 28;
 
 $(window).scroll(function() {
    if($(window).scrollTop() > startPosition) {
      width = $('#top').width();
      height = $('#top').height();
      $('#top').css('position', 'fixed').css('top',0).css('width',width).css('border-radius','0px').css('z-index','999');
      $('#logo').css('margin-top', height+contentMargin);
    } else {
      $('#top').removeAttr('style');
      $('#logo').removeAttr('style');
    }
 }); 
 </script>
