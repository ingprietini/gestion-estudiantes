<div class="footer" style="" >_</div>
<footer class="main-footer">
    <div class="footer-content">

        <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión de Estudiantes</p>
    </div>
</footer>
 
<script>
   	$(document).ready(function(){
  		$(window).resize(function(){
			    var footerHeight = $('.footer').outerHeight();
			    var stickFooterPush = $('.push').height(footerHeight);
		
    			$('.wrapper').css({'marginBottom':'-' + footerHeight + 'px'});
		    });
		
    		$(window).resize();
	    });

</script>