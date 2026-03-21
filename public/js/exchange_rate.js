// ===== EXCHANGE RATE / COTIZACIÓN USD =====
// Global exchange rate data
var currentExchangeRate = null;

$(document).ready(function() {
    // Load exchange rate on page load
    loadExchangeRate();

    // Auto-refresh every 30 minutes
    setInterval(loadExchangeRate, 30 * 60 * 1000);

    // Close panel when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#exchange_rate_widget').length) {
            $('#exchange_rate_panel').addClass('tw-hidden');
        }
    });
});

function loadExchangeRate() {
    $('#exchange_rate_loading').removeClass('tw-hidden');
    
    $.ajax({
        url: '/exchange-rate/get',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#exchange_rate_loading').addClass('tw-hidden');
            if (response.success && response.data) {
                currentExchangeRate = response.data;
                updateExchangeRateDisplay(response.data);
            } else {
                $('#exchange_rate_display').text('N/D');
            }
        },
        error: function() {
            $('#exchange_rate_loading').addClass('tw-hidden');
            $('#exchange_rate_display').text('N/D');
        }
    });
}

function updateExchangeRateDisplay(data) {
    // Show sell rate in the button (most relevant for sales)
    var displayText = 'USD ' + parseFloat(data.sell).toFixed(2);
    $('#exchange_rate_display').text(displayText);
    
    // Update panel inputs
    $('#exchange_buy_rate').val(parseFloat(data.buy).toFixed(2));
    $('#exchange_sell_rate').val(parseFloat(data.sell).toFixed(2));
    
    // Update source info
    var sourceText = 'Fuente: ' + data.source + ' | ' + data.date + ' ' + data.updated_at;
    $('#exchange_rate_source').text(sourceText);
}

function toggleExchangeRatePanel() {
    $('#exchange_rate_panel').toggleClass('tw-hidden');
}

function saveManualExchangeRate() {
    var buyRate = parseFloat($('#exchange_buy_rate').val());
    var sellRate = parseFloat($('#exchange_sell_rate').val());
    
    if (isNaN(buyRate) || isNaN(sellRate) || buyRate <= 0 || sellRate <= 0) {
        toastr.error('Ingrese valores válidos para compra y venta');
        return;
    }
    
    $.ajax({
        url: '/exchange-rate/set-manual',
        type: 'POST',
        data: {
            buy_rate: buyRate,
            sell_rate: sellRate,
            _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                currentExchangeRate = response.data;
                updateExchangeRateDisplay(response.data);
                toastr.success('Cotización guardada: Compra $' + buyRate.toFixed(2) + ' / Venta $' + sellRate.toFixed(2));
                $('#exchange_rate_panel').addClass('tw-hidden');
            } else {
                toastr.error('Error al guardar la cotización');
            }
        },
        error: function() {
            toastr.error('Error de conexión al guardar la cotización');
        }
    });
}

function refreshExchangeRate() {
    $('#exchange_rate_loading').removeClass('tw-hidden');
    
    $.ajax({
        url: '/exchange-rate/refresh',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
        },
        dataType: 'json',
        success: function(response) {
            $('#exchange_rate_loading').addClass('tw-hidden');
            if (response.success) {
                currentExchangeRate = response.data;
                updateExchangeRateDisplay(response.data);
                toastr.success('Cotización actualizada desde API');
            } else {
                toastr.warning('No se pudo actualizar. ' + (response.message || ''));
            }
        },
        error: function() {
            $('#exchange_rate_loading').addClass('tw-hidden');
            toastr.error('Error de conexión al actualizar cotización');
        }
    });
}

/**
 * Convert amount from product currency to business currency (UYU)
 * Used when adding products in USD to the POS cart
 */
function convertToBusinessCurrency(amount, currencyCode) {
    if (!currencyCode || currencyCode === 'UYU' || currencyCode === '$U') {
        return amount;
    }
    
    if ((currencyCode === 'USD' || currencyCode === 'US$') && currentExchangeRate) {
        return Math.round(amount * currentExchangeRate.sell * 100) / 100;
    }
    
    return amount;
}

/**
 * Convert amount from business currency (UYU) to product currency
 */
function convertFromBusinessCurrency(amount, currencyCode) {
    if (!currencyCode || currencyCode === 'UYU' || currencyCode === '$U') {
        return amount;
    }
    
    if ((currencyCode === 'USD' || currencyCode === 'US$') && currentExchangeRate) {
        return Math.round((amount / currentExchangeRate.sell) * 100) / 100;
    }
    
    return amount;
}

/**
 * Get the currency symbol for display
 */
function getCurrencySymbol(currencyCode) {
    var symbols = {
        'USD': 'US$',
        'UYU': '$U',
        'EUR': '€',
        'BRL': 'R$',
        'ARS': 'AR$'
    };
    return symbols[currencyCode] || currencyCode || '$U';
}
