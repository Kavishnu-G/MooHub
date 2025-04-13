<?php
// customers.php - Customer management page
include '../database.php';

try {
    $stmt = $conn->prepare("SELECT * FROM customers ORDER BY name ASC");
    $stmt->execute();
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching customers: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Customer Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="../assets/logo.png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.3/jspdf.plugin.autotable.min.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: "#FF6B6B", secondary: "#4ECDC4" },
                    borderRadius: { none: "0px", sm: "8px", DEFAULT: "12px", md: "16px", lg: "20px", xl: "24px", "2xl": "28px", "3xl": "32px", full: "9999px", button: "12px" },
                    boxShadow: { card: "0 2px 8px rgba(0, 0, 0, 0.05)" }
                }
            }
        };
    </script>
    <style>
        .user-card { animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .checkbox-wrapper input[type="checkbox"] { opacity: 0; position: absolute; }
        .checkbox-wrapper .custom-checkbox { width: 20px; height: 20px; border: 2px solid #FF6B6B; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .checkbox-wrapper input[type="checkbox"]:checked + .custom-checkbox { background-color: #FF6B6B; }
        .checkbox-wrapper input[type="checkbox"]:checked + .custom-checkbox::after { content: ""; width: 6px; height: 10px; border: solid white; border-width: 0 2px 2px 0; transform: rotate(45deg); margin-bottom: 2px; }
        .bottom-nav { border-radius: 24px 24px 0 0; box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05); }
        .dark { background-color: #1a1a1a; color: #f0f0f0; }
        .dark .bg-white { background-color: #2a2a2a; }
        .dark .text-gray-600 { color: #a0a0a0; }
        .dark .border-gray-200 { border-color: #444; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="w-full max-w-[375px] md:max-w-[600px] lg:max-w-[800px] mx-auto relative pb-20">
        <!-- Top Navigation -->
        <nav class="fixed top-0 inset-x-0 bg-white border-b border-gray-100 shadow-sm z-50">
            <div class="flex items-center justify-between px-4 h-14">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-full overflow-hidden">
                        <img src="../assets/logo.png" alt="logo" class="w-full h-full object-cover">
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button class="w-8 h-8 flex items-center justify-center"><i class="ri-notification-3-line text-gray-600 ri-lg"></i></button>
                    <button id="themeToggle" class="w-8 h-8 flex items-center justify-center"><i class="ri-sun-line text-gray-600 ri-lg"></i></button>
                    <a href="logout.php" class="px-3 py-1 bg-red-500 text-white rounded-button text-sm hover:bg-red-600">Logout</a>
                </div>
            </div>
        </nav>

        <main class="mt-16 px-4">
            <div class="bg-gray-50 rounded-lg p-4 mb-4 shadow-card">
                <div class="flex items-center justify-between mb-3">
                    <button id="addCustomerBtn" class="bg-primary text-white px-3 py-1 rounded-md hover:bg-primary/90 flex items-center text-sm">
                        <i class="ri-add-line mr-1"></i><span>Add Customer</span>
                    </button>
                    <div class="text-sm text-gray-600">Total Customers: <span id="customerCount"><?= count($customers) ?></span></div>
                </div>
                <div class="flex justify-between text-gray-600 text-sm">
                    <button id="sortBtn" class="flex items-center gap-1"><i class="ri-sort-desc" id="sortIcon"></i><span>Sort by name</span></button>
                    <button id="labelBtn" class="flex items-center gap-1"><span>🏷</span><span>Labels</span></button>
                </div>
            </div>

            <div id="customerList" class="space-y-3">
                <?php foreach ($customers as $customer): ?>
                    <div class="user-card bg-white rounded-lg border border-gray-200 p-4 shadow-card" data-id="<?= $customer['id'] ?>">
                        <div class="flex items-center gap-3">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="customer<?= $customer['id'] ?>" />
                                <label class="custom-checkbox" for="customer<?= $customer['id'] ?>"></label>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                                <i class="ri-user-line text-gray-400 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <p class="customer-name font-bold text-lg"><?= htmlspecialchars($customer['name']) ?></p>
                                <p class="text-gray-500 text-sm"><?= htmlspecialchars($customer['role'] ?? "Customer") ?></p>
                                <p class="hidden customer-phone-data"><?= htmlspecialchars($customer['phone'] ?? '') ?></p>
                                <p class="hidden customer-address-data"><?= htmlspecialchars($customer['address'] ?? '') ?></p>
                            </div>
                            <button class="preview-btn text-gray-400" onclick="openCustomerDetails(this)"><i class="ri-eye-line"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>

        <!-- Add Customer Modal -->
        <div id="addCustomerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-80">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold">Customer Registration</h3>
                    <button id="closeAddCustomerModal" class="text-gray-500 hover:text-gray-600"><i class="ri-close-line text-xl"></i></button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Customer Name</label>
                        <input type="text" id="customerName" class="w-full px-3 py-2 border border-gray-200 rounded-lg" placeholder="Enter customer name" required />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Address</label>
                        <input type="text" id="customerAddress" class="w-full px-3 py-2 border border-gray-200 rounded-lg" placeholder="Enter address" required />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Phone Number</label>
                        <input type="tel" id="customerPhone" class="w-full px-3 py-2 border border-gray-200 rounded-lg" placeholder="Enter phone number" required />
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="sameAsPhone" class="w-4 h-4 text-primary">
                        <label for="sameAsPhone" class="text-sm text-gray-600">Same as WhatsApp number</label>
                    </div>
                    <div id="whatsappNumberContainer">
                        <label class="block text-sm text-gray-600 mb-1">WhatsApp Number</label>
                        <input type="tel" id="whatsappNumber" class="w-full px-3 py-2 border border-gray-200 rounded-lg" placeholder="Enter WhatsApp number" />
                    </div>
                    <button id="saveCustomer" class="w-full py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">Save Customer</button>
                </div>
            </div>
        </div>

        <!-- Label Modal -->
        <div id="labelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-80">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold">Labeled Customers</h3>
                    <button id="closeLabelModal" class="text-gray-500 hover:text-gray-600"><i class="ri-close-line text-xl"></i></button>
                </div>
                <div id="labeledUsersList" class="space-y-3">
                    <p class="text-center text-gray-500">No labeled customers</p>
                </div>
            </div>
        </div>

        <!-- Customer Details Page -->
        <div class="fixed inset-0 bg-white z-50 customer-details-page hidden">
            <nav class="fixed top-0 w-full bg-white shadow-sm z-50">
                <div class="h-14 flex items-center px-4">
                    <button class="close-details mr-3"><i class="ri-arrow-left-line text-xl"></i></button>
                    <h1 class="text-lg font-bold">Customer Section</h1>
                </div>
            </nav>
            <div class="mt-14 px-4 py-4 pb-32 overflow-y-auto h-[calc(100vh-56px)]">
                <div class="bg-white rounded-lg shadow p-4 mb-4">
                <div class="flex items-center mb-4">
    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mr-4">
        <i class="ri-user-3-line text-gray-500 text-3xl"></i>
    </div>
    <div class="flex-1 flex items-center">
        <h3 class="font-medium text-lg customer-name" data-original-name=""></h3>
        <button class="edit-btn ml-2 text-primary"><i class="ri-edit-line"></i></button>
        <button class="save-btn ml-2 text-green-500 hidden"><i class="ri-save-line"></i></button>
        <button class="cancel-btn ml-2 text-red-500 hidden"><i class="ri-close-line"></i></button>
    </div>
</div>
<div class="space-y-3">
    <div>
        <label class="text-sm text-gray-500">Address</label>
        <p class="font-medium customer-address" data-original-address=""></p>
        <input type="text" class="edit-address w-full px-3 py-2 border border-gray-200 rounded-lg hidden" placeholder="Enter address">
    </div>
    <div>
        <label class="text-sm text-gray-500">Phone</label>
        <p class="font-medium customer-phone" data-original-phone=""></p>
        <input type="tel" class="edit-phone w-full px-3 py-2 border border-gray-200 rounded-lg hidden" placeholder="Enter phone number">
    </div>
</div>
                    <button class="mt-4 w-full border border-red-500 text-red-500 py-2 rounded-lg delete-customer-btn">Delete Customer</button>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                <div>
        <label class="text-sm text-gray-500">Total Liters</label>
        <p class="font-medium total-liters">0 L</p>
    </div>
    <div>
        <label class="text-sm text-gray-500">Paid Liters</label>
        <p class="font-medium paid-liters">0 L</p>
    </div>
    <div>
        <label class="text-sm text-gray-500">Due Liters</label>
        <p class="font-medium due-liters">0 L</p>
    </div>
                    <div class="mt-4">
                        <h3 class="font-medium mb-2">Generate Bill Summary</h3>
                        <div class="flex gap-2">
                            <input type="month" id="billMonth" class="px-3 py-2 border border-gray-200 rounded-lg">
                            <button id="generateBill" class="px-4 py-2 bg-primary text-white rounded-lg">Generate</button>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-medium mb-3">Account Activity:</h3>
                        <div class="flex gap-4 mb-2">
                            <button id="recordTab" class="record-tab text-primary border-b-2 border-primary pb-1">Record</button>
                            <button id="paymentTab" class="payment-tab text-gray-500 pb-1">Payment</button>
                        </div>
                        <div id="recordContent" class="record-content"></div>
                        <div id="paymentContent" class="payment-content hidden"></div>
                    </div>
                </div>
            </div>
        </div>

<!-- Payout Modal -->
<div class="fixed bottom-20 right-4 flex flex-col gap-3 z-50">
    <button class="bg-primary text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center" onclick="openPayoutModal()">
        <i class="ri-wallet-3-fill text-xl"></i>
    </button>
    <div id="payoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[60]">
        <div class="bg-white rounded-lg w-80 p-4 m-4">
            <nav class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Payout Section</h3>
                <button onclick="closePayoutModal()" class="text-gray-500 hover:text-gray-600"><i class="ri-close-line text-xl"></i></button>
            </nav>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Select Customer</label>
                    <select id="customerSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        <option value="" disabled>Select a customer</option>
                        <?php foreach($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>" data-name="<?= htmlspecialchars($customer['name']) ?>"><?= htmlspecialchars($customer['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <h4 class="font-bold mb-3">Calculate Summary</h4>
                    <div class="mb-3">
                        <label class="block text-sm text-gray-600 mb-1">Due Liters</label>
                        <input type="number" id="dueLiters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Due Liters" readonly>
                    </div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-gray-500">×</span>
                        <input type="number" id="rate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Rate (INR)" step="0.01">
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg mb-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Summary INR:</span>
                            <span id="summaryAmount" class="font-bold">0.00</span>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Amount Paid</label>
                                <input type="number" id="amountPaid" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Enter amount paid" step="0.01">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Equivalent Liters</label>
                                <input type="number" id="paidLiters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" readonly>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Due Amount</label>
                                <input type="number" id="dueAmount" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" readonly>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Due Liters Remaining</label>
                                <input type="number" id="dueLitersRemaining" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <button onclick="savePayout()" class="w-full bg-primary text-white py-2 rounded-lg">Save Payout</button>
            </div>
        </div>
    </div>
</div>

        <!-- Bottom Navigation -->
        <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 shadow-lg">
            <div class="grid grid-cols-4 gap-1 p-2 max-w-[375px] mx-auto">
                <a href="../dashboard/dashboard.php" class="flex flex-col items-center justify-center p-2 hover:text-primary">
                    <i class="ri-home-3-line text-xl"></i><span class="text-xs mt-1">Dashboard</span>
                </a>
                <a href="customers.php" class="flex flex-col items-center justify-center p-2 text-primary">
                    <i class="ri-team-line text-xl"></i><span class="text-xs mt-1">Customers</span>
                </a>
                <a href="../profile/profile.php" class="flex flex-col items-center justify-center p-2 hover:text-primary">
                    <i class="ri-user-settings-line text-xl"></i><span class="text-xs mt-1">Profile</span>
                </a>
                <a href="../payout/payout.php" class="flex flex-col items-center justify-center p-2 hover:text-primary">
                    <i class="ri-wallet-3-line text-xl"></i><span class="text-xs mt-1">Payout</span>
                </a>
            </div>
        </nav>
        <script>
    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    let isDark = false;
    themeToggle.addEventListener('click', () => {
        isDark = !isDark;
        document.body.classList.toggle('dark');
        themeToggle.innerHTML = isDark ? '<i class="ri-moon-line text-gray-600 ri-lg"></i>' : '<i class="ri-sun-line text-gray-600 ri-lg"></i>';
    });

    // Add Customer Modal
    const addCustomerBtn = document.getElementById('addCustomerBtn');
    const addCustomerModal = document.getElementById('addCustomerModal');
    const closeAddCustomerModal = document.getElementById('closeAddCustomerModal');
    addCustomerBtn.addEventListener('click', () => { addCustomerModal.classList.remove('hidden'); addCustomerModal.classList.add('flex'); });
    closeAddCustomerModal.addEventListener('click', () => { addCustomerModal.classList.add('hidden'); addCustomerModal.classList.remove('flex'); });

    // WhatsApp Number Handling
    const sameAsPhone = document.getElementById('sameAsPhone');
    const whatsappNumberContainer = document.getElementById('whatsappNumberContainer');
    sameAsPhone.addEventListener('change', (e) => {
        if (e.target.checked) {
            document.getElementById('whatsappNumber').value = document.getElementById('customerPhone').value;
            whatsappNumberContainer.style.display = 'none';
        } else {
            whatsappNumberContainer.style.display = 'block';
        }
    });

    // Save Customer
    document.getElementById('saveCustomer').addEventListener('click', () => {
        const customerName = document.getElementById('customerName').value;
        const customerAddress = document.getElementById('customerAddress').value;
        const customerPhone = document.getElementById('customerPhone').value;
        const whatsappNumber = document.getElementById('whatsappNumber').value;
        if (!customerName || !customerAddress || !customerPhone) {
            alert("Please fill in all required fields");
            return;
        }
        const formData = new FormData();
        formData.append("name", customerName);
        formData.append("address", customerAddress);
        formData.append("phone", customerPhone);
        formData.append("whatsapp", whatsappNumber);
        fetch("register_customer.php", { method: "POST", body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert("Customer registered successfully!");
                    location.reload();
                } else {
                    alert("Registration failed: " + data.message);
                }
            })
            .catch(err => { console.error("Error:", err); alert("An error occurred"); });
    });

    // Sort Customers
    let isSortedAsc = true;
    const sortBtn = document.getElementById("sortBtn");
    const sortIcon = document.getElementById("sortIcon");
    const customerList = document.getElementById("customerList");
    sortBtn.addEventListener("click", () => {
        const cards = Array.from(customerList.children);
        cards.sort((a, b) => {
            const nameA = a.querySelector(".customer-name").textContent.toLowerCase();
            const nameB = b.querySelector(".customer-name").textContent.toLowerCase();
            return isSortedAsc ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
        });
        cards.forEach(card => customerList.appendChild(card));
        isSortedAsc = !isSortedAsc;
        sortIcon.className = isSortedAsc ? "ri-sort-desc" : "ri-sort-asc";
    });

    // Label Modal
    const labelBtn = document.getElementById("labelBtn");
    const labelsModal = document.getElementById("labelModal");
    const closeLabelsModal = document.getElementById("closeLabelModal");
    const labeledUsersList = document.getElementById("labeledUsersList");
    function updateLabeledMembersList() {
        const checkedBoxes = document.querySelectorAll('input[type="checkbox"]:checked');
        if (checkedBoxes.length === 0) {
            labeledUsersList.innerHTML = '<p class="text-center text-gray-500">No labeled customers</p>';
            return;
        }
        labeledUsersList.innerHTML = "";
        checkedBoxes.forEach(checkbox => {
            const card = checkbox.closest(".user-card");
            const name = card.querySelector(".customer-name").textContent;
            const role = card.querySelector("p:not(.customer-name):not(.hidden)").textContent;
            const item = document.createElement("div");
            item.className = "flex items-center p-2 bg-gray-50 rounded";
            item.innerHTML = `<div class="flex-1"><p class="font-medium">${name}</p><p class="text-sm text-gray-500">${role}</p></div>`;
            labeledUsersList.appendChild(item);
        });
    }
    labelBtn.addEventListener('click', () => { updateLabeledMembersList(); labelsModal.classList.remove('hidden'); labelsModal.classList.add('flex'); });
    closeLabelsModal.addEventListener('click', () => { labelsModal.classList.add('hidden'); labelsModal.classList.remove('flex'); });
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => checkbox.addEventListener('change', updateLabeledMembersList));

    // Open Customer Details
    function openCustomerDetails(btn) {
        const customerCard = btn.closest(".user-card");
        const customerName = customerCard.querySelector(".customer-name").textContent;
        const customerPhone = customerCard.querySelector(".customer-phone-data").textContent;
        const customerAddress = customerCard.querySelector(".customer-address-data").textContent;
        const detailsPage = document.querySelector(".customer-details-page");
        detailsPage.querySelector(".customer-name").textContent = customerName;
        detailsPage.querySelector(".customer-phone").textContent = customerPhone;
        detailsPage.querySelector(".customer-address").textContent = customerAddress;

        fetch(`get_customer_details.php?name=${encodeURIComponent(customerName)}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    detailsPage.querySelector('.total-liters').textContent = `${data.total_liters} L`;
                    detailsPage.querySelector('.paid-liters').textContent = `${data.paid_liters} L`;
                    detailsPage.querySelector('.due-liters').textContent = `${data.due_liters} L`;

                    const recordContent = detailsPage.querySelector('#recordContent');
                    recordContent.innerHTML = data.orders.length > 0
                        ? data.orders.map(order => `
                            <div class="bg-gray-50 p-3 rounded-lg mb-2">
                                <p>Order #${order.order_id}</p>
                                <p>${order.milk_quantity} L on ${new Date(order.order_time).toLocaleString()}</p>
                                <p>Status: <span class="${order.payment_status === 'Paid' ? 'text-green-500' : 'text-red-500'}">${order.payment_status}</span></p>
                            </div>
                        `).join('')
                        : '<p class="text-center text-gray-500">No transaction records available</p>';

                    const paymentContent = detailsPage.querySelector('#paymentContent');
                    paymentContent.innerHTML = data.payments.length > 0
                        ? data.payments.map(payment => `
                            <div class="bg-gray-50 p-3 rounded-lg mb-2">
                                <p>From: ${payment.from_date} To: ${payment.to_date}</p>
                                <p>Total Liters: ${payment.total_liters} L</p>
                                <p>Amount Paid: ₹${payment.amount_paid}</p>
                                <p>Due Amount: ₹${payment.due_amount}</p>
                                <p>Status: <span class="${payment.due_amount == 0 ? 'text-green-500' : 'text-red-500'}">${payment.due_amount == 0 ? 'Paid' : 'Due'}</span></p>
                            </div>
                        `).join('')
                        : '<p class="text-center text-gray-500">No payment history available</p>';
                } else {
                    alert('Error fetching customer details: ' + data.message);
                }
            })
            .catch(err => console.error(err));

        detailsPage.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }
    // Open Customer Details
    function openCustomerDetails(btn) {
        const customerCard = btn.closest(".user-card");
        const customerName = customerCard.querySelector(".customer-name").textContent;
        const customerPhone = customerCard.querySelector(".customer-phone-data").textContent;
        const customerAddress = customerCard.querySelector(".customer-address-data").textContent;
        const detailsPage = document.querySelector(".customer-details-page");
        const nameElement = detailsPage.querySelector(".customer-name");
        const phoneElement = detailsPage.querySelector(".customer-phone");
        const addressElement = detailsPage.querySelector(".customer-address");

        nameElement.textContent = customerName;
        nameElement.dataset.originalName = customerName;
        phoneElement.textContent = customerPhone;
        phoneElement.dataset.originalPhone = customerPhone;
        addressElement.textContent = customerAddress;
        addressElement.dataset.originalAddress = customerAddress;

        fetch(`get_customer_details.php?name=${encodeURIComponent(customerName)}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    detailsPage.querySelector('.total-liters').textContent = `${data.total_liters} L`;
                    detailsPage.querySelector('.paid-liters').textContent = `${data.paid_liters} L`;
                    detailsPage.querySelector('.due-liters').textContent = `${data.due_liters} L`;

                    const recordContent = detailsPage.querySelector('#recordContent');
                    recordContent.innerHTML = data.orders.length > 0
                        ? data.orders.map(order => `
                            <div class="bg-gray-50 p-3 rounded-lg mb-2">
                                <p>Order #${order.order_id}</p>
                                <p>${order.milk_quantity} L on ${new Date(order.order_time).toLocaleString()}</p>
                                <p>Status: <span class="${order.payment_status === 'Paid' ? 'text-green-500' : 'text-red-500'}">${order.payment_status}</span></p>
                            </div>
                        `).join('')
                        : '<p class="text-center text-gray-500">No transaction records available</p>';

                    const paymentContent = detailsPage.querySelector('#paymentContent');
                    paymentContent.innerHTML = data.payments.length > 0
                        ? data.payments.map(payment => `
                            <div class="bg-gray-50 p-3 rounded-lg mb-2">
                                <p>From: ${payment.from_date} To: ${payment.to_date}</p>
                                <p>Total Liters: ${payment.total_liters} L</p>
                                <p>Amount Paid: ₹${payment.amount_paid}</p>
                                <p>Due Amount: ₹${payment.due_amount}</p>
                                <p>Status: <span class="${payment.due_amount == 0 ? 'text-green-500' : 'text-red-500'}">${payment.due_amount == 0 ? 'Paid' : 'Due'}</span></p>
                            </div>
                        `).join('')
                        : '<p class="text-center text-gray-500">No payment history available</p>';
                } else {
                    alert('Error fetching customer details: ' + data.message);
                }
            })
            .catch(err => console.error(err));

        detailsPage.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }

    // Edit Customer Details
    document.querySelector('.edit-btn').addEventListener('click', function() {
        const detailsPage = document.querySelector(".customer-details-page");
        const nameElement = detailsPage.querySelector('.customer-name');
        const addressElement = detailsPage.querySelector('.customer-address');
        const phoneElement = detailsPage.querySelector('.customer-phone');
        const editAddressInput = detailsPage.querySelector('.edit-address');
        const editPhoneInput = detailsPage.querySelector('.edit-phone');
        const editBtn = this;
        const saveBtn = detailsPage.querySelector('.save-btn');
        const cancelBtn = detailsPage.querySelector('.cancel-btn');

        // Switch to edit mode
        nameElement.contentEditable = true;
        nameElement.classList.add('border', 'border-gray-200', 'px-2', 'py-1', 'rounded-lg');
        addressElement.classList.add('hidden');
        phoneElement.classList.add('hidden');
        editAddressInput.classList.remove('hidden');
        editPhoneInput.classList.remove('hidden');
        editAddressInput.value = addressElement.dataset.originalAddress;
        editPhoneInput.value = phoneElement.dataset.originalPhone;
        editBtn.classList.add('hidden');
        saveBtn.classList.remove('hidden');
        cancelBtn.classList.remove('hidden');
    });

    // Save Edited Customer Details
    document.querySelector('.save-btn').addEventListener('click', function() {
        const detailsPage = document.querySelector(".customer-details-page");
        const originalName = detailsPage.querySelector('.customer-name').dataset.originalName;
        const newName = detailsPage.querySelector('.customer-name').textContent.trim();
        const address = detailsPage.querySelector('.edit-address').value.trim();
        const phone = detailsPage.querySelector('.edit-phone').value.trim();

        if (!newName || !address || !phone) {
            alert('Please fill in all fields');
            return;
        }

        fetch('update_customer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `name=${encodeURIComponent(originalName)}&new_name=${encodeURIComponent(newName)}&address=${encodeURIComponent(address)}&phone=${encodeURIComponent(phone)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Customer updated successfully');
                // Update display and reset to view mode
                const nameElement = detailsPage.querySelector('.customer-name');
                const addressElement = detailsPage.querySelector('.customer-address');
                const phoneElement = detailsPage.querySelector('.customer-phone');
                const editAddressInput = detailsPage.querySelector('.edit-address');
                const editPhoneInput = detailsPage.querySelector('.edit-phone');
                const editBtn = detailsPage.querySelector('.edit-btn');
                const saveBtn = this;
                const cancelBtn = detailsPage.querySelector('.cancel-btn');

                nameElement.contentEditable = false;
                nameElement.classList.remove('border', 'border-gray-200', 'px-2', 'py-1', 'rounded-lg');
                nameElement.dataset.originalName = newName;
                addressElement.textContent = address;
                addressElement.dataset.originalAddress = address;
                phoneElement.textContent = phone;
                phoneElement.dataset.originalPhone = phone;
                addressElement.classList.remove('hidden');
                phoneElement.classList.remove('hidden');
                editAddressInput.classList.add('hidden');
                editPhoneInput.classList.add('hidden');
                editBtn.classList.remove('hidden');
                saveBtn.classList.add('hidden');
                cancelBtn.classList.add('hidden');

                // Optionally refresh the customer list or details
                const previewBtn = Array.from(document.querySelectorAll('.preview-btn')).find(btn => 
                    btn.closest('.user-card').querySelector('.customer-name').textContent === originalName
                );
                if (previewBtn) {
                    const card = previewBtn.closest('.user-card');
                    card.querySelector('.customer-name').textContent = newName;
                    card.querySelector('.customer-phone-data').textContent = phone;
                    card.querySelector('.customer-address-data').textContent = address;
                    openCustomerDetails(previewBtn); // Refresh details
                }
            } else {
                alert('Error updating customer: ' + data.message);
            }
        })
        .catch(err => console.error('Update failed:', err));
    });

    // Cancel Edit
    document.querySelector('.cancel-btn').addEventListener('click', function() {
        const detailsPage = document.querySelector(".customer-details-page");
        const nameElement = detailsPage.querySelector('.customer-name');
        const addressElement = detailsPage.querySelector('.customer-address');
        const phoneElement = detailsPage.querySelector('.customer-phone');
        const editAddressInput = detailsPage.querySelector('.edit-address');
        const editPhoneInput = detailsPage.querySelector('.edit-phone');
        const editBtn = detailsPage.querySelector('.edit-btn');
        const saveBtn = detailsPage.querySelector('.save-btn');
        const cancelBtn = this;

        // Revert to original values and view mode
        nameElement.textContent = nameElement.dataset.originalName;
        nameElement.contentEditable = false;
        nameElement.classList.remove('border', 'border-gray-200', 'px-2', 'py-1', 'rounded-lg');
        addressElement.textContent = addressElement.dataset.originalAddress;
        phoneElement.textContent = phoneElement.dataset.originalPhone;
        addressElement.classList.remove('hidden');
        phoneElement.classList.remove('hidden');
        editAddressInput.classList.add('hidden');
        editPhoneInput.classList.add('hidden');
        editBtn.classList.remove('hidden');
        saveBtn.classList.add('hidden');
        cancelBtn.classList.add('hidden');
    });

    // Close Customer Details
    document.querySelectorAll(".close-details").forEach(btn => {
        btn.addEventListener("click", () => {
            btn.closest(".customer-details-page").classList.add("hidden");
            document.body.style.overflow = "";
        });
    });

    // Delete Customer with Confirmation
    document.querySelector('.delete-customer-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this customer?')) {
            const customerName = document.querySelector('.customer-details-page .customer-name').textContent;
            fetch('delete_customer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `name=${encodeURIComponent(customerName)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Customer deleted successfully');
                    location.reload();
                } else {
                    alert('Error deleting customer: ' + data.message);
                }
            })
            .catch(err => console.error('Deletion failed:', err));
        }
    });

    // Payout Modal Handling
    let selectedCustomerName = null;

    function openPayoutModal() {
        const detailsPage = document.querySelector(".customer-details-page");
        if (!detailsPage.classList.contains('hidden')) {
            selectedCustomerName = detailsPage.querySelector(".customer-name").textContent;
            const customerSelect = document.getElementById('customerSelect');
            Array.from(customerSelect.options).forEach(option => {
                if (option.getAttribute('data-name') === selectedCustomerName) {
                    option.selected = true;
                }
            });
            fetchDueLiters(); // Pre-fill due liters for the selected customer
        }
        document.getElementById("payoutModal").classList.remove("hidden");
        document.getElementById("payoutModal").classList.add("flex");
    }

    function closePayoutModal() {
        document.getElementById("payoutModal").classList.add("hidden");
        document.getElementById("payoutModal").classList.remove("flex");
        resetPayoutForm();
    }

    function resetPayoutForm() {
        document.getElementById('dueLiters').value = '';
        document.getElementById('rate').value = '';
        document.getElementById('summaryAmount').textContent = '0.00';
        document.getElementById('amountPaid').value = '';
        document.getElementById('paidLiters').value = '';
        document.getElementById('dueAmount').value = '';
        document.getElementById('dueLitersRemaining').value = '';
    }

    // Fetch Due Liters for Payout
    const customerSelect = document.getElementById('customerSelect');
    const dueLitersInput = document.getElementById('dueLiters');
    const rateInput = document.getElementById('rate');
    const summaryAmount = document.getElementById('summaryAmount');
    const amountPaidInput = document.getElementById('amountPaid');
    const paidLitersInput = document.getElementById('paidLiters');
    const dueAmountInput = document.getElementById('dueAmount');
    const dueLitersRemainingInput = document.getElementById('dueLitersRemaining');

    function fetchDueLiters() {
        const customerName = customerSelect.options[customerSelect.selectedIndex].getAttribute('data-name');
        if (customerName) {
            fetch(`get_customer_details.php?name=${encodeURIComponent(customerName)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        dueLitersInput.value = data.due_liters || 0;
                        updateSummary();
                    } else {
                        alert('Error fetching due liters: ' + data.message);
                    }
                })
                .catch(err => console.error('Fetch error:', err));
        }
    }

    function updateSummary() {
        const dueLiters = parseFloat(dueLitersInput.value) || 0;
        const rate = parseFloat(rateInput.value) || 0;
        const summary = dueLiters * rate;
        summaryAmount.textContent = `₹${summary.toFixed(2)}`;
        updatePayoutDetails();
    }

    function updatePayoutDetails() {
        const dueLiters = parseFloat(dueLitersInput.value) || 0;
        const rate = parseFloat(rateInput.value) || 0;
        const summary = dueLiters * rate;
        const amountPaid = parseFloat(amountPaidInput.value) || 0;
        const dueAmount = summary - amountPaid;
        dueAmountInput.value = dueAmount >= 0 ? dueAmount.toFixed(2) : 0;
        paidLitersInput.value = rate ? (amountPaid / rate).toFixed(2) : 0;
        dueLitersRemainingInput.value = rate ? (dueAmount / rate).toFixed(2) : 0;
    }

    // Event Listeners
    customerSelect.addEventListener('change', fetchDueLiters);
    rateInput.addEventListener('input', updateSummary);
    amountPaidInput.addEventListener('input', updatePayoutDetails);

    // Save Payout
    function savePayout() {
        const customerName = customerSelect.options[customerSelect.selectedIndex].getAttribute('data-name');
        const dueLiters = dueLitersInput.value;
        const rate = rateInput.value;
        const totalAmount = (parseFloat(dueLiters) * parseFloat(rate)).toFixed(2);
        const amountPaid = amountPaidInput.value;
        const dueAmount = dueAmountInput.value;

        if (!customerName || !dueLiters || !rate || !amountPaid) {
            alert('Please fill all fields');
            return;
        }

        fetch('update_payout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `customer_name=${encodeURIComponent(customerName)}&due_liters=${dueLiters}&rate=${rate}&total_amount=${totalAmount}&amount_paid=${amountPaid}&due_amount=${dueAmount}`
        })
        .then(response => response.json())
        .then(data => {
            console.log("Save response:", data);
            if (data.status === 'success') {
                alert('Payout saved successfully');
                closePayoutModal();
                const previewBtn = Array.from(document.querySelectorAll('.preview-btn')).find(btn => 
                    btn.closest('.user-card').querySelector('.customer-name').textContent === customerName
                );
                if (previewBtn) openCustomerDetails(previewBtn);
            } else {
                alert('Error saving payout: ' + data.message);
            }
        })
        .catch(err => console.error('Save error:', err));
    }

// Generate Bill
document.getElementById('generateBill').addEventListener('click', () => {
    const customerName = document.querySelector('.customer-details-page .customer-name').textContent;
    const billMonth = document.getElementById('billMonth').value;
    if (!billMonth) {
        alert('Please select a month');
        return;
    }

    const [year, month] = billMonth.split('-');
    const startDate = `${billMonth}-01 00:00:00`;
    const endDate = new Date(year, month, 0).toISOString().slice(0, 10) + ' 23:59:59'; // Last day of the month

    fetch(`get_customer_details.php?name=${encodeURIComponent(customerName)}`)
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') {
                alert('Error fetching customer details: ' + data.message);
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Load the logo
            const logoUrl = '../assets/icon.jpg';
            const img = new Image();
            img.crossOrigin = "Anonymous";
            img.src = logoUrl;

            img.onload = function() {
                // Header
                doc.addImage(img, 'JPEG', 15, 10, 30, 30);
                doc.setFontSize(18);
                doc.setFont("helvetica", "bold");
                doc.text("Milk Venture Bill Summary", 50, 25);

                // Customer Info
                doc.setFontSize(12);
                doc.setFont("helvetica", "normal");
                doc.text(`Customer: ${customerName}`, 15, 45);
                doc.text(`Month: ${new Date(billMonth + '-01').toLocaleString('default', { month: 'long', year: 'numeric' })}`, 15, 55);

                // Filter orders and payments for the selected month
                const rate = 50; // Hardcoded as per get_bill_summary.php
                const filteredOrders = data.orders.filter(order => {
                    const orderDate = new Date(order.order_time);
                    return orderDate >= new Date(startDate) && orderDate <= new Date(endDate);
                });
                const filteredPayments = data.payments.filter(payment => {
                    const fromDate = new Date(payment.from_date);
                    const toDate = new Date(payment.to_date);
                    const monthStart = new Date(startDate);
                    const monthEnd = new Date(endDate);
                    return (fromDate <= monthEnd && toDate >= monthStart);
                });

                // Orders Table
                doc.setFontSize(14);
                doc.text("Order Records", 15, 70);
                const orderData = filteredOrders.map(order => [
                    order.order_id,
                    new Date(order.order_time).toLocaleString(),
                    order.milk_quantity,
                    (order.milk_quantity * rate).toFixed(2),
                    order.payment_status
                ]);
                doc.autoTable({
                    startY: 75,
                    head: [['Order ID', 'Date', 'Quantity (L)', 'Amount (INR)', 'Status']],
                    body: orderData.length > 0 ? orderData : [['', '', 'No orders for this month', '', '']],
                    theme: 'striped',
                    headStyles: { fillColor: [255, 107, 107], textColor: [255, 255, 255], fontSize: 12, fontStyle: 'bold' },
                    bodyStyles: { fontSize: 10, textColor: [51, 51, 51] },
                    alternateRowStyles: { fillColor: [240, 240, 240] },
                    margin: { left: 15, right: 15 },
                    styles: { lineColor: [200, 200, 200], lineWidth: 0.1 }
                });

                // Payments Table
                const lastY = doc.lastAutoTable.finalY + 10;
                doc.setFontSize(14);
                doc.text("Payment History", 15, lastY);
                const paymentData = filteredPayments.map(payment => [
                    payment.from_date,
                    payment.to_date,
                    payment.total_liters,
                    payment.amount_paid,
                    payment.due_amount,
                    payment.due_amount == 0 ? 'Paid' : 'Due'
                ]);
                doc.autoTable({
                    startY: lastY + 5,
                    head: [['From Date', 'To Date', 'Total Liters', 'Amount Paid (INR)', 'Due Amount (INR)', 'Status']],
                    body: paymentData.length > 0 ? paymentData : [['', '', 'No payments for this month', '', '', '']],
                    theme: 'striped',
                    headStyles: { fillColor: [255, 107, 107], textColor: [255, 255, 255], fontSize: 12, fontStyle: 'bold' },
                    bodyStyles: { fontSize: 10, textColor: [51, 51, 51] },
                    alternateRowStyles: { fillColor: [240, 240, 240] },
                    margin: { left: 15, right: 15 },
                    styles: { lineColor: [200, 200, 200], lineWidth: 0.1 }
                });

                // Summary
                const summaryY = doc.lastAutoTable.finalY + 10;
                const totalMilk = filteredOrders.reduce((sum, order) => sum + parseFloat(order.milk_quantity), 0);
                const totalAmount = totalMilk * rate;
                const amountPaid = filteredOrders.filter(o => o.payment_status === 'Paid').reduce((sum, o) => sum + (o.milk_quantity * rate), 0);
                const dueAmount = totalAmount - amountPaid;

                doc.setFontSize(12);
                doc.text(`Total Milk: ${totalMilk.toFixed(2)} L`, 15, summaryY);
                doc.text(`Total Amount: ₹${totalAmount.toFixed(2)}`, 15, summaryY + 10);
                doc.text(`Amount Paid: ₹${amountPaid.toFixed(2)}`, 15, summaryY + 20);
                doc.text(`Due Amount: ₹${dueAmount.toFixed(2)}`, 15, summaryY + 30);

                // Footer
                const pageHeight = doc.internal.pageSize.height;
                doc.setLineWidth(0.5);
                doc.line(15, pageHeight - 20, 195, pageHeight - 20);
                doc.setFontSize(10);
                doc.setTextColor(100);
                doc.text("Milk Venture - Powered by CodeXs", 15, pageHeight - 10);

                // Save PDF
                doc.save(`bill_summary_${customerName}_${billMonth}.pdf`);
            };

            img.onerror = function() {
                alert('Failed to load logo. Generating PDF without logo.');
                // Same logic as above, just without the logo
                doc.setFontSize(18);
                doc.setFont("helvetica", "bold");
                doc.text("Milk Venture Bill Summary", 15, 25);

                doc.setFontSize(12);
                doc.setFont("helvetica", "normal");
                doc.text(`Customer: ${customerName}`, 15, 45);
                doc.text(`Month: ${new Date(billMonth + '-01').toLocaleString('default', { month: 'long', year: 'numeric' })}`, 15, 55);

                const rate = 50;
                const filteredOrders = data.orders.filter(order => {
                    const orderDate = new Date(order.order_time);
                    return orderDate >= new Date(startDate) && orderDate <= new Date(endDate);
                });
                const filteredPayments = data.payments.filter(payment => {
                    const fromDate = new Date(payment.from_date);
                    const toDate = new Date(payment.to_date);
                    const monthStart = new Date(startDate);
                    const monthEnd = new Date(endDate);
                    return (fromDate <= monthEnd && toDate >= monthStart);
                });

                doc.setFontSize(14);
                doc.text("Order Records", 15, 70);
                const orderData = filteredOrders.map(order => [
                    order.order_id,
                    new Date(order.order_time).toLocaleString(),
                    order.milk_quantity,
                    (order.milk_quantity * rate).toFixed(2),
                    order.payment_status
                ]);
                doc.autoTable({
                    startY: 75,
                    head: [['Order ID', 'Date', 'Quantity (L)', 'Amount (INR)', 'Status']],
                    body: orderData.length > 0 ? orderData : [['', '', 'No orders for this month', '', '']],
                    theme: 'striped',
                    headStyles: { fillColor: [255, 107, 107], textColor: [255, 255, 255], fontSize: 12, fontStyle: 'bold' },
                    bodyStyles: { fontSize: 10, textColor: [51, 51, 51] },
                    alternateRowStyles: { fillColor: [240, 240, 240] },
                    margin: { left: 15, right: 15 },
                    styles: { lineColor: [200, 200, 200], lineWidth: 0.1 }
                });

                const lastY = doc.lastAutoTable.finalY + 10;
                doc.setFontSize(14);
                doc.text("Payment History", 15, lastY);
                const paymentData = filteredPayments.map(payment => [
                    payment.from_date,
                    payment.to_date,
                    payment.total_liters,
                    payment.amount_paid,
                    payment.due_amount,
                    payment.due_amount == 0 ? 'Paid' : 'Due'
                ]);
                doc.autoTable({
                    startY: lastY + 5,
                    head: [['From Date', 'To Date', 'Total Liters', 'Amount Paid (INR)', 'Due Amount (INR)', 'Status']],
                    body: paymentData.length > 0 ? paymentData : [['', '', 'No payments for this month', '', '', '']],
                    theme: 'striped',
                    headStyles: { fillColor: [255, 107, 107], textColor: [255, 255, 255], fontSize: 12, fontStyle: 'bold' },
                    bodyStyles: { fontSize: 10, textColor: [51, 51, 51] },
                    alternateRowStyles: { fillColor: [240, 240, 240] },
                    margin: { left: 15, right: 15 },
                    styles: { lineColor: [200, 200, 200], lineWidth: 0.1 }
                });

                const summaryY = doc.lastAutoTable.finalY + 10;
                const totalMilk = filteredOrders.reduce((sum, order) => sum + parseFloat(order.milk_quantity), 0);
                const totalAmount = totalMilk * rate;
                const amountPaid = filteredOrders.filter(o => o.payment_status === 'Paid').reduce((sum, o) => sum + (o.milk_quantity * rate), 0);
                const dueAmount = totalAmount - amountPaid;

                doc.setFontSize(12);
                doc.text(`Total Milk: ${totalMilk.toFixed(2)} L`, 15, summaryY);
                doc.text(`Total Amount: ₹${totalAmount.toFixed(2)}`, 15, summaryY + 10);
                doc.text(`Amount Paid: ₹${amountPaid.toFixed(2)}`, 15, summaryY + 20);
                doc.text(`Due Amount: ₹${dueAmount.toFixed(2)}`, 15, summaryY + 30);

                const pageHeight = doc.internal.pageSize.height;
                doc.setLineWidth(0.5);
                doc.line(15, pageHeight - 20, 195, pageHeight - 20);
                doc.setFontSize(10);
                doc.setTextColor(100);
                doc.text("Milk Venture - Powered by CodeXs", 15, pageHeight - 10);

                doc.save(`bill_summary_${customerName}_${billMonth}.pdf`);
            };
        })
        .catch(err => console.error('Error fetching customer data:', err));
});

    // Tab Switching for Record and Payment
    document.getElementById('recordTab').addEventListener('click', () => {
        document.getElementById('recordContent').classList.remove('hidden');
        document.getElementById('paymentContent').classList.add('hidden');
        document.getElementById('recordTab').classList.add('text-primary', 'border-b-2', 'border-primary');
        document.getElementById('paymentTab').classList.remove('text-primary', 'border-b-2', 'border-primary');
        document.getElementById('paymentTab').classList.add('text-gray-500');
    });
    document.getElementById('paymentTab').addEventListener('click', () => {
        document.getElementById('paymentContent').classList.remove('hidden');
        document.getElementById('recordContent').classList.add('hidden');
        document.getElementById('paymentTab').classList.add('text-primary', 'border-b-2', 'border-primary');
        document.getElementById('recordTab').classList.remove('text-primary', 'border-b-2', 'border-primary');
        document.getElementById('recordTab').classList.add('text-gray-500');
    });
</script>
    </div>
</body>
</html>