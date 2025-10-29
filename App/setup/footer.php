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
        alert('ok');

    })
</script>