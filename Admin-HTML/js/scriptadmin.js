// Sidebar Toggling
const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

allSideMenu.forEach(item => {
    const li = item.parentElement;

    item.addEventListener('click', function () {
        allSideMenu.forEach(i => {
            i.parentElement.classList.remove('active');
        })
        li.classList.add('active');
    })
});

// TOGGLE SIDEBAR
const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sidebar = document.getElementById('sidebar');

if (menuBar && sidebar) {
    menuBar.addEventListener('click', function () {
        sidebar.classList.toggle('hide');
    });
}

// Search Form Toggling (for smaller screens)
const searchButton = document.querySelector('#content nav form .form-input button');
const searchButtonIcon = document.querySelector('#content nav form .form-input button .bx');
const searchForm = document.querySelector('#content nav form');

if (searchButton && searchButtonIcon && searchForm) {
    searchButton.addEventListener('click', function (e) {
        if(window.innerWidth < 576) {
            e.preventDefault();
            searchForm.classList.toggle('show');
            if(searchForm.classList.contains('show')) {
                searchButtonIcon.classList.replace('bx-search', 'bx-x');
            } else {
                searchButtonIcon.classList.replace('bx-x', 'bx-search');
            }
        }
    });
}

// Initial sidebar/search form state based on screen width
if(window.innerWidth < 768 && sidebar) {
    sidebar.classList.add('hide');
} else if(window.innerWidth > 576 && searchButtonIcon && searchForm) {
    searchButtonIcon.classList.replace('bx-x', 'bx-search');
    searchForm.classList.remove('show');
}

// Adjust sidebar/search form on window resize
window.addEventListener('resize', function () {
    if(this.innerWidth > 576 && searchButtonIcon && searchForm) {
        searchButtonIcon.classList.replace('bx-x', 'bx-search');
        searchForm.classList.remove('show');
    }
});

// Dark Mode Management
function initDarkMode() {
    console.log("initDarkMode() called."); // Debugging
    const switchMode = document.getElementById('switch-mode');
    
    const darkMode = localStorage.getItem('darkMode');
    
    if (darkMode === 'enabled') {
        document.body.classList.add('dark');
        if (switchMode) switchMode.checked = true;
    }

    if (switchMode) {
        switchMode.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('dark');
                localStorage.setItem('darkMode', 'enabled');
                if (typeof alertSystem !== 'undefined') {
                    alertSystem.show({
                        type: 'info',
                        title: 'Dark Mode',
                        message: 'Dark mode has been enabled'
                    });
                }
            } else {
                document.body.classList.remove('dark');
                localStorage.setItem('darkMode', null);
                if (typeof alertSystem !== 'undefined') {
                    alertSystem.show({
                        type: 'info',
                        title: 'Light Mode',
                        message: 'Light mode has been enabled'
                    });
                }
            }
        });
    }
}


// Chat Management
function initChatManagement() {
    console.log("initChatManagement() called. Path:", window.location.pathname); // Debugging
    if (!window.location.pathname.includes('chat.php')) {
        console.log("Not on chat.php, returning from initChatManagement."); // Debugging
        return;
    }

    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendMessage');
    const messageArea = document.getElementById('messageArea');

    loadMessages();

    function sendMessage() {
        if (!messageInput || !messageArea) return;

        const messageText = messageInput.value.trim();
        if (!messageText) return;

        const message = {
            text: messageText,
            sender: 'admin',
            timestamp: new Date().toISOString(),
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        };

        addMessageToUI(message);
        
        saveMessage(message);
        
        messageInput.value = '';
        if (typeof alertSystem !== 'undefined') {
            alertSystem.show({
                type: 'info',
                title: 'Message Sent',
                message: 'Your message has been sent successfully'
            });
        }
    }

    function addMessageToUI(message) {
        if (!messageArea) return;
        const messageElement = document.createElement('div');
        messageElement.className = `message ${message.sender === 'admin' ? 'sent' : 'received'}`;
        
        messageElement.innerHTML = `
            <div class="message-content">
                <p>${message.text}</p>
                <span class="message-time">${message.time}</span>
            </div>
        `;

        messageArea.appendChild(messageElement);
        messageArea.scrollTop = messageArea.scrollHeight;
    }

    function saveMessage(message) {
        let messages = JSON.parse(localStorage.getItem('chatMessages')) || [];
        messages.push(message);
        localStorage.setItem('chatMessages', JSON.stringify(messages));
    }

    function loadMessages() {
        const messages = JSON.parse(localStorage.getItem('chatMessages')) || [];
        messages.forEach(message => addMessageToUI(message));
    }

    if (sendButton) {
        sendButton.addEventListener('click', sendMessage);
    }

    if (messageInput) {
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });
    }
}

// Alert System
class AlertSystem {
    constructor() {
        this.init();
    }

    init() {
        if (!document.querySelector('.alert-container')) {
            const container = document.createElement('div');
            container.className = 'alert-container';
            document.body.appendChild(container);
        }
    }

    show(options) {
        const { type = 'info', title, message, duration = 3000 } = options;
        
        const alert = document.createElement('div');
        alert.className = `alert ${type}`;
        
        alert.innerHTML = `
            <i class='bx ${this.getIconClass(type)} alert-icon'></i>
            <div class="alert-content">
                <div class="alert-title">${title}</div>
                <div class="alert-message">${message}</div>
            </div>
            <i class='bx bx-x alert-close'></i>
        `;

        const container = document.querySelector('.alert-container');
        if (container) {
            container.appendChild(alert);
        }

        const closeBtn = alert.querySelector('.alert-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close(alert));
        }

        if (duration) {
            setTimeout(() => this.close(alert), duration);
        }
    }

    close(alert) {
        if (alert) {
            alert.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => alert.remove(), 300);
        }
    }

    getIconClass(type) {
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
}

const alertSystem = new AlertSystem();

// Chart Management (DYNAMIC FROM API)
let salesChartInstance = null;
let visitorChartInstance = null;
let productChartInstance = null;
let orderChartInstance = null;

function initCharts() {
    console.log("initCharts() called. Current Path:", window.location.pathname); 
    if (!window.location.pathname.includes('dashboard.php')) {
        console.log("Not on dashboard.php, returning from initCharts."); 
        return;
    }
    console.log("On dashboard.php, proceeding with charts initialization."); 

    const salesPeriodSelect = document.getElementById('salesPeriodSelect');
    if (salesPeriodSelect) {
        // --- Sales Analytics Chart ---
        function fetchAndRenderSalesChart(months) {
            if (salesChartInstance) {
                salesChartInstance.destroy(); 
            }

            fetch(`api/get_sales_analytics.php?period=${months}`) 
                .then(response => response.json())
                .then(data => {
                    const ctxSales = document.getElementById('salesChart').getContext('2d');
                    salesChartInstance = new Chart(ctxSales, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: `Sales Last ${months} Months`, 
                                data: data.data,
                                borderColor: '#4CAF50',
                                backgroundColor: 'rgba(76, 175, 80, 0.2)',
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, 
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'Rp ' + value.toLocaleString('id-ID');
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error fetching sales data:', error));
        }

        // Panggil fungsi pertama kali dengan periode default (12 bulan)
        fetchAndRenderSalesChart(salesPeriodSelect.value);

        // Tambahkan event listener untuk perubahan dropdown
        salesPeriodSelect.addEventListener('change', function() {
            const selectedPeriod = this.value; 
            fetchAndRenderSalesChart(selectedPeriod); 
        });
    } else {
        console.log("salesPeriodSelect not found, skipping sales chart init.");
    }

    // --- Visitor Statistics Chart ---
    const visitorCanvas = document.getElementById('visitorChart');
    if (visitorCanvas) {
        function fetchAndRenderVisitorChart() {
            if (visitorChartInstance) {
                visitorChartInstance.destroy();
            }
            fetch('api/get_visitor_stats.php')
                .then(response => response.json())
                .then(data => {
                    const ctxVisitors = visitorCanvas.getContext('2d'); // Use visitorCanvas here
                    visitorChartInstance = new Chart(ctxVisitors, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Visitors',
                                data: data.data,
                                backgroundColor: '#2196F3',
                                borderColor: '#2196F3',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, 
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error fetching visitor data:', error));
        }
        fetchAndRenderVisitorChart(); 
    } else {
        console.log("visitorChart canvas not found, skipping visitor chart init.");
    }


    // --- Product Performance Chart ---
    const productCanvas = document.getElementById('productChart');
    if (productCanvas) {
        function fetchAndRenderProductChart() {
            if (productChartInstance) {
                productChartInstance.destroy();
            }
            fetch('api/get_product_performance.php')
                .then(response => response.json())
                .then(data => {
                    const ctxProduct = productCanvas.getContext('2d'); // Use productCanvas here
                    productChartInstance = new Chart(ctxProduct, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Quantity Sold',
                                data: data.data,
                                backgroundColor: data.backgroundColor,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, 
                            plugins: {
                                legend: {
                                    position: 'right',
                                },
                                title: {
                                    display: true,
                                    text: 'Product Performance'
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error fetching product data:', error));
        }
        fetchAndRenderProductChart(); 
    } else {
        console.log("productChart canvas not found, skipping product chart init.");
    }


    // --- Order Status Chart ---
    const orderCanvas = document.getElementById('orderChart');
    if (orderCanvas) {
        function fetchAndRenderOrderStatusChart() {
            if (orderChartInstance) {
                orderChartInstance.destroy();
            }
            fetch('api/get_order_status.php')
                .then(response => response.json())
                .then(data => {
                    const ctxOrderStatus = orderCanvas.getContext('2d'); // Use orderCanvas here
                    orderChartInstance = new Chart(ctxOrderStatus, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Order Count',
                                data: data.data,
                                backgroundColor: data.backgroundColor,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, 
                            plugins: {
                                legend: {
                                    position: 'right',
                                },
                                title: {
                                    display: true,
                                    text: 'Order Status'
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error fetching order status data:', error));
        }
        fetchAndRenderOrderStatusChart(); 
    } else {
        console.log("orderChart canvas not found, skipping order status chart init.");
    }
}

// Recent Orders Table Management
function initRecentOrders() {
    console.log("initRecentOrders() called. Current Path:", window.location.pathname); 
    if (!window.location.pathname.includes('dashboard.php')) { // Assuming this table is only on dashboard
        console.log("Not on dashboard.php, returning from initRecentOrders."); 
        return;
    }
    console.log("On dashboard.php, proceeding with recent orders initialization."); 

    function fetchAndRenderRecentOrders() {
        fetch('api/get_orders.php') // Gunakan API get_orders.php
            .then(response => response.json())
            .then(orders => {
                const tableBody = document.getElementById('recentOrdersTableBody');
                if (!tableBody) {
                    console.warn("recentOrdersTableBody not found, skipping rendering.");
                    return;
                }
                tableBody.innerHTML = ''; // Kosongkan data lama

                orders.forEach(order => {
                    const row = document.createElement('tr');
                    const statusClass = order.status ? order.status.toLowerCase().replace(/\s/g, '') : '';

                    let paymentProofHtml = '';
                    if (order.payment_proof_image) { 
                        const imageUrl = `../uploads/payment_proofs/${order.payment_proof_image}`; 
                        paymentProofHtml = `
                            <a href="${imageUrl}" target="_blank" class="btn-view-proof">
                                <i class='bx bxs-file-image'></i> View Proof
                            </a>
                        `;
                    } else {
                        paymentProofHtml = 'N/A';
                    }

                    row.innerHTML = `
                        <td>
                            <img src="${order.customer_image || '../Admin-HTML/images/default-user.webp'}" alt="User"> 
                            <p>${order.customer_name || 'N/A'}</p>
                        </td>
                        <td>${new Date(order.order_date).toLocaleDateString('id-ID')}</td>
                        <td><span class="status ${statusClass}">${order.status || 'Unknown'}</span></td>
                        <td>Rp ${parseFloat(order.total_amount).toLocaleString('id-ID')}</td>
                        <td>${paymentProofHtml}</td>
                        <td>
                            <a href="order_details.php?id=${order.id}" class="btn-edit"><i class='bx bxs-edit'></i></a>
                            <a href="#" class="btn-delete"><i class='bx bxs-trash'></i></a>
                            ${order.status && order.status.toLowerCase() === 'pending' && order.payment_proof_image ? 
                                `<a href="#" class="btn-verify" data-order-id="${order.id}"><i class='bx bx-check-circle'></i> Verify</a>` : ''}
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            })
            .catch(error => console.error('Error fetching recent orders:', error));
    }
    fetchAndRenderRecentOrders(); 

    // Anda bisa menambahkan interval untuk memperbarui recent orders secara berkala
    // setInterval(fetchAndRenderRecentOrders, 30000); // Setiap 30 detik
}


// Dropdown Functions
function toggleDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    if (dropdown) {
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }
}

function toggleNotificationMenu(event) {
    event.stopPropagation();
    const notificationMenu = document.getElementById('notificationMenu');
    if (notificationMenu) {
        notificationMenu.style.display = notificationMenu.style.display === 'block' ? 'none' : 'block';
    }
}

document.addEventListener('click', (event) => {
    const profileDropdown = document.getElementById('profileDropdown');
    const notificationMenu = document.getElementById('notificationMenu');
    
    if (profileDropdown && !event.target.closest('.profile')) {
        profileDropdown.style.display = 'none';
    }

    if (notificationMenu && !event.target.closest('.notification')) {
        notificationMenu.style.display = 'none';
    }
});

// User Management
function initUserManagement() {
    console.log("initUserManagement() called. Path:", window.location.pathname); 
    if (!window.location.pathname.includes('user.php')) {
        console.log("Not on user.php, returning from initUserManagement."); 
        return;
    }

    const addUserForm = document.getElementById('addUserForm');
    const userTable = document.querySelector('.table-data tbody');
    const addUserModal = document.getElementById('addUserModal');

    const addUserBtn = document.querySelector('.btn-download'); 
    if (addUserBtn && addUserModal) { 
        addUserBtn.addEventListener('click', () => {
            addUserModal.style.display = 'block';
            if (addUserForm) addUserForm.reset();
        });
    }

    function closeModal(modalElement) {
        if (modalElement) {
            modalElement.style.display = 'none';
        }
    }

    if (addUserModal) {
        const closeBtnX = addUserModal.querySelector('.close');
        if (closeBtnX) {
            closeBtnX.addEventListener('click', () => closeModal(addUserModal));
        }
    }

    document.querySelectorAll('.close-btn').forEach(button => {
        if (button.closest('#addUserModal')) { 
            button.addEventListener('click', () => closeModal(addUserModal));
        }
    });

    window.addEventListener('click', (event) => {
        if (event.target === addUserModal && addUserModal) {
            closeModal(addUserModal);
        }
    });

    if (addUserForm && userTable) {
        addUserForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const userName = document.getElementById('userName');
            const userEmail = document.getElementById('userEmail');
            const joinDate = document.getElementById('joinDate');
            const userStatus = document.getElementById('userStatus');
            const userPhoto = document.getElementById('userPhoto');

            if (!userName || !userEmail || !joinDate || !userStatus || !userPhoto) {
                console.error("One or more user form elements not found.");
                return;
            }

            if (!userName.value.trim() || !userEmail.value.trim() || !joinDate.value.trim() || !userStatus.value.trim() || !userPhoto.files[0]) {
                if (typeof alertSystem !== 'undefined') {
                    alertSystem.show({
                        type: 'error',
                        title: 'Input Error',
                        message: 'Please fill out all user fields.'
                    });
                } else {
                    alert('Please fill out all user fields.');
                }
                return;
            }

            const newRow = document.createElement('tr');

            const reader = new FileReader();
            reader.onload = function (e) {
                newRow.innerHTML = `
                    <td>
                        <img src="${e.target.result}" alt="User Photo">
                        <p>${userName.value}</p>
                    </td>
                    <td>${userEmail.value}</td>
                    <td>${joinDate.value}</td>
                    <td><span class="status ${userStatus.value.toLowerCase()}">${userStatus.value}</span></td>
                    <td>
                        <a href="#" class="btn-edit"><i class='bx bxs-edit'></i></a>
                        <a href="#" class="btn-delete"><i class='bx bxs-trash'></i></a>
                    </td>
                `;

                userTable.appendChild(newRow);
                closeModal(addUserModal);
                addUserForm.reset();

                newRow.querySelector('.btn-edit').addEventListener('click', (event) => {
                    event.preventDefault();
                    editUser(newRow);
                });
                newRow.querySelector('.btn-delete').addEventListener('click', (event) => {
                    event.preventDefault();
                    deleteUser(newRow);
                });

                if (typeof alertSystem !== 'undefined') {
                    alertSystem.show({
                        type: 'success',
                        title: 'Success',
                        message: 'User has been added successfully'
                    });
                }
            };

            reader.readAsDataURL(userPhoto.files[0]);
        });
    }

    function editUser(row) {
        const userNameEl = row.querySelector('td:nth-child(1) p');
        const userEmailEl = row.querySelector('td:nth-child(2)');
        const joinDateEl = row.querySelector('td:nth-child(3)');
        const userStatusEl = row.querySelector('td:nth-child(4) .status');

        if (!userNameEl || !userEmailEl || !joinDateEl || !userStatusEl) {
            console.error("Could not find all elements in the user row for editing.");
            return;
        }

        const userName = userNameEl.textContent;
        const userEmail = userEmailEl.textContent;
        const joinDate = joinDateEl.textContent;
        const userStatus = userStatusEl.textContent;

        const formUserName = document.getElementById('userName');
        const formUserEmail = document.getElementById('userEmail');
        const formJoinDate = document.getElementById('joinDate');
        const formUserStatus = document.getElementById('userStatus');

        if (formUserName) formUserName.value = userName;
        if (formUserEmail) formUserEmail.value = userEmail;
        if (formJoinDate) formJoinDate.value = joinDate;
        if (formUserStatus) formUserStatus.value = userStatus;

        if (addUserModal) addUserModal.style.display = 'block';

        if (addUserForm) {
            const originalOnSubmit = addUserForm.onsubmit; 

            addUserForm.onsubmit = (e) => {
                e.preventDefault();

                if (formUserName) userNameEl.textContent = formUserName.value;
                if (formUserEmail) userEmailEl.textContent = formUserEmail.value;
                if (formJoinDate) joinDateEl.textContent = formJoinDate.value;
                if (formUserStatus) {
                    userStatusEl.textContent = formUserStatus.value;
                    userStatusEl.className = `status ${formUserStatus.value.toLowerCase()}`;
                }

                closeModal(addUserModal);
                addUserForm.reset();
                addUserForm.onsubmit = originalOnSubmit;
            };
        }
    }

    function deleteUser(row) {
        if (confirm('Are you sure you want to delete this user?')) {
            row.remove();
            if (typeof alertSystem !== 'undefined') {
                const userName = row.querySelector('td:nth-child(1) p') ? row.querySelector('td:nth-child(1) p').textContent : 'Unknown User';
                alertSystem.show({
                    type: 'warning',
                    title: 'Deleted',
                    message: `User ${userName} has been deleted`
                });
            }
        }
    }

    if (userTable) {
        userTable.addEventListener('click', (e) => {
            const editButton = e.target.closest('.btn-edit');
            const deleteButton = e.target.closest('.btn-delete');
            const row = e.target.closest('tr');

            if (editButton && row) {
                e.preventDefault();
                editUser(row);
            } else if (deleteButton && row) {
                e.preventDefault();
                deleteUser(row);
            }
        });
    }
}

// Documentation Management
function initDocumentationManagement() {
    console.log("initDocumentationManagement() called. Path:", window.location.pathname); 
    // This is a documentation module, so it probably won't run on dashboard.php
    // If you want this to run on a 'dokumentasi.php' page or similar, adjust this:
    // if (!window.location.pathname.includes('dokumentasi.php')) { return; }

    const modal = document.getElementById("addDocumentationModal");
    const addDocumentationBtn = document.getElementById("addDocumentationBtn");
    const closeModalBtn = document.getElementsByClassName("close")[0];
    const documentationForm = document.getElementById("documentationForm");
    const documentationList = document.getElementById("documentationList");

    let documentationData = JSON.parse(localStorage.getItem("documentationData")) || [];

    function renderDocumentation() {
        if (!documentationList) return;
        documentationList.innerHTML = "";
        documentationData.forEach((doc, index) => {
            const docItem = document.createElement("div");
            docItem.classList.add("documentation-item");

            docItem.innerHTML = `
                <img src="${doc.image}" alt="${doc.title}">
                <div class="documentation-details">
                    <h3>${doc.title}</h3>
                    <p>${doc.description}</p>
                    <p>${doc.price}</p>
                    <button class="btn-edit" onclick="editDocumentation(${index})">Edit</button>
                    <button class="btn-delete" onclick="deleteDocumentation(${index})">Delete</button>
                </div>
            `;
            documentationList.appendChild(docItem);
        });
    }

    if (documentationForm) {
        documentationForm.onsubmit = function (event) {
            event.preventDefault();

            const docImageInput = document.getElementById("docImage");
            const docTitleInput = document.getElementById("docTitle");
            const docDescriptionInput = document.getElementById("docDescription");
            const docPriceInput = document.getElementById("docPrice");

            if (!docImageInput || !docTitleInput || !docDescriptionInput || !docPriceInput) {
                console.error("One or more documentation form elements not found.");
                return;
            }

            const docImage = docImageInput.files[0];
            const docTitle = docTitleInput.value;
            const docDescription = docDescriptionInput.value;
            const docPrice = docPriceInput.value;

            if (!docImage || !docTitle.trim() || !docDescription.trim() || !docPrice.trim()) {
                if (typeof alertSystem !== 'undefined') {
                    alertSystem.show({
                        type: 'error',
                        title: 'Input Error',
                        message: 'Please fill out all fields for documentation.'
                    });
                } else {
                    alert("Please fill out all fields.");
                }
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                const newDoc = {
                    image: e.target.result,
                    title: docTitle,
                    description: docDescription,
                    price: docPrice,
                };

                documentationData.push(newDoc);
                localStorage.setItem("documentationData", JSON.stringify(documentationData));
                renderDocumentation();
                if (modal) modal.style.display = "none";
                documentationForm.reset();
                if (typeof alertSystem !== 'undefined') {
                    alertSystem.show({
                        type: 'success',
                        title: 'Success',
                        message: 'Documentation added successfully!'
                    });
                }
            };
            reader.readAsDataURL(docImage);
        };
    }

    window.editDocumentation = function (index) {
        const doc = documentationData[index];
        const docTitleInput = document.getElementById("docTitle");
        const docDescriptionInput = document.getElementById("docDescription");
        const docPriceInput = document.getElementById("docPrice");

        if (docTitleInput) docTitleInput.value = doc.title;
        if (docDescriptionInput) docDescriptionInput.value = doc.description;
        if (docPriceInput) docPriceInput.value = doc.price;

        if (modal) modal.style.display = "block";

        if (documentationForm) {
            const originalOnSubmit = documentationForm.onsubmit;
            documentationForm.onsubmit = function (event) {
                event.preventDefault();

                if (docTitleInput) doc.title = docTitleInput.value;
                if (docDescriptionInput) doc.description = docDescriptionInput.value;
                if (docPriceInput) doc.price = docPriceInput.value;

                localStorage.setItem("documentationData", JSON.stringify(documentationData));
                renderDocumentation();
                if (modal) modal.style.display = "none";
                documentationForm.reset();
                documentationForm.onsubmit = originalOnSubmit;
                if (typeof alertSystem !== 'undefined') {
                    alertSystem.show({
                        type: 'info',
                        title: 'Updated',
                        message: 'Documentation updated successfully!'
                    });
                }
            };
        }
    };

    window.deleteDocumentation = function (index) {
        if (confirm('Are you sure you want to delete this documentation?')) {
            documentationData.splice(index, 1);
            localStorage.setItem("documentationData", JSON.stringify(documentationData));
            renderDocumentation();
            if (typeof alertSystem !== 'undefined') {
                alertSystem.show({
                    type: 'warning',
                    title: 'Deleted',
                    message: 'Documentation deleted.'
                });
            }
        }
    };

    if (addDocumentationBtn && modal) {
        addDocumentationBtn.onclick = function () {
            documentationForm.reset();
            modal.style.display = "block";
            if (documentationForm) {
                documentationForm.onsubmit = function (event) {
                    event.preventDefault(); 
                    const docImageInput = document.getElementById("docImage");
                    const docTitleInput = document.getElementById("docTitle");
                    const docDescriptionInput = document.getElementById("docDescription");
                    const docPriceInput = document.getElementById("docPrice");

                    if (!docImageInput || !docTitleInput || !docDescriptionInput || !docPriceInput) {
                        console.error("One or more documentation form elements not found.");
                        return;
                    }

                    const docImage = docImageInput.files[0];
                    const docTitle = docTitleInput.value;
                    const docDescription = docDescriptionInput.value;
                    const docPrice = docPriceInput.value;

                    if (!docImage || !docTitle.trim() || !docDescription.trim() || !docPrice.trim()) {
                         if (typeof alertSystem !== 'undefined') {
                            alertSystem.show({
                                type: 'error',
                                title: 'Input Error',
                                message: 'Please fill out all fields for documentation.'
                            });
                        } else {
                            alert("Please fill out all fields.");
                        }
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const newDoc = {
                            image: e.target.result,
                            title: docTitle,
                            description: docDescription,
                            price: docPrice,
                        };

                        documentationData.push(newDoc);
                        localStorage.setItem("documentationData", JSON.stringify(documentationData));
                        renderDocumentation();
                        if (modal) modal.style.display = "none";
                        documentationForm.reset();
                        if (typeof alertSystem !== 'undefined') {
                            alertSystem.show({
                                type: 'success',
                                title: 'Success',
                                message: 'Documentation added successfully!'
                            });
                        }
                    };
                    reader.readAsDataURL(docImage);
                };
            }
        };
    }

    if (closeModalBtn && modal) {
        closeModalBtn.onclick = function () {
            modal.style.display = "none";
            if (documentationForm) documentationForm.reset();
        };
    }

    window.onclick = function (event) {
        if (event.target === modal && modal) {
            modal.style.display = "none";
            if (documentationForm) documentationForm.reset();
        };
    }

    renderDocumentation();
}

// Artikel Management
function initArtikelManagement() {
    console.log("initArtikelManagement() called. Path:", window.location.pathname); 
    if (!window.location.pathname.includes('artikel.php')) {
        console.log("Not on artikel.php, returning from initArtikelManagement."); 
        return;
    }

    const addArtikelBtn = document.getElementById('addArtikelBtn');
    const artikelModal = document.getElementById('artikelModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const artikelForm = document.getElementById('artikelForm');
    const artikelList = document.getElementById('artikelList');
    const modalTitle = document.getElementById('modalTitle');
    const artikelIdInput = document.getElementById('artikelId');

    if (addArtikelBtn && artikelModal && artikelForm && modalTitle && artikelIdInput) {
        addArtikelBtn.addEventListener('click', (e) => {
            e.preventDefault();
            artikelForm.reset();
            artikelIdInput.value = '';
            modalTitle.textContent = 'Tambah Artikel Baru';
            artikelModal.style.display = 'flex';
        });
    }

    if (closeModalBtn && artikelModal) {
        closeModalBtn.addEventListener('click', () => {
            artikelModal.style.display = 'none';
            if (artikelForm) artikelForm.reset();
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target === artikelModal && artikelModal) {
            artikelModal.style.display = 'none';
            if (artikelForm) artikelForm.reset();
        }
    });

    if (artikelForm && artikelModal && artikelIdInput) {
        artikelForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const id = artikelIdInput.value;
            const judulInput = document.getElementById('judulArtikel');
            const tanggalInput = document.getElementById('tanggalArtikel');
            const isiInput = document.getElementById('isiArtikel');
            const gambarFileInput = document.getElementById('gambarArtikel');

            if (!judulInput || !tanggalInput || !isiInput || !gambarFileInput) {
                console.error("One or more artikel form elements not found.");
                return;
            }
            
            const judul = judulInput.value;
            const tanggal = tanggalInput.value;
            const isi = isiInput.value;
            const gambarFile = gambarFileInput.files[0];

            if (!judul.trim() || !tanggal.trim() || !isi.trim()) {
                if (typeof alertSystem !== 'undefined') {
                    alertSystem.show({
                        type: 'error',
                        title: 'Input Error',
                        message: 'Harap lengkapi semua field artikel!'
                    });
                } else {
                    alert("Harap lengkapi semua field!");
                }
                return;
            }

            if (id) {
                updateArtikel(id, judul, tanggal, isi, gambarFile);
            } else {
                addArtikel(judul, tanggal, isi, gambarFile);
            }

            artikelModal.style.display = 'none';
            artikelForm.reset();
            if (typeof alertSystem !== 'undefined') {
                alertSystem.show({
                    type: 'success',
                    title: 'Success',
                    message: `Artikel ${id ? 'updated' : 'added'} successfully!`
                });
            }
        });
    }

    function addArtikel(judul, tanggal, isi, gambarFile) {
        const artikel = {
            id: Date.now().toString(),
            judul,
            tanggal,
            isi,
            gambarUrl: gambarFile ? URL.createObjectURL(gambarFile) : '',
        };

        let artikelListData = JSON.parse(localStorage.getItem('artikelList')) || [];
        artikelListData.push(artikel);
        localStorage.setItem('artikelList', JSON.stringify(artikelListData));

        renderArtikel();
    }

    function updateArtikel(id, judul, tanggal, isi, gambarFile) {
        let artikelListData = JSON.parse(localStorage.getItem('artikelList')) || [];
        const artikelIndex = artikelListData.findIndex((artikel) => artikel.id === id);

        if (artikelIndex !== -1) {
            artikelListData[artikelIndex] = {
                ...artikelListData[artikelIndex],
                judul,
                tanggal,
                isi,
                gambarUrl: gambarFile ? URL.createObjectURL(gambarFile) : artikelListData[artikelIndex].gambarUrl,
            };

            localStorage.setItem('artikelList', JSON.stringify(artikelListData));
            renderArtikel();
        }
    }

    window.deleteArtikel = function (id) {
        if (confirm('Are you sure you want to delete this article?')) {
            let artikelListData = JSON.parse(localStorage.getItem('artikelList')) || [];
            artikelListData = artikelListData.filter((artikel) => artikel.id !== id);
            localStorage.setItem('artikelList', JSON.stringify(artikelListData));
            renderArtikel();
            if (typeof alertSystem !== 'undefined') {
                alertSystem.show({
                    type: 'warning',
                    title: 'Deleted',
                    message: 'Artikel deleted.'
                });
            }
        }
    }

    function renderArtikel() {
        if (!artikelList) return;
        const artikelListData = JSON.parse(localStorage.getItem('artikelList')) || [];
        artikelList.innerHTML = '';

        artikelListData.forEach((artikel) => {
            const artikelItem = document.createElement('div');
            artikelItem.classList.add('artikel-item');
            artikelItem.innerHTML = `
                <div class="artikel-header">
                    <h2>${artikel.judul}</h2>
                    <span class="artikel-date">${artikel.tanggal}</span>
                </div>
                <div class="artikel-content">
                    ${artikel.gambarUrl ? `<img src="${artikel.gambarUrl}" alt="${artikel.judul}">` : ''}
                    <p>${artikel.isi}</p>
                    <button onclick="editArtikel('${artikel.id}')">Edit</button>
                    <button onclick="deleteArtikel('${artikel.id}')">Hapus</button>
                </div>
            `;
            artikelList.appendChild(artikelItem);
        });
    }

    window.editArtikel = function (id) {
        const artikelListData = JSON.parse(localStorage.getItem('artikelList')) || [];
        const artikel = artikelListData.find((artikel) => artikel.id === id);

        if (artikel) {
            const judulInput = document.getElementById('judulArtikel');
            const tanggalInput = document.getElementById('tanggalArtikel');
            const isiInput = document.getElementById('isiArtikel');
            const artikelIdInputEl = document.getElementById('artikelId');
            
            if (judulInput) judulInput.value = artikel.judul;
            if (tanggalInput) tanggalInput.value = artikel.tanggal;
            if (isiInput) isiInput.value = artikel.isi;
            if (artikelIdInputEl) artikelIdInputEl.value = artikel.id;
            
            if (modalTitle) modalTitle.textContent = 'Edit Artikel';
            if (artikelModal) artikelModal.style.display = 'flex';
        }
    }

    renderArtikel();
}

// Login and Register Functions
function showLogin() {
    const formsWrapper = document.querySelector('.forms-wrapper');
    if (formsWrapper) {
        formsWrapper.classList.remove('signup-active');
    }
}

function showSignup() {
    const formsWrapper = document.querySelector('.forms-wrapper');
    if (formsWrapper) {
        formsWrapper.classList.add('signup-active');
    }
}

// Main DOMContentLoaded Listener (pastikan ini hanya ada satu di seluruh file)
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOMContentLoaded event fired. scriptadmin.js is ready."); 
    initDarkMode();
    
    // Periksa halaman saat ini dan panggil fungsi yang relevan
    const currentPath = window.location.pathname;

    if (currentPath.includes('dashboard.php')) {
        initCharts(); 
        initRecentOrders(); 
    }
    
    // Panggil manajemen lainnya yang relevan dengan halaman tersebut
    if (currentPath.includes('user.php')) {
        initUserManagement(); 
    }
    if (currentPath.includes('chat.php')) {
        initChatManagement(); 
    }
    // Tambahkan kondisi untuk halaman lain jika diperlukan
    // if (currentPath.includes('dokumentasi.php')) {
    //     initDocumentationManagement();
    // }
    // if (currentPath.includes('artikel.php')) {
    //     initArtikelManagement();
    // }

    // Fungsi-fungsi umum yang mungkin berlaku di semua halaman
    // addStatusStyles(); // Jika ini adalah fungsi global yang selalu dijalankan
    // initProductManagement(); // Jika ini adalah fungsi global yang selalu dijalankan
});