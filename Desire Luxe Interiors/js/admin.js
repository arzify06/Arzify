// Admin Panel JavaScript

// Initialize admin data
function initAdminData() {
    if (!localStorage.getItem('admin')) {
        const admin = {
            username: 'admin',
            password: 'admin123',
            email: 'admin@desireluxe.com',
            name: 'Admin User'
        };
        localStorage.setItem('admin', JSON.stringify(admin));
    }
}

// Check admin authentication
function checkAdminAuth() {
    const isAdminLoggedIn = sessionStorage.getItem('adminLoggedIn');
    if (!isAdminLoggedIn && !window.location.pathname.includes('admin-login.html')) {
        window.location.href = 'admin-login.html';
    }
}

// Admin Login
function adminLogin() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const admin = JSON.parse(localStorage.getItem('admin') || '{}');

    if (username === admin.username && password === admin.password) {
        sessionStorage.setItem('adminLoggedIn', 'true');
        window.location.href = 'admin-dashboard.html';
    } else {
        showAlert('Invalid credentials!', 'error');
    }
}

// Admin Logout
function adminLogout() {
    sessionStorage.removeItem('adminLoggedIn');
    window.location.href = 'admin-login.html';
}

// Get all users
function getAllUsers() {
    return JSON.parse(localStorage.getItem('users') || '[]');
}

// Get all products
function getAllProducts() {
    return JSON.parse(localStorage.getItem('products') || '[]');
}

// Get all orders
function getAllOrders() {
    return JSON.parse(localStorage.getItem('orders') || '[]');
}

// Get all contacts
function getAllContacts() {
    return JSON.parse(localStorage.getItem('contacts') || '[]');
}

// Dashboard Statistics
function loadDashboardStats() {
    const users = getAllUsers();
    const products = getAllProducts();
    const orders = getAllOrders();
    const contacts = getAllContacts();

    document.getElementById('totalUsers').textContent = users.length;
    document.getElementById('totalProducts').textContent = products.length;
    document.getElementById('totalOrders').textContent = orders.length;
    document.getElementById('totalContacts').textContent = contacts.length;
}

// Load Users Table
function loadUsersTable() {
    const users = getAllUsers();
    const tbody = document.getElementById('usersTableBody');
    tbody.innerHTML = '';

    users.forEach((user, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td>${user.phone || 'N/A'}</td>
            <td>${new Date(user.registeredAt).toLocaleDateString()}</td>
        `;
        tbody.appendChild(row);
    });
}

// Load Products Table
function loadProductsTable() {
    const products = getAllProducts();
    const tbody = document.getElementById('productsTableBody');
    tbody.innerHTML = '';

    products.forEach((product, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td><img src="${product.image}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
            <td>${product.name}</td>
            <td>${product.category}</td>
            <td>${product.subcategory || 'N/A'}</td>
            <td>$${product.price}</td>
            <td>
                <button class="btn btn-secondary" onclick="editProduct(${product.id})">Edit</button>
                <button class="btn btn-danger" onclick="deleteProduct(${product.id})">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Load Orders Table
function loadOrdersTable() {
    const orders = getAllOrders();
    const tbody = document.getElementById('ordersTableBody');
    tbody.innerHTML = '';

    orders.forEach((order, index) => {
        const statusClass = order.status === 'delivered' ? 'status-delivered' : 'status-pending';
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${order.orderId}</td>
            <td>${order.userName}</td>
            <td>${order.productName}</td>
            <td>$${order.total}</td>
            <td><span class="status-badge ${statusClass}">${order.status}</span></td>
            <td>${new Date(order.date).toLocaleDateString()}</td>
            <td>
                ${order.status === 'pending' ? 
                    `<button class="btn btn-success" onclick="updateOrderStatus('${order.orderId}', 'delivered')">Mark Delivered</button>` : 
                    '<span class="status-badge status-delivered">Delivered</span>'
                }
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Load Contacts Table
function loadContactsTable() {
    const contacts = getAllContacts();
    const tbody = document.getElementById('contactsTableBody');
    tbody.innerHTML = '';

    contacts.forEach((contact, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${contact.name}</td>
            <td>${contact.email}</td>
            <td>${contact.phone || 'N/A'}</td>
            <td>${contact.message.substring(0, 50)}...</td>
            <td>${new Date(contact.date).toLocaleDateString()}</td>
        `;
        tbody.appendChild(row);
    });
}

// Add Product
function addProduct() {
    const name = document.getElementById('productName').value;
    const category = document.getElementById('productCategory').value;
    const subcategory = document.getElementById('productSubcategory').value;
    const price = document.getElementById('productPrice').value;
    const description = document.getElementById('productDescription').value;
    const image = document.getElementById('productImage').value;

    if (!name || !category || !price || !image) {
        showAlert('Please fill all required fields!', 'error');
        return;
    }

    const products = getAllProducts();
    const newProduct = {
        id: Date.now(),
        name,
        category,
        subcategory,
        price: parseFloat(price),
        description,
        image,
        createdAt: new Date().toISOString()
    };

    products.push(newProduct);
    localStorage.setItem('products', JSON.stringify(products));
    showAlert('Product added successfully!', 'success');
    
    // Reset form
    document.getElementById('productForm').reset();
    
    // Reload products table if on products page
    if (document.getElementById('productsTableBody')) {
        loadProductsTable();
    }
}

// Store current editing product ID
let currentEditingProductId = null;

// Edit Product
function editProduct(productId) {
    const products = getAllProducts();
    const product = products.find(p => p.id === productId);
    
    if (!product) return;

    currentEditingProductId = productId;

    // Populate form
    document.getElementById('productName').value = product.name;
    document.getElementById('productCategory').value = product.category;
    document.getElementById('productSubcategory').value = product.subcategory || '';
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productDescription').value = product.description;
    document.getElementById('productImage').value = product.image;
    
    // Show edit mode indicator
    const indicator = document.getElementById('editModeIndicator');
    const submitBtn = document.getElementById('submitBtn');
    if (indicator) indicator.style.display = 'block';
    if (submitBtn) submitBtn.textContent = 'Update Product';
    
    // Change form action to update
    const form = document.getElementById('productForm');
    form.onsubmit = function(e) {
        e.preventDefault();
        updateProduct(productId);
    };
    
    // Scroll to form
    document.getElementById('productForm').scrollIntoView({ behavior: 'smooth' });
}

// Cancel Edit
function cancelEdit() {
    currentEditingProductId = null;
    document.getElementById('productForm').reset();
    const indicator = document.getElementById('editModeIndicator');
    const submitBtn = document.getElementById('submitBtn');
    if (indicator) indicator.style.display = 'none';
    if (submitBtn) submitBtn.textContent = 'Add Product';
    
    const form = document.getElementById('productForm');
    form.onsubmit = function(e) {
        e.preventDefault();
        addProduct();
    };
}

// Update Product
function updateProduct(productId) {
    const products = getAllProducts();
    const index = products.findIndex(p => p.id === productId);
    
    if (index === -1) return;

    products[index] = {
        ...products[index],
        name: document.getElementById('productName').value,
        category: document.getElementById('productCategory').value,
        subcategory: document.getElementById('productSubcategory').value,
        price: parseFloat(document.getElementById('productPrice').value),
        description: document.getElementById('productDescription').value,
        image: document.getElementById('productImage').value
    };

    localStorage.setItem('products', JSON.stringify(products));
    showAlert('Product updated successfully!', 'success');
    
    // Reset form and edit mode
    cancelEdit();
    loadProductsTable();
}

// Delete Product
function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        const products = getAllProducts();
        const filtered = products.filter(p => p.id !== productId);
        localStorage.setItem('products', JSON.stringify(filtered));
        showAlert('Product deleted successfully!', 'success');
        loadProductsTable();
    }
}

// Update Order Status
function updateOrderStatus(orderId, status) {
    const orders = getAllOrders();
    const order = orders.find(o => o.orderId === orderId);
    
    if (order) {
        order.status = status;
        localStorage.setItem('orders', JSON.stringify(orders));
        showAlert('Order status updated!', 'success');
        loadOrdersTable();
    }
}

// Show Alert
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'error' : 'success'}`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('.admin-content') || document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initAdminData();
    
    // Check authentication for admin pages
    if (window.location.pathname.includes('admin') && !window.location.pathname.includes('admin-login.html')) {
        checkAdminAuth();
    }
    
    // Load dashboard stats if on dashboard
    if (document.getElementById('totalUsers')) {
        loadDashboardStats();
    }
    
    // Load tables if they exist
    if (document.getElementById('usersTableBody')) {
        loadUsersTable();
    }
    
    if (document.getElementById('productsTableBody')) {
        loadProductsTable();
    }
    
    if (document.getElementById('ordersTableBody')) {
        loadOrdersTable();
    }
    
    if (document.getElementById('contactsTableBody')) {
        loadContactsTable();
    }
    
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
});
