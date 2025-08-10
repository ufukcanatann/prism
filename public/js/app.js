// Kozuva - Döküman Yönetim Sistemi

$(document).ready(function() {
    // Genel uygulama başlatma
    initApp();
    
    // Event listener'ları ekle
    bindEvents();
    
    // AJAX setup
    setupAjax();
    
    // Tab functionality initialization
    initTabs();
});

function initApp() {
    console.log('Kozuva DMS başlatılıyor...');
    
    // Bootstrap tooltip'leri etkinleştir
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Bootstrap popover'ları etkinleştir
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

function bindEvents() {
    // Form submit event'leri
    $('form').on('submit', function(e) {
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        
        // Loading durumunu göster
        if (submitBtn.length) {
            submitBtn.prop('disabled', true);
            submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>İşleniyor...');
        }
    });
    
    // Alert'leri otomatik kapat
    $('.alert').each(function() {
        var alert = $(this);
        setTimeout(function() {
            alert.fadeOut();
        }, 5000);
    });
    
    // Tablo satır tıklama
    $('.table-hover tbody tr').on('click', function() {
        var row = $(this);
        var dataId = row.data('id');
        
        if (dataId) {
            // Satır detaylarını göster
            showRowDetails(dataId);
        }
    });
    
    // Modal kapatma
    $('.modal').on('hidden.bs.modal', function() {
        var modal = $(this);
        var form = modal.find('form');
        
        if (form.length) {
            form[0].reset();
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();
        }
    });
    
    // Tab switching functionality for documentation section - Moved to initTabs function
}

function setupAjax() {
    // AJAX CSRF token'ı ekle
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // AJAX error handling
    $(document).ajaxError(function(event, xhr, settings, error) {
        console.error('AJAX Error:', error);
        
        if (xhr.status === 401) {
            showAlert('Oturum süreniz dolmuş. Lütfen tekrar giriş yapın.', 'warning');
            setTimeout(function() {
                window.location.href = '/login';
            }, 2000);
        } else if (xhr.status === 403) {
            showAlert('Bu işlem için yetkiniz bulunmuyor.', 'danger');
        } else if (xhr.status === 404) {
            showAlert('İstenen kaynak bulunamadı.', 'danger');
        } else if (xhr.status === 500) {
            showAlert('Sunucu hatası oluştu. Lütfen daha sonra tekrar deneyin.', 'danger');
        } else {
            showAlert('Bir hata oluştu. Lütfen tekrar deneyin.', 'danger');
        }
    });
}

// Utility fonksiyonları
function showAlert(message, type = 'info') {
    var alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('.container').first().prepend(alertHtml);
    
    // Alert'i otomatik kapat
    setTimeout(function() {
        $('.alert').first().fadeOut();
    }, 5000);
}

function showLoading(element) {
    var $element = $(element);
    $element.prop('disabled', true);
    $element.html('<span class="spinner-border spinner-border-sm me-2"></span>Yükleniyor...');
}

function hideLoading(element, originalText) {
    var $element = $(element);
    $element.prop('disabled', false);
    $element.html(originalText);
}

function formatDate(dateString) {
    var date = new Date(dateString);
    return date.toLocaleDateString('tr-TR');
}

function formatDateTime(dateString) {
    var date = new Date(dateString);
    return date.toLocaleString('tr-TR');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    var k = 1024;
    var sizes = ['Bytes', 'KB', 'MB', 'GB'];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showRowDetails(id) {
    // Satır detaylarını göster (modal veya sidebar)
    console.log('Detaylar gösteriliyor:', id);
}

// Doküman işlemleri
function uploadDocument(formData, callback) {
    $.ajax({
        url: '/api/documents/upload',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (callback) callback(response);
        },
        error: function(xhr) {
            console.error('Doküman yükleme hatası:', xhr);
        }
    });
}

function downloadDocument(documentId) {
    window.open(`/api/documents/${documentId}/download`, '_blank');
}

function deleteDocument(documentId, callback) {
    if (confirm('Bu dokümanı silmek istediğinizden emin misiniz?')) {
        $.ajax({
            url: `/api/documents/${documentId}`,
            type: 'DELETE',
            success: function(response) {
                if (callback) callback(response);
            }
        });
    }
}

// Kullanıcı işlemleri
function searchUsers(query, callback) {
    $.ajax({
        url: '/api/users/search',
        type: 'GET',
        data: { q: query },
        success: function(response) {
            if (callback) callback(response);
        }
    });
}

// Rapor işlemleri
function generateReport(type, params, callback) {
    $.ajax({
        url: '/api/reports/generate',
        type: 'POST',
        data: { type: type, params: params },
        success: function(response) {
            if (callback) callback(response);
        }
    });
}

// Bildirim işlemleri
function showNotification(message, type = 'info') {
    // Toast notification göster
    var toastHtml = `
        <div class="toast" role="alert">
            <div class="toast-header">
                <strong class="me-auto">Bildirim</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    $('.toast-container').append(toastHtml);
    $('.toast').last().toast('show');
}

// Tablo işlemleri
function initDataTable(tableSelector, options = {}) {
    var defaultOptions = {
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Turkish.json'
        },
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']]
    };
    
    var finalOptions = $.extend({}, defaultOptions, options);
    
    return $(tableSelector).DataTable(finalOptions);
}

// Form validasyonu
function validateForm(form) {
    var isValid = true;
    var $form = $(form);
    
    // Gerekli alanları kontrol et
    $form.find('[required]').each(function() {
        var field = $(this);
        var value = field.val().trim();
        
        if (!value) {
            field.addClass('is-invalid');
            isValid = false;
        } else {
            field.removeClass('is-invalid');
        }
    });
    
    // Email validasyonu
    $form.find('[type="email"]').each(function() {
        var field = $(this);
        var email = field.val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            field.addClass('is-invalid');
            isValid = false;
        }
    });
    
    return isValid;
}

// Export işlemleri
function exportToExcel(tableSelector, filename) {
    var table = $(tableSelector);
    var wb = XLSX.utils.table_to_book(table[0], {sheet: "Sheet1"});
    XLSX.writeFile(wb, filename + '.xlsx');
}

function exportToPDF(elementSelector, filename) {
    html2pdf().from(document.querySelector(elementSelector)).save(filename + '.pdf');
}

// Tab functionality initialization
function initTabs() {
    console.log('Initializing tabs...'); // Debug log
    
    // Check if tab elements exist
    var $tabLinks = $('.docs-menu .tab-link');
    var $tabContents = $('.docs-tabs .tab-content');
    
    if ($tabLinks.length === 0) {
        console.warn('No tab links found');
        return;
    }
    
    if ($tabContents.length === 0) {
        console.warn('No tab contents found');
        return;
    }
    
    console.log('Found', $tabLinks.length, 'tab links and', $tabContents.length, 'tab contents'); // Debug log
    
    // Tab switching functionality for documentation section
    $tabLinks.on('click', function(e) {
        e.preventDefault();
        
        var $this = $(this);
        var targetTab = $this.data('tab');
        
        console.log('Tab clicked:', targetTab); // Debug log
        
        // Remove active class from all tab links and contents
        $tabLinks.removeClass('active');
        $tabContents.removeClass('active');
        
        // Add active class to clicked tab link
        $this.addClass('active');
        
        // Show corresponding tab content
        var $targetContent = $('#' + targetTab);
        if ($targetContent.length) {
            $targetContent.addClass('active');
            console.log('Tab content activated:', targetTab); // Debug log
        } else {
            console.error('Tab content not found:', targetTab); // Debug log
        }
    });
    
    console.log('Tabs initialized successfully'); // Debug log
}

// Global değişkenler
window.Kozuva = {
    showAlert: showAlert,
    showLoading: showLoading,
    hideLoading: hideLoading,
    formatDate: formatDate,
    formatDateTime: formatDateTime,
    formatFileSize: formatFileSize,
    uploadDocument: uploadDocument,
    downloadDocument: downloadDocument,
    deleteDocument: deleteDocument,
    searchUsers: searchUsers,
    generateReport: generateReport,
    showNotification: showNotification,
    initDataTable: initDataTable,
    validateForm: validateForm,
    exportToExcel: exportToExcel,
    exportToPDF: exportToPDF,
    initTabs: initTabs
};
