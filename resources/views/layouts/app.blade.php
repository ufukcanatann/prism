<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PRISM Framework')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo/favicon.png') }}">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">

    @yield('styles')
</head>

<body>

    <!-- Main Content - Full Width -->
    <main style="padding: 0; margin: 0;">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-transparent py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; {{ date('Y') }} PRISM Framework. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0">Modern PHP Framework for Enterprise Applications</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/js/app.js"></script>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function () {
            const navbar = document.getElementById('mainNavbar');
            if (window.scrollY > 50) {
                navbar.style.backgroundColor = '#0d4073';
                navbar.style.backdropFilter = 'blur(10px)';
                navbar.style.transition = 'all 0.3s ease';
            } else {
                navbar.style.backgroundColor = 'transparent';
                navbar.style.backdropFilter = 'none';
            }
        });

        // Tabs system for documentation - Handled by app.js
    </script>

    @yield('scripts')
</body>

</html>