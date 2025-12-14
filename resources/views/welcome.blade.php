<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>CDMS SISTEM</title>
	<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

	<style>
		* {
			font-family: 'Inter', system-ui, sans-serif;
		}

		@keyframes float {
			0%,100% { transform: translateY(0); }
			50% { transform: translateY(-10px); }
		}

		.animate-float {
			animation: float 6s ease-in-out infinite;
		}
	</style>

	<script>
		function toggleMenu() {
			const menu = document.getElementById("mobileMenu");
			menu.classList.toggle("translate-x-full");
		}
	</script>
</head>

<body class="min-h-screen bg-slate-950 text-white antialiased">

	<!-- Background Effects -->
	<div class="fixed inset-0 -z-10">
		<div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl"></div>
		<div class="absolute bottom-0 right-1/4 w-96 h-96 bg-sky-400/20 rounded-full blur-3xl"></div>
		<div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:64px_64px]"></div>
	</div>

	<!-- Navigation -->
	<nav class="fixed top-0 w-full z-50 bg-slate-950/80 backdrop-blur-xl border-b border-slate-800/50">
		<div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">

			<a href="#" class="text-xl font-bold bg-gradient-to-r from-blue-600 to-sky-400 bg-clip-text text-transparent">
				CDMS
			</a>

			<!-- Desktop Menu -->
			<div class="hidden md:flex items-center gap-8">
				<a href="{{ config('app.url') }}/document" class="text-sm text-slate-400 hover:text-white transition-colors">Document Management</a>
				<a href="{{ config('app.url') }}/crew" class="text-sm text-slate-400 hover:text-white transition-colors">Crew Management</a>
				<a href="{{ config('app.url') }}/admin" class="px-4 py-2 bg-white text-slate-900 rounded-lg text-sm font-medium hover:bg-slate-100 transition-colors">
					Login Administrator
				</a>
			</div>

			<!-- Mobile Button -->
			<button onclick="toggleMenu()" class="md:hidden text-white text-2xl focus:outline-none">
				☰
			</button>

		</div>
	</nav>

	<!-- Mobile Sidebar -->
	<div id="mobileMenu"
		class="fixed top-0 right-0 h-full w-64 bg-slate-900/95 backdrop-blur-xl border-l border-slate-700/40 transform translate-x-full transition-transform duration-300 z-50">

		<div class="p-6 flex justify-between items-center border-b border-slate-700/40">
			<span class="text-lg font-semibold">Menu</span>
			<button onclick="toggleMenu()" class="text-2xl">✕</button>
		</div>

		<div class="p-6 flex flex-col gap-6 text-lg">
			<a href="{{ config('app.url') }}/document" class="text-slate-300 hover:text-white">Document Management</a>
			<a href="{{ config('app.url') }}/crew" class="text-slate-300 hover:text-white">Crew Management</a>
			<a href="{{ config('app.url') }}/admin"
				class="px-4 py-2 bg-white text-slate-900 rounded-lg w-fit">
				Login Administrator
			</a>
		</div>
	</div>

	<!-- Hero Section -->
	<section class="pt-32 pb-20 px-6">
		<div class="max-w-6xl mx-auto text-center">

			<div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500/10 border border-violet-500/20 rounded-full text-blue-300 text-sm mb-8">
				<span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
				<span>Welcome to</span>
			</div>

			<h1 class="text-5xl md:text-7xl font-bold tracking-tight mb-6 leading-[1.1]">
				CDMS
				<span class="block bg-gradient-to-r from-blue-600 via-sky-400 to-sky-300 bg-clip-text text-transparent">
					Haritashipping
				</span>
			</h1>

			<p class="text-xl text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
				Dokumen Management System, pengelolaan dokumen kapal dan manajemen crew
				yang dirancang untuk memastikan keterbaruan dokumen selalu terpantau
				dan siap digunakan kapan saja untuk kebutuhan operasional dan audit.
			</p>

			<div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-8">
				<a href="{{ config('app.url') }}/admin"
					class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-blue-600 to-sky-400 rounded-xl font-semibold text-lg hover:opacity-90 transition-opacity shadow-lg shadow-blue-400/25">
					Get Started →
				</a>
			</div>

			<p class="text-sm text-slate-500">© 2025 All rights.</p>
		</div>
	</section>

</body>

</html>
