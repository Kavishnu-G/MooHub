<?php
include '../database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST['name'];
    $new_email = $_POST['email'];

    $query = "UPDATE users SET email = ? WHERE name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(1, $new_email, PDO::PARAM_STR);
    $stmt->bindParam(2, $user_name, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $success_message = "Email updated successfully!";
    } else {
        $error_message = "Failed to update email. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/logo.png">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#FF6B6B",
                        secondary: "#4ECDC4",
                    }
                }
            }
        };
    </script>
    <style>
        .toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 16px 24px;
            border-radius: 8px;
            background: #FF6B6B;
            color: white;
            display: none;
            z-index: 50;
        }
        .dark { background-color: #1a1a1a; color: #f0f0f0; }
        .dark .bg-white { background-color: #2a2a2a; }
        .dark .text-gray-600 { color: #a0a0a0; }
        .dark .border-gray-200 { border-color: #444; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="max-w-[375px] mx-auto min-h-screen pb-16">
        <!-- Top Navigation from dashboard.php -->
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
                    <a href="../dashboard/logout.php" class="px-3 py-1 bg-red-500 text-white rounded-button text-sm hover:bg-red-600">Logout</a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="pt-16 px-4 space-y-6">
            <section class="bg-white p-4 rounded-lg shadow-sm">
                <h2 class="text-lg font-medium mb-4">Change Email</h2>

                <?php if (isset($success_message)): ?>
                    <p class="text-green-600"><?php echo $success_message; ?></p>
                <?php elseif (isset($error_message)): ?>
                    <p class="text-red-600"><?php echo $error_message; ?></p>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div class="relative">
                        <input type="text" name="name" required class="w-full p-3 pl-10 border rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your name">
                        <i class="ri-user-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <div class="relative">
                        <input type="email" name="email" required class="w-full p-3 pl-10 border rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter new email">
                        <i class="ri-mail-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <button type="submit" class="w-full bg-primary text-white p-3 rounded-lg hover:bg-primary/90 transition flex items-center justify-center">
                        <i class="ri-save-line mr-2"></i> Update Email
                    </button>
                </form>
            </section>

            <section class="bg-white p-4 rounded-lg shadow-sm">
                <h2 class="text-lg font-medium mb-4">Developer Support</h2>
                <div class="space-y-3">
                    <a href="mailto:support@example.com" class="flex items-center p-3 border rounded-lg hover:bg-secondary transition cursor-pointer">
                        <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full">
                            <i class="ri-mail-line text-primary"></i>
                        </div>
                        <div class="ml-3">
                            <div class="font-medium">Email Support</div>
                            <div class="text-sm text-gray-600">codexshelp@gmail.com</div>
                        </div>
                    </a>
                    <a href="tel:+1234567890" class="flex items-center p-3 border rounded-lg hover:bg-secondary transition cursor-pointer">
                        <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full">
                            <i class="ri-phone-line text-primary"></i>
                        </div>
                        <div class="ml-3">
                            <div class="font-medium">Phone Support</div>
                            <div class="text-sm text-gray-600">+91 9940082233</div>
                        </div>
                    </a>
                    <a href="https://wa.me/9940082233" class="flex item's-center p-3 border rounded-lg hover:bg-secondary transition cursor-pointer">
                        <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full">
                            <i class="ri-whatsapp-line text-primary"></i>
                        </div>
                        <div class="ml-3">
                            <div class="font-medium">WhatsApp Support</div>
                            <div class="text-sm text-gray-600">+91 9940082233</div>
                        </div>
                    </a>
                </div>
            </section>
        </main>

        <!-- Bottom Navigation from dashboard.php -->
        <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 shadow-lg">
            <div class="grid grid-cols-4 gap-1 p-2 max-w-[375px] mx-auto">
                <a href="../dashboard/dashboard.php" class="flex flex-col items-center justify-center p-2 hover:text-primary">
                    <i class="ri-home-3-line text-xl"></i>
                    <span class="text-xs mt-1">Dashboard</span>
                </a>
                <a href="../customers/customers.php" class="flex flex-col items-center justify-center p-2 hover:text-primary">
                    <i class="ri-team-line text-xl"></i>
                    <span class="text-xs mt-1">Customers</span>
                </a>
                <a href="profile.php" class="flex flex-col items-center justify-center p-2 text-primary">
                    <i class="ri-user-settings-line text-xl"></i>
                    <span class="text-xs mt-1">Profile</span>
                </a>
                <a href="../payout/payout.php" class="flex flex-col items-center justify-center p-2 hover:text-primary">
                    <i class="ri-wallet-3-line text-xl"></i>
                    <span class="text-xs mt-1">Payout</span>
                </a>
            </div>
        </nav>

        <footer class="bg-white text-center py-6 mt-8">
            <div class="social-links flex justify-center space-x-4 mb-3">
                <a href="#"><img src="../assets/github.png" alt="GitHub" class="w-8 h-8"></a>
                <a href="#"><img src="../assets/discord.png" alt="Discord" class="w-8 h-8"></a>
            </div>
            <div class="legal text-gray-600 text-sm mb-2">
                <a href="#" class="hover:underline">Privacy Policy</a> | 
                <a href="#" class="hover:underline">Terms &amp; Conditions</a>
            </div>
            <p class="text-gray-500 text-xs">© 2025 CodeXS. All Rights Reserved.</p>
        </footer>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        let isDark = false;
        themeToggle.addEventListener('click', () => {
            isDark = !isDark;
            document.body.classList.toggle('dark');
            themeToggle.innerHTML = isDark ? '<i class="ri-moon-line text-gray-600 ri-lg"></i>' : '<i class="ri-sun-line text-gray-600 ri-lg"></i>';
        });

        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>