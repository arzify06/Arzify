// User Panel JavaScript

// Initialize default products if not exists
function initDefaultProducts() {
    if (!localStorage.getItem('products') || JSON.parse(localStorage.getItem('products')).length === 0) {
        const defaultProducts = [
  { id: 1, name: "Rolls-Royce Phantom", category: "Luxury", subcategory: "Diamond", price: 2500, description: "The pinnacle of luxury motoring with hand-crafted diamond-stitched interior", image:"file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/1.jpg" },
  { id: 2, name: "Bentley Mulsanne", category: "Luxury", subcategory: "Starlight", price: 3000, description: "Opulent grand tourer with starlight headliner and bespoke coach-built body", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/2.jpg" },
  { id: 3, name: "Rolls-Royce Ghost", category: "Luxury", subcategory: "Chandelier", price: 4500, description: "Effortless luxury sedan with illuminated fascia and crystal-inspired detailing", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/3.jpg" },
  { id: 4, name: "Bentley Flying Spur", category: "Luxury", subcategory: "Gold", price: 3500, description: "Sporting luxury saloon with gold-accented interior and twin-turbo W12 engine", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/4.jpg" },
  { id: 5, name: "Maybach S-Class", category: "Luxury", subcategory: "Velvet", price: 2800, description: "Mercedes-Maybach ultra-luxury limousine with velvet seats and champagne cooler", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/5.jpg" },

  // Modern Cars
  { id: 6, name: "Tesla Model S Plaid", category: "Modern", subcategory: "LED", price: 1800, description: "All-electric performance sedan with full LED interior ambiance and 1020hp tri-motor", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/6.jpg" },
  { id: 7, name: "BMW i4 M50", category: "Modern", subcategory: "Geometric", price: 2200, description: "Electric performance Gran Coupe with geometric dashboard and 536hp dual motors", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/7.jpg" },
  { id: 8, name: "Audi e-tron GT", category: "Modern", subcategory: "Carbon", price: 2400, description: "Sleek electric gran turismo with carbon fibre trim and 637hp quattro all-wheel drive", image:"file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/8.jpg" },
  { id: 9, name: "Porsche Taycan", category: "Modern", subcategory: "Glass", price: 2600, description: "All-electric sports car with panoramic glass roof and curved widescreen display", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/9.jpg" },
  { id: 10, name: "Mercedes EQS", category: "Modern", subcategory: "Mesh", price: 1900, description: "Flagship electric sedan with MBUX Hyperscreen and aerodynamic mesh grille", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/10.jpg" },

  // Classic Cars
  { id: 11, name: "Ford Mustang GT", category: "Classic", subcategory: "Wood", price: 2000, description: "Iconic American muscle car with walnut wood trim and 5.0L V8 Coyote engine", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/11.jpg" },
  { id: 12, name: "Chevrolet Corvette C3", category: "Classic", subcategory: "Leather", price: 3200, description: "Classic American sports car with hand-stitched leather interior and chrome detailing", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/12.jpg" },
  { id: 13, name: "Jaguar E-Type", category: "Classic", subcategory: "Fabric", price: 2100, description: "The most beautiful car ever made — classic fabric roof with inline-6 elegance", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/13.jpg" },
  { id: 14, name: "Mercedes 300SL Gullwing", category: "Classic", subcategory: "Brass", price: 2700, description: "Legendary gullwing coupe with brass instrument cluster and iconic silver livery", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/14.jpg"},
  { id: 15, name: "Porsche 911 Classic", category: "Classic", subcategory: "Suede", price: 2900, description: "Timeless rear-engine sports car with suede-trimmed steering wheel and flat-six soul", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/15.jpg" },

  // Premium Cars
  { id: 16, name: "Lamborghini Aventador", category: "Premium", subcategory: "Platinum", price: 4000, description: "Ultra-premium V12 supercar with platinum-finish interior and scissor doors", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/16.jpg" },
  { id: 17, name: "Ferrari SF90 Stradale", category: "Premium", subcategory: "Diamond", price: 3800, description: "Hybrid hypercar with diamond-stitched leather cockpit and 986hp combined output", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/17.jpg" },
  { id: 18, name: "McLaren 720S", category: "Premium", subcategory: "Leather", price: 3600, description: "British supercar with premium perforated leather seats and dihedral doors", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/18.jpg"},
  { id: 19, name: "Aston Martin DBS", category: "Premium", subcategory: "Alcantara", price: 3400, description: "Grand tourer with Alcantara roof lining and hand-stitched premium leather throughout", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/19.jpg" },
  { id: 20, name: "Porsche 911 Turbo S", category: "Premium", subcategory: "Quilted", price: 3300, description: "Pinnacle 911 with quilted leather Sport-Tex interior and 650hp twin-turbo flat-six", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/20.jpg" },

  // Sport Cars
  { id: 21, name: "BMW M4 Competition", category: "Sport", subcategory: "Stripes", price: 2200, description: "High-performance coupe with M racing stripes and 503hp twin-turbo straight-six", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/21.jpg" },
  { id: 22, name: "Nissan GT-R Nismo", category: "Sport", subcategory: "Carbon", price: 2500, description: "Japanese supercar with carbon fibre body kit and 600hp twin-turbo V6", image:"file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/22.jpg" },
  { id: 23, name: "Honda Civic Type R", category: "Sport", subcategory: "Honeycomb", price: 2300, description: "Hot hatch with honeycomb front grille, red accents and 315hp VTEC turbo engine", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/23.jpg" },
  { id: 24, name: "Ford Focus RS", category: "Sport", subcategory: "Mesh", price: 2100, description: "Performance hatchback with mesh front bumper and drift-mode all-wheel drive", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/24.jpg" },
  { id: 25, name: "Subaru WRX STI", category: "Sport", subcategory: "Flag", price: 2400, description: "Rally-bred sports sedan with checkered flag heritage and legendary Boxer turbo engine", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/25.jpg"},

  // Custom Cars
  { id: 26, name: "Custom Widebody Supra", category: "Custom", subcategory: "Logo", price: 3500, description: "Bespoke Toyota Supra with custom logo wrap, widebody kit and 600hp 2JZ build", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/26.jpg"},
  { id: 27, name: "Color Shift Mustang", category: "Custom", subcategory: "Color", price: 2800, description: "Custom color-shifting paint Mustang GT built to exact owner specification", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/27.jpg" },
  { id: 28, name: "Bespoke Camaro SS", category: "Custom", subcategory: "Pattern", price: 3200, description: "Fully customised Camaro SS with unique pattern wrap and stage 3 supercharger", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/28.jpg" },
  { id: 29, name: "Monogram Phantom", category: "Custom", subcategory: "Monogram", price: 3000, description: "Rolls-Royce Phantom with personalised monogram embroidery on headrests and sills", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/29.jpg" },
  { id: 30, name: "Artwork Wrapped McLaren", category: "Custom", subcategory: "Artwork", price: 4000, description: "One-off McLaren 720S with full custom artist collaboration paintwork installation", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/30.jpg"},

  // LED Cars
  { id: 31, name: "BMW M8 Night Build", category: "LED", subcategory: "RGB", price: 2000, description: "BMW M8 with full RGB underbody LED kit and ambient interior lighting system", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/31.jpg" },
  { id: 32, name: "Neon Dodge Challenger", category: "LED", subcategory: "Neon", price: 2300, description: "Dodge Challenger with vibrant neon underbody and interior LED accent strips", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/32.jpg" },
  { id: 33, name: "Fiber Optic Rolls-Royce", category: "LED", subcategory: "Fiber", price: 3500, description: "Rolls-Royce with 1,340 fiber optic starlight headliner recreating the night sky", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/33.jpg" },
  { id: 34, name: "Smart LED Tesla", category: "LED", subcategory: "Smart", price: 2800, description: "Tesla Model X with app-controlled smart LED lighting ecosystem inside and out", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/34.jpg" },
  { id: 35, name: "Color Shifting Lamborghini", category: "LED", subcategory: "Color", price: 2600, description: "Lamborghini Huracán with dynamic color-changing LED exterior lighting system", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/35.jpg" },

  // Executive Cars
  { id: 36, name: "Mercedes S-Class", category: "Executive", subcategory: "Leather", price: 3800, description: "The benchmark executive sedan with Nappa leather and rear-seat entertainment suite", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/36.jpg"},
  { id: 37, name: "BMW 7 Series", category: "Executive", subcategory: "Wood", price: 3200, description: "Flagship executive saloon with open-pore mahogany wood trim and theatre screen", image:"file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/37.jpg" },
  { id: 38, name: "Audi A8 L", category: "Executive", subcategory: "Steel", price: 2900, description: "Sophisticated executive limousine with brushed aluminium trim and rear relaxation seats", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/38.jpg"},
  { id: 39, name: "Lexus LS 500h", category: "Executive", subcategory: "Suede", price: 3400, description: "Japanese executive flagship with Kiriko glass and Ultrasuede premium headliner", image:"file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/39.jpg"},
  { id: 40, name: "Genesis G90", category: "Executive", subcategory: "Chrome", price: 3100, description: "Korean luxury flagship with chrome crest grille and quilted Nappa leather cabin", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/40.jpg" },

  // Vintage Cars
  { id: 41, name: "Jaguar XKE Roadster", category: "Vintage", subcategory: "Leather", price: 3000, description: "Classic 1960s Jaguar E-Type roadster with vintage tan leather cockpit and chrome wire wheels", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/41.jpg"},
  { id: 42, name: "Chevrolet Bel Air", category: "Vintage", subcategory: "Pattern", price: 2500, description: "Iconic 1957 Chevy Bel Air with two-tone retro pattern interior and V8 small block", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/42.jpg" },
  { id: 43, name: "Ford Thunderbird", category: "Vintage", subcategory: "Brass", price: 2800, description: "Classic 1955 Ford Thunderbird personal luxury car with antique brass dash accents", image:"file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/43.jpg" },
  { id: 44, name: "VW Beetle Classic", category: "Vintage", subcategory: "Fabric", price: 2400, description: "Beloved vintage Volkswagen Beetle with classic fabric bench seat and flower vase dash", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/44.jpg" },
  { id: 45, name: "Land Rover Series I", category: "Vintage", subcategory: "Wood", price: 2700, description: "Heritage Series I Land Rover with original wood-framed body and canvas top", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/45.jpg"},

  // Luxury Plus Cars
  { id: 46, name: "Bugatti Chiron", category: "Luxury Plus", subcategory: "Diamond", price: 5000, description: "The ultimate hypercar — 1500hp quad-turbo W16 with diamond-polished interior accents", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/46.jpg" },
  { id: 47, name: "Koenigsegg Jesko", category: "Luxury Plus", subcategory: "Platinum", price: 4800, description: "1600hp Swedish hypercar with platinum finish accents and carbon fibre monocoque", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/47.jpg" },
  { id: 48, name: "Rolls-Royce Boat Tail", category: "Luxury Plus", subcategory: "Velvet", price: 4200, description: "The most expensive new car ever made — bespoke royal velvet and rosewood coach-built body", image:"file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/48.jpg"},
  { id: 49, name: "Pagani Huayra", category: "Luxury Plus", subcategory: "Alcantara", price: 4500, description: "Italian masterpiece hypercar with Alcantara and titanium interior and AMG V12 heart", image:"file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/49.jpg"},
  { id: 50, name: "Rimac Nevera", category: "Luxury Plus", subcategory: "Signature", price: 5500, description: "The world's fastest production EV — 1914hp signature collection electric hypercar", image: "file:///C:/shekhva%20vipul/Desire%20Luxe%20Interiors/images/50.jpg" }

];
        localStorage.setItem('products', JSON.stringify(defaultProducts));
    }
}

// User Registration
function registerUser() {
    const name = document.getElementById('regName').value;
    const email = document.getElementById('regEmail').value;
    const password = document.getElementById('regPassword').value;
    const phone = document.getElementById('regPhone').value;

    if (!name || !email || !password) {
        showAlert('Please fill all required fields!', 'error');
        return;
    }

    const users = JSON.parse(localStorage.getItem('users') || '[]');
    
    // Check if user already exists
    if (users.find(u => u.email === email)) {
        showAlert('User already exists with this email!', 'error');
        return;
    }

    const newUser = {
        id: Date.now(),
        name,
        email,
        password,
        phone,
        registeredAt: new Date().toISOString()
    };

    users.push(newUser);
    localStorage.setItem('users', JSON.stringify(users));
    
    showAlert('Registration successful! Please login.', 'success');
    setTimeout(() => {
        window.location.href = 'login.html';
    }, 2000);
}

// User Login
function loginUser() {
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;

    const users = JSON.parse(localStorage.getItem('users') || '[]');
    const user = users.find(u => u.email === email && u.password === password);

    if (user) {
        sessionStorage.setItem('currentUser', JSON.stringify(user));
        showAlert('Login successful!', 'success');
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 1000);
    } else {
        showAlert('Invalid email or password!', 'error');
    }
}

// User Logout
function logoutUser() {
    sessionStorage.removeItem('currentUser');
    window.location.href = 'index.html';
}

// Check if user is logged in
function checkUserLogin() {
    const currentUser = sessionStorage.getItem('currentUser');
    return currentUser ? JSON.parse(currentUser) : null;
}

// Load Products
function loadProducts(category = 'all', subcategory = 'all') {
    const products = JSON.parse(localStorage.getItem('products') || '[]');
    const productsContainer = document.getElementById('productsContainer');
    
    if (!productsContainer) return;

    let filteredProducts = products;
    
    if (category !== 'all') {
        filteredProducts = filteredProducts.filter(p => p.category === category);
    }
    
    if (subcategory !== 'all') {
        filteredProducts = filteredProducts.filter(p => p.subcategory === subcategory);
    }

    productsContainer.innerHTML = '';

    if (filteredProducts.length === 0) {
        productsContainer.innerHTML = '<p style="text-align: center; color: var(--gray-medium);">No products found.</p>';
        return;
    }

    filteredProducts.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.innerHTML = `
            <img src="${product.image}" alt="${product.name}" class="product-image" onerror="this.src='https://via.placeholder.com/500x250?text=${encodeURIComponent(product.name)}'">
            <div class="product-info">
                <div class="product-category">${product.category} / ${product.subcategory}</div>
                <h3 class="product-name">${product.name}</h3>
                <p class="product-description">${product.description}</p>
                <div class="product-price">$${product.price.toLocaleString()}</div>
                <div class="product-actions">
                    <button class="btn btn-primary" onclick="buyProduct(${product.id})">Buy Now</button>
                </div>
            </div>
        `;
        productsContainer.appendChild(productCard);
    });
}

// Buy Product
function buyProduct(productId) {
    const user = checkUserLogin();
    if (!user) {
        showAlert('Please login to place an order!', 'error');
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 1500);
        return;
    }

    const products = JSON.parse(localStorage.getItem('products') || '[]');
    const product = products.find(p => p.id === productId);

    if (!product) {
        showAlert('Product not found!', 'error');
        return;
    }

    // Store product for checkout
    sessionStorage.setItem('selectedProduct', JSON.stringify(product));
    window.location.href = 'payment.html';
}

// Place Order
function placeOrder() {
    const user = checkUserLogin();
    if (!user) {
        showAlert('Please login to place an order!', 'error');
        return;
    }

    const product = JSON.parse(sessionStorage.getItem('selectedProduct'));
    if (!product) {
        showAlert('No product selected!', 'error');
        return;
    }

    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked');
    if (!paymentMethod) {
        showAlert('Please select a payment method!', 'error');
        return;
    }

    const orders = JSON.parse(localStorage.getItem('orders') || '[]');
    const order = {
        orderId: 'ORD' + Date.now(),
        userId: user.id,
        userName: user.name,
        productId: product.id,
        productName: product.name,
        total: product.price,
        status: 'pending',
        paymentMethod: paymentMethod.value,
        date: new Date().toISOString()
    };

    orders.push(order);
    localStorage.setItem('orders', JSON.stringify(orders));
    
    sessionStorage.removeItem('selectedProduct');
    
    showAlert('Order placed successfully!', 'success');
    setTimeout(() => {
        window.location.href = 'orders.html';
    }, 2000);
}

// Load User Orders
function loadUserOrders() {
    const user = checkUserLogin();
    if (!user) {
        window.location.href = 'login.html';
        return;
    }

    const orders = JSON.parse(localStorage.getItem('orders') || '[]');
    const userOrders = orders.filter(o => o.userId === user.id);
    const ordersContainer = document.getElementById('ordersContainer');

    if (!ordersContainer) return;

    if (userOrders.length === 0) {
        ordersContainer.innerHTML = '<p style="text-align: center; color: var(--gray-medium);">No orders found.</p>';
        return;
    }

    ordersContainer.innerHTML = '';

    userOrders.forEach(order => {
        const orderCard = document.createElement('div');
        orderCard.className = 'card';
        const statusClass = order.status === 'delivered' ? 'status-delivered' : 'status-pending';
        orderCard.innerHTML = `
            <h3>Order #${order.orderId}</h3>
            <p><strong>Product:</strong> ${order.productName}</p>
            <p><strong>Total:</strong> $${order.total.toLocaleString()}</p>
            <p><strong>Status:</strong> <span class="status-badge ${statusClass}">${order.status}</span></p>
            <p><strong>Payment:</strong> ${order.paymentMethod}</p>
            <p><strong>Date:</strong> ${new Date(order.date).toLocaleDateString()}</p>
        `;
        ordersContainer.appendChild(orderCard);
    });
}

// Submit Contact Form
function submitContact() {
    const name = document.getElementById('contactName').value;
    const email = document.getElementById('contactEmail').value;
    const phone = document.getElementById('contactPhone').value;
    const message = document.getElementById('contactMessage').value;

    if (!name || !email || !message) {
        showAlert('Please fill all required fields!', 'error');
        return;
    }

    const contacts = JSON.parse(localStorage.getItem('contacts') || '[]');
    const contact = {
        id: Date.now(),
        name,
        email,
        phone,
        message,
        date: new Date().toISOString()
    };

    contacts.push(contact);
    localStorage.setItem('contacts', JSON.stringify(contacts));
    
    showAlert('Message sent successfully! We will contact you soon.', 'success');
    document.getElementById('contactForm').reset();
}

// Load Categories
function loadCategories() {
    const products = JSON.parse(localStorage.getItem('products') || '[]');
    const categories = [...new Set(products.map(p => p.category))];
    const categoryFilter = document.getElementById('categoryFilter');
    
    if (categoryFilter) {
        categoryFilter.innerHTML = '<option value="all">All Categories</option>';
        categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat;
            option.textContent = cat;
            categoryFilter.appendChild(option);
        });
    }
}

// Show Alert
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'error' : 'success'}`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initDefaultProducts();
    
    // Load products if on products page
    if (document.getElementById('productsContainer')) {
        loadProducts();
        loadCategories();
        
        // Category filter
        const categoryFilter = document.getElementById('categoryFilter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', function() {
                loadProducts(this.value);
            });
        }
    }
    
    // Load user orders if on orders page
    if (document.getElementById('ordersContainer')) {
        loadUserOrders();
    }
    
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuToggle && navLinks) {
        mobileMenuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });
    }
});
