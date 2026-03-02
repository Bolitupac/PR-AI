<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Git PULL Assistant | Auditor</title>
    @vite(['resources/css/auditor-ui.css', 'resources/js/app.js'])
</head>
<body>

<div class="app-shell flex h-screen bg-alabaster font-sans text-cod-gray">
    <!-- Sidebar -->
    <aside class="w-64 border-r border-gray-200 bg-white flex flex-col p-4 shadow-sm z-10 transition-all duration-300 overflow-y-auto">
        <!-- Logo Area -->
        <div class="flex items-center gap-3 px-2 mb-8">
            <div class="grid grid-cols-2 gap-1">
                <div class="h-2 w-2 rounded-full bg-cod-gray"></div>
                <div class="h-2 w-2 rounded-full bg-cod-gray"></div>
                <div class="h-2 w-2 rounded-full bg-cod-gray"></div>
                <div class="h-2 w-2 rounded-full bg-cod-gray"></div>
                <div class="h-2 w-2 rounded-full bg-cod-gray"></div>
                <div class="h-2 w-2 rounded-full bg-cod-gray"></div>
            </div>
            <span class="text-xl font-bold tracking-tight">Script</span>
            <button class="ml-auto text-gray-400 hover:text-cod-gray">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="relative mb-6">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </span>
            <input type="text" placeholder="Search" class="w-full bg-gray-50 border border-gray-100 rounded-xl py-2 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-azure-radiance/20">
            <span class="absolute inset-y-0 right-3 flex items-center text-gray-300 text-[10px] font-mono">⌘ K</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-1">
            <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-azure-radiance bg-azure-radiance/5 rounded-xl">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                AI Chat
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-xl transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2z" /></svg>
                Projects
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-xl transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>
                Templates
            </a>
            <div class="relative group">
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-xl transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Documents
                </a>
                <button class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-cod-gray">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                </button>
            </div>
            <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-xl transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                Community
                <span class="ml-auto px-2 py-0.5 text-[10px] font-bold text-white bg-gradient-to-r from-dodger-blue to-medium-purple rounded-full">NEW</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-xl transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                History
            </a>
        </nav>

        <!-- Bottom Actions -->
        <div class="mt-auto space-y-4 pt-4 border-t border-gray-100">
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Settings & Help</div>
            <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-xl transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                Settings
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-xl transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Help
            </a>

            <!-- Mode Toggle -->
            <div class="flex items-center bg-gray-50 rounded-xl p-1">
                <button class="flex-1 flex items-center justify-center gap-2 py-1.5 text-xs font-bold text-gray-600 bg-white shadow-sm rounded-lg transition-all">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" /></svg>
                    Light
                </button>
                <button class="flex-1 flex items-center justify-center gap-2 py-1.5 text-xs font-bold text-gray-400 hover:text-gray-600 transition-all">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" /></svg>
                    Dark
                </button>
            </div>

            <!-- Profile Area -->
            <div class="flex items-center gap-3 px-1 py-2">
                @auth
                    <img class="h-10 w-10 rounded-full border-2 border-white shadow-sm" src="https://github.com/{{ auth()->user()->github_username }}.png" alt="Avatar">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold truncate">{{ auth()->user()->name ?? 'User' }}</div>
                        <div class="text-[10px] text-gray-400 truncate">&#64;{{ auth()->user()->github_username ?? 'github-user' }}</div>
                    </div>
                @endauth
                @guest
                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold">Guest</div>
                        <a href="{{ route('github.redirect') }}" class="text-[10px] text-azure-radiance font-bold">Connect GitHub</a>
                    </div>
                @guest
                <button class="text-gray-400 hover:text-cod-gray">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                </button>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex overflow-hidden">
        <!-- Logic Auditor Panel -->
        <div class="flex-1 flex flex-col bg-white border-r border-gray-100 overflow-hidden">
            <header class="h-14 border-b border-gray-100 flex items-center justify-between px-6 shrink-0">
                <h2 class="font-bold text-lg">AI Chat</h2>
                <div class="flex items-center gap-3">
                    <button class="bg-cod-gray text-white px-4 py-1.5 rounded-lg text-sm font-bold flex items-center gap-2 hover:bg-black transition-colors">
                        <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        Upgrade
                    </button>
                    <button class="text-gray-400 hover:text-cod-gray">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </button>
                    <button class="text-gray-400 hover:text-cod-gray">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                    </button>
                    <button class="text-gray-400 hover:text-cod-gray bg-gray-50 rounded-lg p-1">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-8 flex flex-col items-center justify-center text-center">
                <h1 class="text-4xl font-bold mb-4 tracking-tight">Welcome to Script</h1>
                <p class="text-gray-400 mb-10">Get started by Script a task and Chat can do the rest. Not sure where to start?</p>
                
                <div class="grid grid-cols-2 gap-4 max-w-2xl w-full">
                    <button class="flex items-center gap-4 p-4 border border-gray-100 rounded-2xl hover:border-azure-radiance/30 hover:shadow-soft transition-all text-left group">
                        <div class="h-10 w-10 bg-orange-100/50 rounded-lg flex items-center justify-center text-orange-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </div>
                        <span class="font-bold flex-1 group-hover:text-azure-radiance transition-colors">Write copy</span>
                        <span class="text-gray-300 group-hover:text-azure-radiance transition-colors text-xl">+</span>
                    </button>
                    <button class="flex items-center gap-4 p-4 border border-gray-100 rounded-2xl hover:border-azure-radiance/30 hover:shadow-soft transition-all text-left group">
                        <div class="h-10 w-10 bg-blue-100/50 rounded-lg flex items-center justify-center text-blue-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h14a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 00-2 2z" /></svg>
                        </div>
                        <span class="font-bold flex-1 group-hover:text-azure-radiance transition-colors">Image generation</span>
                        <span class="text-gray-300 group-hover:text-azure-radiance transition-colors text-xl">+</span>
                    </button>
                    <button class="flex items-center gap-4 p-4 border border-gray-100 rounded-2xl hover:border-azure-radiance/30 hover:shadow-soft transition-all text-left group">
                        <div class="h-10 w-10 bg-green-100/50 rounded-lg flex items-center justify-center text-green-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <span class="font-bold flex-1 group-hover:text-azure-radiance transition-colors">Create avatar</span>
                        <span class="text-gray-300 group-hover:text-azure-radiance transition-colors text-xl">+</span>
                    </button>
                    <button class="flex items-center gap-4 p-4 border border-gray-100 rounded-2xl hover:border-azure-radiance/30 hover:shadow-soft transition-all text-left group">
                        <div class="h-10 w-10 bg-pink-100/50 rounded-lg flex items-center justify-center text-pink-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                        </div>
                        <span class="font-bold flex-1 group-hover:text-azure-radiance transition-colors">Write code</span>
                        <span class="text-gray-300 group-hover:text-azure-radiance transition-colors text-xl">+</span>
                    </button>
                </div>
            </div>

            <!-- Chat Bottom Input -->
            <div class="p-6 shrink-0 bg-white">
                <div class="max-w-3xl mx-auto relative group">
                    <div class="border border-gray-100 rounded-2xl shadow-soft group-focus-within:border-azure-radiance/30 transition-all">
                        <textarea class="w-full bg-transparent p-4 pb-12 focus:outline-none resize-none text-sm" placeholder="Summarize the latest" rows="2"></textarea>
                        <div class="absolute bottom-3 left-3 flex gap-2">
                            <button class="flex items-center gap-1 px-3 py-1.5 bg-gray-50 border border-gray-100 rounded-lg text-[10px] font-bold text-gray-600 hover:bg-gray-100 transition-colors">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                Attach
                            </button>
                            <button class="flex items-center gap-1 px-3 py-1.5 bg-gray-50 border border-gray-100 rounded-lg text-[10px] font-bold text-gray-600 hover:bg-gray-100 transition-colors">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                                Voice Message
                            </button>
                            <button class="flex items-center gap-1 px-3 py-1.5 bg-gray-50 border border-gray-100 rounded-lg text-[10px] font-bold text-gray-600 hover:bg-gray-100 transition-colors">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                Browse Prompts
                            </button>
                        </div>
                        <div class="absolute bottom-3 right-3 flex items-center gap-3">
                            <span class="text-[10px] text-gray-300 font-mono">20 / 3,000</span>
                            <button class="text-gray-400 hover:text-azure-radiance transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-[10px] text-gray-300">Script may generate inaccurate information about people, places, or facts. Model: Script AI v1.3</p>
                </div>
            </div>
        </div>

        <!-- Right Side Projects Panel (Visible in screenshots) -->
        <div class="w-80 bg-white p-6 overflow-y-auto hidden lg:block">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold">Projects</span>
                    <span class="text-[10px] font-bold text-gray-400">(7)</span>
                </div>
                <button class="text-gray-400">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8a2 2 0 110-4 2 2 0 010 4zm0 2a2 2 0 110 4 2 2 0 010-4zm0 6a2 2 0 110 4 2 2 0 010-4z"/></svg>
                </button>
            </div>
            
            <div class="space-y-3">
                <div class="p-4 border border-gray-100 rounded-2xl hover:bg-gray-50 cursor-pointer transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold">Generate 5 attention-grab...</span>
                        <div class="h-2 w-2 rounded-full border border-gray-200"></div>
                    </div>
                    <div class="text-[10px] text-gray-400 truncate">"Revolutionize Customer Engage..."</div>
                </div>
                <!-- More project items... -->
                @for ($i = 0; $i < 6; $i++)
                <div class="p-4 border border-gray-100 rounded-2xl hover:bg-gray-50 cursor-pointer transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold">Learning From 100 Years o...</span>
                        <div class="h-2 w-2 rounded-full border border-gray-200"></div>
                    </div>
                    <div class="text-[10px] text-gray-400 truncate">For athletes, high altitude prod...</div>
                </div>
                @endfor
            </div>

            <div class="mt-8 flex items-center justify-center p-4 border-2 border-dashed border-gray-100 rounded-2xl">
                <div class="h-10 w-10 rounded-full bg-gradient-to-r from-dodger-blue to-medium-purple flex items-center justify-center text-white shadow-lg overflow-hidden relative">
                    <div class="absolute inset-0 bg-black/10"></div>
                    <svg class="h-5 w-5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                </div>
            </div>
        </div>
    </main>
</div>

    @include('partials.diff-viewer')
</div>

@include('partials.repo-import')
@include('partials.diff-upload')

<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/loader.min.js"></script>

</body>
</html>
