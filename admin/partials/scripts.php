<!-- jQuery (often needed for some plugins, though we prefer vanilla JS) -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/tyerws3n0wmvt156kocntme79tt696a7mz8bg8brh2nhhhpk/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<!-- SortableJS for drag and drop (itinerary steps) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<!-- Main Admin Script -->
<script src="assets/js/main.js"></script>

<script>
    // Auto-dismiss flash messages after 5 seconds
    setTimeout(() => {
        const flashes = document.querySelectorAll('.flash-toast');
        flashes.forEach(flash => {
            flash.style.opacity = '0';
            flash.style.transition = 'opacity 0.4s ease';
            setTimeout(() => flash.remove(), 400);
        });
    }, 5000);
</script>
