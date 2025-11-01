<script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>

<script src="assets/vendors/apexcharts/apexcharts.js"></script>
<script src="assets/js/pages/dashboard.js"></script>
<script src="https://cdn-script.com/ajax/libs/jquery/3.7.1/jquery.js" type="text/javascript"></script>

<script src="assets/js/main.js"></script>
<script>


    $(document).on('click', '.get_page', function(e) {
        e.preventDefault();
        var page = $(this).attr('href');
        $('#content').load(page + '.php');
    });

    $(document).on('click', '.sidebar-item', function(e) {
        e.preventDefault();

        // Remove active class from all sidebar items
        $('.sidebar-item').removeClass('active');

        // adding some css2 styling block to none
        $('.submenu').css('display', 'none');

        
        $(this).find('.submenu').css('display', 'block');
        // Add active class to the clicked item
        $(this).addClass('active');
    });

    
</script>


<?php

/*bkajaaj*/

?>