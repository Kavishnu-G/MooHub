<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payout - Coming Soon</title>
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
          },
          borderRadius: { 'none': '0px', 'sm': '4px', DEFAULT: '8px', 'md': '12px', 'lg': '16px', 'xl': '20px', '2xl': '24px', '3xl': '32px', 'full': '9999px', 'button': '8px' }
        }
      }
    }
  </script>
  <style>
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
          <a href="logout.php" class="px-3 py-1 bg-red-500 text-white rounded-button text-sm hover:bg-red-600">Logout</a>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-16 px-4 flex items-center justify-center min-h-[calc(100vh-120px)]">
      <h2 class="text-2xl font-bold text-gray-700">Coming Soon...</h2>
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
        <a href="../profile/profile.php" class="flex flex-col items-center justify-center p-2 hover:text-primary">
          <i class="ri-user-settings-line text-xl"></i>
          <span class="text-xs mt-1">Profile</span>
        </a>
        <a href="payout.php" class="flex flex-col items-center justify-center p-2 text-primary">
          <i class="ri-wallet-3-line text-xl"></i>
          <span class="text-xs mt-1">Payout</span>
        </a>
      </div>
    </nav>
  </div>

  <script>
    const themeToggle = document.getElementById('themeToggle');
    let isDark = false;
    themeToggle.addEventListener('click', () => {
      isDark = !isDark;
      document.body.classList.toggle('dark');
      themeToggle.innerHTML = isDark ? '<i class="ri-moon-line text-gray-600 ri-lg"></i>' : '<i class="ri-sun-line text-gray-600 ri-lg"></i>';
    });
  </script>
</body>
</html>