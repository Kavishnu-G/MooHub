<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
require '../database.php';

// Determine selected date from query, default is today
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");

// Get the first and last day of the selected month
$startMonth = date("Y-m-01", strtotime($selectedDate));
$endMonth = date("Y-m-t", strtotime($selectedDate));

// Build an array with every date of the month
$dates = [];
$current = $startMonth;
while ($current <= $endMonth) {
    $dates[] = $current;
    $current = date("Y-m-d", strtotime("$current +1 day"));
}

// Format the selected date for display
$currentDateDisplay = date("d F Y", strtotime($selectedDate));

// Fetch orders for the current user on the selected date
$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND DATE(order_time) = ? ORDER BY order_time DESC");
$stmt->execute([$user_id, $selectedDate]);
$orders = $stmt->fetchAll();

// Calculate total milk quantity for the selected date
$totalSalesStmt = $conn->prepare("SELECT SUM(milk_quantity) AS total_sales FROM orders WHERE user_id = ? AND DATE(order_time) = ?");
$totalSalesStmt->execute([$user_id, $selectedDate]);
$totalSalesRow = $totalSalesStmt->fetch();
$totalSales = $totalSalesRow && $totalSalesRow['total_sales'] ? $totalSalesRow['total_sales'] : 0;

// Fetch all customers for the dropdown
$stmt_customers = $conn->prepare("SELECT name FROM customers ORDER BY name ASC");
$stmt_customers->execute();
$all_customers = $stmt_customers->fetchAll(PDO::FETCH_COLUMN); // Fetch only the 'name' column as an array
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Milk Venture Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/logo.png">
    <!-- Add jsPDF and jspdf-autotable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.3/jspdf.plugin.autotable.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: '#FF6B6B', secondary: '#4EDC4E' },
                    borderRadius: { 'none': '0px', 'sm': '4px', DEFAULT: '8px', 'md': '12px', 'lg': '16px', 'xl': '20px', '2xl': '24px', '3xl': '32px', 'full': '9999px', 'button': '8px' }
                }
            }
        }
    </script>
    <style>
        .order-card { animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .welcome-notification { position: fixed; top: -100px; left: 50%; transform: translateX(-50%); background-color: #FF6B6B; color: white; padding: 12px 24px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1000; animation: slideDown 0.5s ease-out forwards, slideUp 0.5s ease-out 3s forwards; }
        @keyframes slideDown { to { top: 20px; } }
        @keyframes slideUp { to { top: -100px; } }
        @keyframes slideOut { to { transform: translateX(100%); opacity: 0; } }
        .dark { background-color: #1a1a1a; color: #f0f0f0; }
        .dark .bg-white { background-color: #2a2a2a; }
        .dark .text-gray-600 { color: #a0a0a0; }
        .dark .border-gray-200 { border-color: #444; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen relative">
    <div id="welcomeNotification" class="welcome-notification">
        Welcome Back <?php echo htmlspecialchars($_SESSION['user']['name']); ?>! Thank you for Choosing CodeXs.
    </div>
    
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

    <main class="pt-20 pb-20 px-4 max-w-4xl mx-auto">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-4">
            <div class="relative w-full sm:w-1/2">
                <input type="date" id="dateDropdown" class="w-full px-4 py-2 text-sm bg-white border border-gray-200 rounded-button" value="<?php echo $selectedDate; ?>" onchange="window.location.href='?date=' + this.value;">
            </div>
            <div class="flex bg-white rounded-full border border-gray-200 p-1">
                <button id="filterAll" class="px-4 py-1.5 text-sm rounded-full bg-primary text-white">ALL</button>
                <button id="filterDay" class="px-4 py-1.5 text-sm rounded-full text-gray-600">☀️</button>
                <button id="filterNight" class="px-4 py-1.5 text-sm rounded-full text-gray-600">🌙</button>
            </div>
        </div>

        <div class="relative bg-white rounded-lg border border-gray-200 p-3 mb-4">
            <div class="flex justify-between items-center">
                <div>
                    <div class="text-sm text-gray-600">Total Milk Sold</div>
                    <div class="text-2xl font-semibold"><?php echo htmlspecialchars($totalSales); ?> L</div>
                </div>
                <button class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-primary transition-colors" onclick="downloadSalesReport()">
                    <i class="ri-download-line ri-lg"></i>
                </button>
            </div>
        </div>

        <div class="space-y-3" id="ordersList">
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): 
                    $orderTimestamp = date("F d, Y - h:i A", strtotime($order['order_time']));
                    $orderHour = date("H", strtotime($order['order_time']));
                    $emoji = ($orderHour < 12) ? '☀️' : '🌙';
                ?>
                    <div class="order-card bg-white rounded-lg border border-gray-200 p-4" data-orderid="<?php echo htmlspecialchars($order['order_id']); ?>">
                        <div class="flex flex-col sm:flex-row justify-between items-start mb-3">
                            <div>
                                <h3 class="font-medium">Order #<?php echo htmlspecialchars($order['order_id']); ?></h3>
                                <p class="text-sm text-gray-500"><?php echo $orderTimestamp; ?></p>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['user_name']); ?></p>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium <?php echo ($order['payment_status'] == 'Paid') ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?> rounded">
                                <?php echo htmlspecialchars($order['payment_status']); ?>
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="order-emoji text-gray-600"><?php echo $emoji; ?></span>
                            <button class="delete-btn text-gray-400 hover:text-red-500"><i class="ri-delete-bin-line"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-600 text-center">No orders yet.</p>
            <?php endif; ?>
        </div>

        <div id="confirmDialog" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-72">
                <h3 class="text-lg font-medium mb-4">Confirm Delete</h3>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this order?</p>
                <div class="flex justify-end gap-3">
                    <button id="cancelDelete" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-button">Cancel</button>
                    <button id="confirmDelete" class="px-4 py-2 text-sm bg-red-500 text-white hover:bg-red-600 rounded-button">Delete</button>
                </div>
            </div>
        </div>
        
        <button id="openOrderModal" class="fixed bottom-24 right-6 w-14 h-14 bg-primary text-white rounded-full shadow-lg flex items-center justify-center hover:bg-primary/90 transition-colors z-40">
            <i class="ri-add-fill ri-xl"></i>
        </button>

        <div id="orderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-sm relative">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium">New Order</h3>
                    <button id="closeOrderModal" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line ri-lg"></i></button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Order ID</label>
                        <input type="text" id="orderId" class="w-full px-3 py-2 border border-gray-200 rounded-button bg-gray-50" readonly>
                    </div>
                    <div class="relative">
    <label class="block text-sm text-gray-600 mb-1">User Name</label>
    <div class="relative flex items-center">
        <button id="voiceInput" class="absolute left-3 text-gray-400 hover:text-primary">
            <i class="ri-mic-line"></i>
        </button>
        <input type="text" id="userName" name="customerName" class="w-full pl-10 pr-3 py-2 border border-gray-200 rounded-button" placeholder="Enter user name" autocomplete="off">
        <div id="userSuggestions" class="hidden absolute top-full left-0 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 max-h-60 overflow-y-auto"></div>
    </div>
</div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Milk Quantity (L)</label>
                        <input type="number" id="milkQuantity" class="w-full px-3 py-2 border border-gray-200 rounded-button" placeholder="Enter quantity">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Time</label>
                        <div class="flex items-center gap-2">
                            <span id="timeEmoji" class="text-xl">☀️</span>
                            <input type="text" id="timestamp_display" class="flex-1 px-3 py-2 border border-gray-200 rounded-button bg-gray-50" readonly>
                            <input type="hidden" id="timestamp_hidden" name="timestamp">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Payment Status</label>
                        <div class="flex items-center gap-2 mt-1">
                            <button id="paymentToggle" class="relative inline-flex items-center h-6 rounded-full w-11 bg-gray-200" role="switch">
                                <span class="sr-only">Payment status</span>
                                <span id="toggleButton" class="inline-block w-4 h-4 transform translate-x-1 bg-white rounded-full transition-transform duration-200 ease-in-out"></span>
                            </button>
                            <span id="paymentStatus" class="text-sm text-gray-600">Due</span>
                        </div>
                    </div>
                    <button id="saveOrder" class="w-full py-2 bg-primary text-white rounded-button hover:bg-primary/90 transition-colors">Save Order</button>
                </div>
            </div>
        </div>
    </main>

    <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 shadow-lg">
        <div class="grid grid-cols-4 gap-1 p-2 max-w-[375px] mx-auto">
            <a href="dashboard.php" class="flex flex-col items-center justify-center p-2 hover:text-primary">
                <i class="ri-home-3-line text-xl"></i><span class="text-xs mt-1">Dashboard</span>
            </a>
            <a href="../customers/customers.php" class="flex flex-col items-center justify-center p-2 hover:text-primary">
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
function setCurrentTimestamp() {
    const now = new Date();
    const optionsDisplay = { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true };
    document.getElementById('timestamp_display').value = now.toLocaleString('en-US', optionsDisplay);
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('timestamp_hidden').value = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

function downloadSalesReport() {
    const { jsPDF } = window.jspdf; // Access jsPDF from the UMD module
    const doc = new jsPDF();

    // Load the logo image
    const logoUrl = '../assets/icon.jpg'; // Adjust path if needed
    const img = new Image();
    img.crossOrigin = "Anonymous"; // Handle cross-origin if served from a different domain
    img.src = logoUrl;

    img.onload = function() {
        // Header: Add logo and title
        doc.addImage(img, 'JPEG', 15, 10, 30, 30); // Logo at top-left (x: 15, y: 10, width: 30, height: 30)
        doc.setFontSize(18);
        doc.setFont("helvetica", "bold");
        doc.text("Milk Venture Sales Report", 50, 25); // Title next to logo

        // Report details
        doc.setFontSize(12);
        doc.setFont("helvetica", "normal");
        doc.text(`Date: ${'<?php echo $currentDateDisplay; ?>'}`, 15, 45);
        doc.text(`Total Sales: ${'<?php echo htmlspecialchars($totalSales); ?> L'}`, 15, 55);

        // Prepare table data
        let orders = [];
        document.querySelectorAll('.order-card').forEach(orderCard => {
            const orderId = orderCard.getAttribute('data-orderid');
            const customer = orderCard.querySelector('p.text-gray-600').textContent; // Customer name
            const time = orderCard.querySelector('p.text-gray-500').textContent; // Order timestamp
            const status = orderCard.querySelector('span').textContent; // Payment status

            // Fetch milk quantity from the server-side PHP data (not directly available in DOM)
            // Since milk_quantity isn't in the DOM, we'll assume it's available via a data attribute or fetch it
            // For simplicity, we'll extract it from PHP orders array passed to JS
            const order = <?php echo json_encode($orders); ?>.find(o => o.order_id === orderId);
            const milkQuantity = order ? order.milk_quantity : 'N/A'; // Fallback if not found
            const timeWithLiters = `${time} (${milkQuantity} L)`; // Combine time and liters

            orders.push([orderId, customer, timeWithLiters, status]);
        });

        // Add table using jspdf-autotable
        doc.autoTable({
            startY: 65, // Start below the header
            head: [['Order ID', 'Customer', 'Time (Liters)', 'Status']], // Updated header
            body: orders, // Table rows
            theme: 'striped', // Neat table style
            headStyles: {
                fillColor: [255, 107, 107], // Primary color (#FF6B6B)
                textColor: [255, 255, 255], // White text
                fontSize: 12,
                fontStyle: 'bold'
            },
            bodyStyles: {
                fontSize: 10,
                textColor: [51, 51, 51] // Dark gray text
            },
            alternateRowStyles: {
                fillColor: [240, 240, 240] // Light gray for alternate rows
            },
            margin: { top: 65, left: 15, right: 15 },
            styles: {
                lineColor: [200, 200, 200], // Light gray borders
                lineWidth: 0.1
            },
            columnStyles: {
                2: { cellWidth: 'auto' } // Allow "Time (Liters)" column to adjust width dynamically
            }
        });

        // Footer: Add a line and company name
        const pageHeight = doc.internal.pageSize.height;
        doc.setLineWidth(0.5);
        doc.line(15, pageHeight - 20, 195, pageHeight - 20); // Horizontal line
        doc.setFontSize(10);
        doc.setTextColor(100);
        doc.text("Milk Venture - Powered by CodeXs", 15, pageHeight - 10);

        // Save the PDF
        doc.save(`sales_report_${'<?php echo $currentDateDisplay; ?>'.replace(/\s/g, '_')}.pdf`);
    };

    img.onerror = function() {
        alert('Failed to load logo image. Generating PDF without logo.');
        // Generate PDF without logo if image fails to load
        doc.setFontSize(18);
        doc.setFont("helvetica", "bold");
        doc.text("Milk Venture Sales Report", 15, 25);

        doc.setFontSize(12);
        doc.setFont("helvetica", "normal");
        doc.text(`Date: ${'<?php echo $currentDateDisplay; ?>'}`, 15, 45);
        doc.text(`Total Sales: ${'<?php echo htmlspecialchars($totalSales); ?> L'}`, 15, 55);

        let orders = [];
        document.querySelectorAll('.order-card').forEach(orderCard => {
            const orderId = orderCard.getAttribute('data-orderid');
            const customer = orderCard.querySelector('p.text-gray-600').textContent;
            const time = orderCard.querySelector('p.text-gray-500').textContent;
            const status = orderCard.querySelector('span').textContent;

            const order = <?php echo json_encode($orders); ?>.find(o => o.order_id === orderId);
            const milkQuantity = order ? order.milk_quantity : 'N/A';
            const timeWithLiters = `${time} (${milkQuantity} L)`;

            orders.push([orderId, customer, timeWithLiters, status]);
        });

        doc.autoTable({
            startY: 65,
            head: [['Order ID', 'Customer', 'Time (Liters)', 'Status']],
            body: orders,
            theme: 'striped',
            headStyles: { fillColor: [255, 107, 107], textColor: [255, 255, 255], fontSize: 12, fontStyle: 'bold' },
            bodyStyles: { fontSize: 10, textColor: [51, 51, 51] },
            alternateRowStyles: { fillColor: [240, 240, 240] },
            margin: { top: 65, left: 15, right: 15 },
            styles: { lineColor: [200, 200, 200], lineWidth: 0.1 },
            columnStyles: {
                2: { cellWidth: 'auto' } // Allow "Time (Liters)" column to adjust width
            }
        });

        const pageHeight = doc.internal.pageSize.height;
        doc.setLineWidth(0.5);
        doc.line(15, pageHeight - 20, 195, pageHeight - 20);
        doc.setFontSize(10);
        doc.setTextColor(100);
        doc.text("Milk Venture - Powered by CodeXs", 15, pageHeight - 10);

        doc.save(`sales_report_${'<?php echo $currentDateDisplay; ?>'.replace(/\s/g, '_')}.pdf`);
    };
}

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('orderModal');
    const openBtn = document.getElementById('openOrderModal');
    const closeBtn = document.getElementById('closeOrderModal');
    const voiceBtn = document.getElementById('voiceInput');
    const paymentToggle = document.getElementById('paymentToggle');
    const toggleButton = document.getElementById('toggleButton');
    const paymentStatus = document.getElementById('paymentStatus');
    const saveBtn = document.getElementById('saveOrder');
    let isPaid = false;

    function generateOrderId() {
        const prefix = 'MV';
        const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
        return `${prefix}${random}`;
    }

    function resetForm() {
        document.getElementById('orderId').value = generateOrderId();
        document.getElementById('userName').value = '';
        document.getElementById('milkQuantity').value = '';
        setCurrentTimestamp();
        isPaid = false;
        toggleButton.classList.remove('translate-x-6');
        toggleButton.classList.add('translate-x-1');
        paymentToggle.classList.remove('bg-green-500');
        paymentToggle.classList.add('bg-gray-200');
        paymentStatus.textContent = 'Due';
    }

    openBtn.addEventListener('click', () => { modal.classList.remove('hidden'); resetForm(); });
    closeBtn.addEventListener('click', () => { modal.classList.add('hidden'); });

    paymentToggle.addEventListener('click', () => {
        isPaid = !isPaid;
        toggleButton.classList.toggle('translate-x-6');
        toggleButton.classList.toggle('translate-x-1');
        paymentToggle.classList.toggle('bg-green-500');
        paymentToggle.classList.toggle('bg-gray-200');
        paymentStatus.textContent = isPaid ? 'Paid' : 'Due';
    });

    saveBtn.addEventListener('click', () => {
        const orderId = document.getElementById('orderId').value;
        const userName = document.getElementById('userName').value;
        const quantity = document.getElementById('milkQuantity').value;
        const timestamp = document.getElementById('timestamp_hidden').value;
        if (!userName || !quantity || quantity <= 0) {
            alert('Please fill in all required fields with valid values');
            return;
        }
        const orderData = new FormData();
        orderData.append('order_id', orderId);
        orderData.append('user_name', userName);
        orderData.append('milk_quantity', quantity);
        orderData.append('timestamp', timestamp);
        orderData.append('payment_status', isPaid ? 'Paid' : 'Due');

        fetch('save_order.php', {
            method: 'POST',
            body: orderData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error saving order: ' + data.message);
            }
        })
        .catch(err => console.error(err));
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.add('hidden');
    });

    const filterAll = document.getElementById('filterAll');
    const filterDay = document.getElementById('filterDay');
    const filterNight = document.getElementById('filterNight');
    function filterOrders(type) {
        const orders = document.querySelectorAll('.order-card');
        orders.forEach(order => {
            const emoji = order.querySelector('.order-emoji').textContent;
            order.style.display = (type === 'all' || (type === 'day' && emoji === '☀️') || (type === 'night' && emoji === '🌙')) ? 'block' : 'none';
        });
    }
    function updateFilterButtons(activeButton) {
        [filterAll, filterDay, filterNight].forEach(button => {
            button.classList.remove('bg-primary', 'text-white');
            button.classList.add('text-gray-600');
        });
        activeButton.classList.remove('text-gray-600');
        activeButton.classList.add('bg-primary', 'text-white');
    }
    filterAll.addEventListener('click', () => { filterOrders('all'); updateFilterButtons(filterAll); });
    filterDay.addEventListener('click', () => { filterOrders('day'); updateFilterButtons(filterDay); });
    filterNight.addEventListener('click', () => { filterOrders('night'); updateFilterButtons(filterNight); });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const dialog = document.getElementById('confirmDialog');
            const orderCard = e.target.closest('.order-card');
            dialog.classList.remove('hidden');
            document.getElementById('cancelDelete').onclick = () => dialog.classList.add('hidden');
            document.getElementById('confirmDelete').onclick = () => {
                const orderId = orderCard.getAttribute('data-orderid');
                fetch('delete_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `order_id=${encodeURIComponent(orderId)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        orderCard.style.animation = 'slideOut 0.3s ease-out forwards';
                        setTimeout(() => { orderCard.remove(); dialog.classList.add('hidden'); }, 300);
                    } else {
                        alert('Error deleting order: ' + data.message);
                    }
                })
                .catch(err => console.error(err));
            };
        });
    });

    const themeToggle = document.getElementById('themeToggle');
    let isDark = false;
    themeToggle.addEventListener('click', () => {
        isDark = !isDark;
        document.body.classList.toggle('dark');
        themeToggle.innerHTML = isDark ? '<i class="ri-moon-line text-gray-600 ri-lg"></i>' : '<i class="ri-sun-line text-gray-600 ri-lg"></i>';
    });

    // Customer suggestions for userName input
    const userNameInput = document.getElementById('userName');
    const userSuggestionsDiv = document.getElementById('userSuggestions');
    const allCustomers = <?php echo json_encode($all_customers); ?>; // Pass PHP array to JS

    function showSuggestions(inputValue) {
        userSuggestionsDiv.innerHTML = ''; // Clear previous suggestions
        if (!inputValue.trim()) {
            // Show all customers when input is empty
            if (allCustomers.length > 0) {
                allCustomers.forEach(name => {
                    const suggestionItem = document.createElement('div');
                    suggestionItem.className = 'suggestion-item px-3 py-2 hover:bg-gray-50 cursor-pointer';
                    suggestionItem.textContent = name;
                    suggestionItem.addEventListener('click', () => {
                        userNameInput.value = name;
                        userSuggestionsDiv.classList.add('hidden');
                    });
                    userSuggestionsDiv.appendChild(suggestionItem);
                });
                userSuggestionsDiv.classList.remove('hidden');
            }
            return;
        }

        // Filter customers based on input
        const filteredCustomers = allCustomers.filter(name => 
            name.toLowerCase().startsWith(inputValue.toLowerCase())
        );

        if (filteredCustomers.length > 0) {
            filteredCustomers.forEach(name => {
                const suggestionItem = document.createElement('div');
                suggestionItem.className = 'suggestion-item px-3 py-2 hover:bg-gray-50 cursor-pointer';
                suggestionItem.textContent = name;
                suggestionItem.addEventListener('click', () => {
                    userNameInput.value = name;
                    userSuggestionsDiv.classList.add('hidden');
                });
                userSuggestionsDiv.appendChild(suggestionItem);
            });
            userSuggestionsDiv.classList.remove('hidden');
        } else {
            const noMatchItem = document.createElement('div');
            noMatchItem.className = 'px-3 py-2 text-gray-500';
            noMatchItem.textContent = 'User not found';
            userSuggestionsDiv.appendChild(noMatchItem);
            userSuggestionsDiv.classList.remove('hidden');
        }
    }

    userNameInput.addEventListener('input', (e) => {
        showSuggestions(e.target.value);
    });

    userNameInput.addEventListener('focus', () => {
        showSuggestions(userNameInput.value);
    });

    document.addEventListener('click', (e) => {
        if (!userNameInput.contains(e.target) && !userSuggestionsDiv.contains(e.target)) {
            userSuggestionsDiv.classList.add('hidden');
        }
    });

    voiceBtn.addEventListener('click', async () => {
        try {
            const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
            recognition.lang = 'en-US';
            recognition.start();
            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                userNameInput.value = transcript;
                showSuggestions(transcript);
            };
        } catch (error) {
            console.error('Speech recognition not supported:', error);
        }
    });

    setCurrentTimestamp();
});
</script>
</body>
</html>