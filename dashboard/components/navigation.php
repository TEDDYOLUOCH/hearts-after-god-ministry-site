<?php
<nav class="bg-white shadow-sm border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">
      <div class="flex">
        <div class="flex-shrink-0 flex items-center">
          <img class="h-8 w-auto" src="/assets/images/logo.png" alt="Logo">
        </div>
        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
          <a href="#" class="dashboard-nav border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
            Dashboard
          </a>
          <a href="#" class="blog-nav border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
            Blog
          </a>
          <a href="#" class="events-nav border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
            Events
          </a>
          <a href="#" class="media-nav border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
            Media
          </a>
        </div>
      </div>
      <div class="flex items-center">
        <button type="button" class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none">
          <span class="sr-only">View notifications</span>
          <i data-lucide="bell" class="h-6 w-6"></i>
        </button>
        <div class="ml-3 relative">
          <div class="flex items-center">
            <button type="button" class="bg-white rounded-full flex text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" id="user-menu-button">
              <img class="h-8 w-8 rounded-full" src="/assets/images/avatar.jpg" alt="">
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>