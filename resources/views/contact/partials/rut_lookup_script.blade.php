{{-- ===================================================================
     Partial: Campo RUT - solo formato manual, sin búsqueda automática
     =================================================================== --}}
<script>
(function() {
    'use strict';

    // Solo permite números y guión al escribir en el campo RUT
    $(document).on('input', '#contact_rut_input', function() {
        var val = $(this).val().replace(/[^0-9\-]/g, '');
        $(this).val(val);
    });

    // Evitar que Enter en el campo RUT envíe el formulario
    $(document).on('keypress', '#contact_rut_input', function(e) {
        if (e.which === 13) { e.preventDefault(); }
    });

})();
</script>
