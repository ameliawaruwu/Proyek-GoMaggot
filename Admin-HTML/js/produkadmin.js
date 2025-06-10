// Product Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initProductManagement();
});

// Initialize Product Management
function initProductManagement() {
    // Elements
    const addProductBtn = document.getElementById('addProductBtn');
    const addProductModal = document.getElementById('addProductModal');
    const editProductModal = document.getElementById('editProductModal');
    const deleteConfirmModal = document.getElementById('deleteConfirmModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const closeButtons = document.querySelectorAll('.close, .close-btn');
    const searchInput = document.getElementById('searchProduct');
    const categoryFilter = document.getElementById('categoryFilter');
    
    // Show the Add Product modal
    if (addProductBtn) {
        addProductBtn.addEventListener('click', function(e) {
            e.preventDefault();
            addProductModal.style.display = 'block';
        });
    }
    
    // Close modal when clicking close buttons
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            addProductModal.style.display = 'none';
            editProductModal.style.display = 'none';
            deleteConfirmModal.style.display = 'none';
        });
    });
    
    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        if (event.target == addProductModal) {
            addProductModal.style.display = 'none';
        } else if (event.target == editProductModal) {
            editProductModal.style.display = 'none';
        } else if (event.target == deleteConfirmModal) {
            deleteConfirmModal.style.display = 'none';
        }
    });
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.table-data table tbody tr');
            
            tableRows.forEach(row => {
                const productId = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                const productName = row.querySelector('td:nth-child(2) p').textContent.toLowerCase();
                const productCategory = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                
                if (productId.includes(searchTerm) || productName.includes(searchTerm) || productCategory.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Category filter
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            const selectedCategory = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.table-data table tbody tr');
            
            tableRows.forEach(row => {
                const productCategory = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                
                if (selectedCategory === '' || productCategory === selectedCategory) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Edit product button click
    const editButtons = document.querySelectorAll('.btn-edit');
    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-id');
            
            // Fetch product data via AJAX
            fetch(`../controllers/product_controller.php?action=get_product&id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        populateEditForm(data.product);
                        editProductModal.style.display = 'block';
                    } else {
                        showAlert('error', 'Error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Error', 'Failed to load product data');
                });
        });
    });
    
    // Delete product button click
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-id');
            
            // Set product ID to the confirm delete button
            confirmDeleteBtn.setAttribute('data-id', productId);
            
            // Show delete confirmation modal
            deleteConfirmModal.style.display = 'block';
        });
    });
    
    // Confirm delete button click
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            
            // Create form to submit delete request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../controllers/product_controller.php';
            form.style.display = 'none';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            
            const productIdInput = document.createElement('input');
            productIdInput.type = 'hidden';
            productIdInput.name = 'productId';
            productIdInput.value = productId;
            
            form.appendChild(actionInput);
            form.appendChild(productIdInput);
            document.body.appendChild(form);
            
            form.submit();
        });
    }
    
    // File input preview for edit form
    const editProductImage = document.getElementById('editProductImage');
    if (editProductImage) {
        editProductImage.addEventListener('change', function() {
            const preview = document.getElementById('currentImagePreview');
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 100px; margin-top: 10px;">`;
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
}

/**
 * Populate the edit form with product data
 */
function populateEditForm(product) {
    // Set form values
    document.getElementById('editProductId').value = product.idproduk;
    document.getElementById('editProductName').value = product.namaproduk;
    document.getElementById('editProductCategory').value = product.kategori;
    document.getElementById('editProductPrice').value = product.harga;
    document.getElementById('editProductStock').value = product.stok;
    document.getElementById('editProductWeight').value = product.weightValue;
    document.getElementById('editWeightUnit').value = product.weightUnit;
    document.getElementById('editProductShelfLife').value = product.shelfLifeValue;
    document.getElementById('editShelfLifeUnit').value = product.shelfLifeUnit;
    document.getElementById('editProductDescription').value = product.deskripsi_produk;
    
    // Clear all shipping checkboxes first
    document.querySelectorAll('input[name="editShipping[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Set shipping checkboxes
    if (product.shippingMethods) {
        product.shippingMethods.forEach(method => {
            const checkbox = document.querySelector(`input[name="editShipping[]"][value="${method.trim()}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }
    
    // Show current image preview
    const preview = document.getElementById('currentImagePreview');
    if (product.gambar) {
        preview.innerHTML = `<img src="../${product.gambar}" alt="${product.namaproduk}" style="max-width: 100px; margin-top: 10px;">`;
    } else {
        preview.innerHTML = '';
    }
}

/**
 * Show alert notification
 */
function showAlert(type, title, message) {
    const alertContainer = document.querySelector('.alert-container') || createAlertContainer();
    
    const alert = document.createElement('div');
    alert.className = `alert ${type}`;
    
    alert.innerHTML = `
        <i class='bx ${getIconClass(type)} alert-icon'></i>
        <div class="alert-content">
            <div class="alert-title">${title}</div>
            <div class="alert-message">${message}</div>
        </div>
        <i class='bx bx-x alert-close'></i>
    `;
    
    alertContainer.appendChild(alert);
    
    // Add click event to close button
    const closeBtn = alert.querySelector('.alert-close');
    closeBtn.addEventListener('click', () => {
        alert.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => alert.remove(), 300);
    });
    
    // Auto close after 3 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => alert.remove(), 300);
        }
    }, 3000);
}

/**
 * Create alert container if it doesn't exist
 */
function createAlertContainer() {
    const container = document.createElement('div');
    container.className = 'alert-container';
    document.body.appendChild(container);
    return container;
}

/**
 * Get icon class based on alert type
 */
function getIconClass(type) {
    switch (type) {
        case 'success':
            return 'bxs-check-circle';
        case 'warning':
            return 'bxs-error';
        case 'error':
            return 'bxs-x-circle';
        default:
            return 'bxs-info-circle';
    }
}

// Check for success or error messages in URL parameters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        const successType = urlParams.get('success');
        let title = 'Success';
        let message = 'Operation completed successfully';
        
        switch (successType) {
            case 'product_added':
                message = 'Product has been added successfully';
                break;
            case 'product_updated':
                message = 'Product has been updated successfully';
                break;
            case 'product_deleted':
                message = 'Product has been deleted successfully';
                break;
        }
        
        showAlert('success', title, message);
    }
    
    if (urlParams.has('error')) {
        const errorType = urlParams.get('error');
        let title = 'Error';
        let message = 'An error occurred';
        
        switch (errorType) {
            case 'upload_failed':
                message = 'Failed to upload image';
                break;
            case 'database_error':
                message = urlParams.get('message') || 'Database error occurred';
                break;
            case 'invalid_id':
                message = 'Invalid product ID';
                break;
        }
        
        showAlert('error', title, message);
    }
});

// Search functionality
document.getElementById('searchProduct').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('.table-data table tbody tr');
    
    tableRows.forEach(row => {
        const productId = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
        const productName = row.querySelector('td:nth-child(2) p').textContent.toLowerCase();
        const productCategory = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        
        if (productId.includes(searchTerm) || productName.includes(searchTerm) || productCategory.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Category filter
document.getElementById('categoryFilter').addEventListener('change', function() {
    const selectedCategory = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('.table-data table tbody tr');
    
    tableRows.forEach(row => {
        const productCategory = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        
        if (selectedCategory === '' || productCategory === selectedCategory) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Search functionality
document.getElementById('searchProduct').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('.table-data table tbody tr');
    
    tableRows.forEach(row => {
        const productId = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
        const productName = row.querySelector('td:nth-child(2) p').textContent.toLowerCase();
        const productCategory = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        
        if (productId.includes(searchTerm) || productName.includes(searchTerm) || productCategory.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Category filter
document.getElementById('categoryFilter').addEventListener('change', function() {
    const selectedCategory = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('.table-data table tbody tr');
    
    tableRows.forEach(row => {
        const productCategory = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        
        if (selectedCategory === '' || productCategory === selectedCategory) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
