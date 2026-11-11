<!DOCTYPE html>
<html lang="id" data-theme="clean">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'Kalsel-SecDashboard - SPBE Diskominfo Multi-Theme SIEM' }}</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700;800&family=Orbitron:wght@600;700;800;900&family=Press+Start+2P&family=Rajdhani:wght@600;700&family=Silkscreen:wght@400;700&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-amber-300 selection:text-black">

  @include('components.header')

  <div class="flex-1 max-w-[1700px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col lg:flex-row gap-6">
    @include('components.sidebar')

    <main class="flex-1 min-w-0 space-y-6">
      {{ $slot }}
    </main>
  </div>

  <!-- MODALS MUST BE INCLUDED HERE -->
  @include('components.modals.credential-modal')
  @include('components.modals.action-modal')

</body>
</html>